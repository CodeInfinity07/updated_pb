<?php

namespace App\Http\Controllers;

use App\Models\CryptoWallet;
use App\Models\Transaction;
use App\Services\PlisioPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PlisioWebhookController extends Controller
{
    private const CURRENCY_SHORT_CODES = [
        'USDT_BEP20' => 'UB',
        'USDT_TRC20' => 'UT',
        'USDT_ERC20' => 'UE',
        'BTC' => 'BT',
        'ETH' => 'ET',
        'BNB' => 'BN',
        'ADA' => 'AD',
        'SOL' => 'SL',
        'MATIC' => 'MT',
        'LTC' => 'LT',
        'DOGE' => 'DG',
        'TRX' => 'TR',
        'XRP' => 'XR',
        'DOT' => 'DT',
        'AVAX' => 'AV',
    ];

    public function handleCallback(Request $request)
    {
        Log::info('=== PLISIO WEBHOOK RECEIVED ===', [
            'timestamp' => now()->toISOString(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'all_data' => $request->all(),
            'raw_content' => $request->getContent()
        ]);

        try {
            $data = $request->all();

            if (empty($data)) {
                $data = json_decode($request->getContent(), true) ?? [];
            }

            if (empty($data['order_number'])) {
                Log::warning('Plisio webhook missing order_number', ['data' => $data]);
                return response('Missing order_number', 400);
            }

            $plisioService = app(PlisioPaymentService::class);
            $rawContent = $request->getContent();
            
            if (!$plisioService->verifyCallback($data, $rawContent)) {
                Log::error('Plisio callback verification failed', ['data' => $data]);
                return response('Invalid signature', 400);
            }

            $status = $data['status'] ?? '';
            $orderId = $data['order_number'];
            $txnId = $data['txn_id'] ?? '';
            $sourceAmount = floatval($data['source_amount'] ?? 0);
            $amount = floatval($data['amount'] ?? 0);
            $currency = $data['source_currency'] ?? 'USD';
            $cryptoCurrency = $data['currency'] ?? '';
            $confirmations = intval($data['confirmations'] ?? 0);

            Log::info('Plisio callback parsed', [
                'order_id' => $orderId,
                'status' => $status,
                'source_amount' => $sourceAmount,
                'crypto_amount' => $amount,
                'currency' => $currency,
                'crypto_currency' => $cryptoCurrency,
                'txn_id' => $txnId,
                'confirmations' => $confirmations
            ]);

            if ($status === 'pending' || $status === 'new') {
                Log::info('Payment pending/new - waiting for confirmation', ['order_id' => $orderId]);
                return response('OK - Waiting', 200);
            }

            if ($status === 'completed' || $status === 'mismatch') {
                $result = $this->confirmDeposit(
                    $orderId,
                    $sourceAmount,
                    $txnId,
                    $data,
                    $confirmations
                );

                if ($result) {
                    Log::info('=== PLISIO DEPOSIT CONFIRMED ===', [
                        'order_id' => $orderId,
                        'amount' => $sourceAmount,
                        'txn_id' => $txnId
                    ]);
                    return response('OK', 200);
                } else {
                    Log::error('Plisio deposit confirmation failed', ['order_id' => $orderId]);
                    return response('Confirmation failed', 500);
                }
            }

            if ($status === 'expired' || $status === 'cancelled' || $status === 'error') {
                $this->handleFailedPayment($orderId, $status, $data);
                return response('OK - Handled', 200);
            }

            Log::info('Plisio webhook processed - status: ' . $status, ['order_id' => $orderId]);
            return response('OK', 200);

        } catch (Exception $e) {
            Log::error('=== PLISIO WEBHOOK ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response('Internal error', 500);
        }
    }

    private function confirmDeposit(string $orderId, float $amount, string $txnId, array $webhookData, int $confirmations): bool
    {
        try {
            return DB::transaction(function () use ($orderId, $amount, $txnId, $webhookData, $confirmations) {
                $orderParts = explode('_', $orderId);

                if (count($orderParts) !== 3) {
                    Log::error('Invalid order ID format', [
                        'order_id' => $orderId,
                        'parts' => $orderParts
                    ]);
                    return false;
                }

                $shortCode = $orderParts[0];
                $userId = $orderParts[1];
                $timestamp = $orderParts[2];

                $currency = $this->getFullCurrencyFromShortCode($shortCode);

                if (!$currency) {
                    Log::error('Unknown currency short code', [
                        'order_id' => $orderId,
                        'short_code' => $shortCode
                    ]);
                    return false;
                }

                $wallet = CryptoWallet::where('user_id', $userId)
                    ->where('currency', $currency)
                    ->first();

                if (!$wallet) {
                    Log::error('Wallet not found for deposit', [
                        'user_id' => $userId,
                        'currency' => $currency
                    ]);
                    return false;
                }

                $existingByTxid = Transaction::where('crypto_txid', $txnId)
                    ->where('status', Transaction::STATUS_COMPLETED)
                    ->first();

                if ($existingByTxid) {
                    Log::warning('Transaction already processed by txn_id', [
                        'order_id' => $orderId,
                        'txn_id' => $txnId
                    ]);
                    return true;
                }

                $transaction = Transaction::where('transaction_id', $orderId)
                    ->where('status', Transaction::STATUS_PENDING)
                    ->first();

                if (!$transaction) {
                    $completedTransaction = Transaction::where('transaction_id', $orderId)
                        ->where('status', Transaction::STATUS_COMPLETED)
                        ->first();

                    if ($completedTransaction) {
                        Log::warning('Transaction already completed', ['order_id' => $orderId]);
                        return true;
                    }

                    Log::warning('No pending transaction found, creating new record', [
                        'order_id' => $orderId,
                        'user_id' => $userId
                    ]);

                    $transaction = Transaction::create([
                        'user_id' => $userId,
                        'transaction_id' => $orderId,
                        'type' => Transaction::TYPE_DEPOSIT,
                        'amount' => $amount,
                        'currency' => $currency,
                        'status' => Transaction::STATUS_PENDING,
                        'payment_method' => 'plisio',
                        'description' => "Crypto deposit - {$currency} via Plisio",
                        'metadata' => [
                            'wallet_id' => $wallet->id,
                            'gateway' => 'plisio',
                            'created_via' => 'webhook_fallback'
                        ]
                    ]);
                }

                $oldBalance = $wallet->balance;
                $wallet->increment('balance', $amount);
                $newBalance = $wallet->fresh()->balance;

                $transaction->update([
                    'status' => Transaction::STATUS_COMPLETED,
                    'crypto_txid' => $txnId,
                    'processed_at' => now(),
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'webhook_data' => $webhookData,
                        'confirmations' => $confirmations,
                        'old_balance' => $oldBalance,
                        'new_balance' => $newBalance,
                        'gateway' => 'plisio'
                    ]),
                ]);

                Log::info('=== DEPOSIT PROCESSED SUCCESSFULLY ===', [
                    'transaction_id' => $transaction->id,
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'txn_id' => $txnId
                ]);

                return true;
            });

        } catch (Exception $e) {
            Log::error('Deposit confirmation exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function handleFailedPayment(string $orderId, string $status, array $data): void
    {
        $transaction = Transaction::where('transaction_id', $orderId)
            ->where('status', Transaction::STATUS_PENDING)
            ->first();

        if ($transaction) {
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'failure_reason' => $status,
                    'webhook_data' => $data,
                    'failed_at' => now()->toISOString()
                ])
            ]);

            Log::info('Payment marked as failed', [
                'order_id' => $orderId,
                'status' => $status
            ]);
        }
    }

    private function getFullCurrencyFromShortCode(string $shortCode): ?string
    {
        $flipped = array_flip(self::CURRENCY_SHORT_CODES);
        return $flipped[$shortCode] ?? null;
    }
}
