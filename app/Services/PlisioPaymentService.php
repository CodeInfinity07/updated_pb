<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PlisioPaymentService
{
    protected $secretKey;
    protected $apiUrl = 'https://api.plisio.net/api/v1';
    protected $timeout;

    public function __construct()
    {
        $this->secretKey = config('payment.plisio.secret_key');
        $this->timeout = config('payment.plisio.timeout', 30);
    }

    protected function ensureConfigured(): void
    {
        if (empty($this->secretKey)) {
            throw new Exception('Plisio API secret key not configured. Please set PLISIO_SECRET_KEY in your environment.');
        }
    }

    public function createInvoice(
        string $currency,
        float $amount,
        string $orderId,
        string $orderName,
        string $email,
        string $callbackUrl
    ): array {
        $this->ensureConfigured();
        
        try {
            $params = [
                'api_key' => $this->secretKey,
                'source_currency' => 'USD',
                'source_amount' => $amount,
                'order_number' => $orderId,
                'order_name' => $orderName,
                'currency' => $this->mapCurrencyToPlisio($currency),
                'email' => $email,
                'callback_url' => $callbackUrl . '?json=true',
            ];

            Log::info('Creating Plisio invoice', [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'plisio_currency' => $params['currency']
            ]);

            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/invoices/new", $params);

            if (!$response->successful()) {
                Log::error('Plisio API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Payment gateway unavailable');
            }

            $data = $response->json();

            if ($data['status'] !== 'success') {
                $error = $data['data']['message'] ?? 'Unknown error';
                Log::error('Plisio invoice creation failed', ['error' => $error, 'response' => $data]);
                throw new Exception("Invoice creation failed: {$error}");
            }

            Log::info('Plisio invoice created successfully', [
                'order_id' => $orderId,
                'txn_id' => $data['data']['txn_id'] ?? null,
                'invoice_url' => $data['data']['invoice_url'] ?? null
            ]);

            return [
                'success' => true,
                'txn_id' => $data['data']['txn_id'],
                'invoice_url' => $data['data']['invoice_url'],
                'amount' => $data['data']['amount'] ?? $amount,
                'pending_amount' => $data['data']['pending_amount'] ?? null,
                'wallet_hash' => $data['data']['wallet_hash'] ?? null,
                'address' => $data['data']['wallet_hash'] ?? null,
                'qr_code' => $data['data']['qr_code'] ?? null,
                'currency' => $currency,
                'expire_utc' => $data['data']['expire_utc'] ?? null,
                'raw_response' => $data['data']
            ];

        } catch (RequestException $e) {
            Log::error('Plisio API request exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Payment gateway is currently unavailable. Please try again later.');
        }
    }

    public function processWithdrawal(
        string $address,
        float $amount,
        string $orderId,
        string $currency,
        array $userInfo
    ): array {
        $this->ensureConfigured();
        
        try {
            $plisioCurrency = $this->mapCurrencyToPlisio($currency);
            $formattedAmount = number_format($amount, 8, '.', '');

            Log::info('Processing Plisio withdrawal', [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'plisio_currency' => $plisioCurrency,
                'to_address' => $address,
                'user_id' => $userInfo['user_id'] ?? 'unknown'
            ]);

            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/operations/withdraw", [
                    'api_key' => $this->secretKey,
                    'currency' => $plisioCurrency,
                    'type' => 'cash_out',
                    'to' => $address,
                    'amount' => $formattedAmount,
                ]);

            if (!$response->successful()) {
                Log::error('Plisio withdrawal API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Withdrawal gateway unavailable');
            }

            $data = $response->json();

            if ($data['status'] !== 'success') {
                $error = $data['data']['message'] ?? $data['data']['name'] ?? 'Unknown error';
                Log::error('Plisio withdrawal failed', ['error' => $error, 'response' => $data]);
                throw new Exception("Withdrawal failed: {$error}");
            }

            $txnId = $data['data']['tx_id'] ?? $data['data']['id'] ?? null;
            
            if (empty($txnId)) {
                throw new Exception('Withdrawal failed - no transaction ID returned');
            }

            Log::info('Plisio withdrawal completed', [
                'order_id' => $orderId,
                'txn_id' => $txnId,
                'amount' => $amount,
                'to_address' => $address
            ]);

            return [
                'success' => true,
                'txn_id' => $txnId,
                'explorer_url' => $data['data']['tx_url'] ?? null,
                'amount' => $amount,
                'message' => 'Withdrawal processed successfully',
                'raw_response' => $data['data']
            ];

        } catch (RequestException $e) {
            Log::error('Plisio withdrawal request exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Withdrawal gateway is currently unavailable. Please try again later.');
        }
    }

    public function getBalance(string $currency): array
    {
        try {
            $plisioCurrency = $this->mapCurrencyToPlisio($currency);
            
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/balances/{$plisioCurrency}", [
                    'api_key' => $this->secretKey
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get balance');
            }

            $data = $response->json();

            return [
                'success' => true,
                'balance' => $data['data']['balance'] ?? 0,
                'currency' => $currency
            ];

        } catch (Exception $e) {
            Log::error('Plisio get balance failed', [
                'currency' => $currency,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getAllBalances(): array
    {
        $currencies = ['USDT_BEP20', 'BNB'];
        $balances = [];
        $totalUsd = 0;
        
        $bnbPrice = $this->getBnbPrice();
        
        foreach ($currencies as $currency) {
            try {
                $result = $this->getBalance($currency);
                $balance = (float) ($result['balance'] ?? 0);
                $usdValue = $currency === 'BNB' ? $balance * $bnbPrice : $balance;
                
                $balances[$currency] = [
                    'balance' => $balance,
                    'formatted' => number_format($balance, 8),
                    'usd_value' => $usdValue,
                    'usd_formatted' => '$' . number_format($usdValue, 2)
                ];
                
                $totalUsd += $usdValue;
            } catch (Exception $e) {
                $balances[$currency] = [
                    'balance' => 0,
                    'formatted' => '0.00000000',
                    'usd_value' => 0,
                    'usd_formatted' => '$0.00',
                    'error' => true
                ];
            }
        }
        
        return [
            'success' => true,
            'balances' => $balances,
            'total_usdt' => $totalUsd,
            'total_usdt_formatted' => '$' . number_format($totalUsd, 2),
            'bnb_price' => $bnbPrice
        ];
    }

    private function getBnbPrice(): float
    {
        try {
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'binancecoin',
                'vs_currencies' => 'usd'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return (float) ($data['binancecoin']['usd'] ?? 600);
            }
        } catch (Exception $e) {
            Log::warning('Failed to fetch BNB price', ['error' => $e->getMessage()]);
        }
        
        return 600;
    }

    public function verifyCallback(array $data, ?string $rawContent = null): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('Plisio secret key not configured for verification');
            return false;
        }

        if (empty($data['verify_hash'])) {
            Log::warning('Plisio callback missing verify_hash');
            return false;
        }

        $receivedHash = $data['verify_hash'];
        
        // Method 1: Use raw JSON content if available (preserves original order)
        if ($rawContent) {
            $rawData = json_decode($rawContent, true);
            if ($rawData && isset($rawData['verify_hash'])) {
                unset($rawData['verify_hash']);
                $rawJsonString = json_encode($rawData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $rawJsonHash = hash_hmac('sha1', $rawJsonString, $this->secretKey);
                
                if (hash_equals($rawJsonHash, $receivedHash)) {
                    Log::info('Plisio callback verified (raw JSON method)', [
                        'order_number' => $data['order_number'] ?? 'unknown'
                    ]);
                    return true;
                }
            }
        }
        
        $dataToVerify = $data;
        unset($dataToVerify['verify_hash']);

        // Method 2: JSON method (for json=true callback URL - no sorting)
        $jsonString = json_encode($dataToVerify, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $jsonHash = hash_hmac('sha1', $jsonString, $this->secretKey);
        
        if (hash_equals($jsonHash, $receivedHash)) {
            Log::info('Plisio callback verified (JSON method)', [
                'order_number' => $data['order_number'] ?? 'unknown'
            ]);
            return true;
        }
        
        // Method 3: PHP serialize method (for standard POST callback)
        ksort($dataToVerify);
        
        if (isset($dataToVerify['expire_utc'])) {
            $dataToVerify['expire_utc'] = (string) $dataToVerify['expire_utc'];
        }
        
        if (isset($dataToVerify['tx_urls'])) {
            $dataToVerify['tx_urls'] = html_entity_decode($dataToVerify['tx_urls']);
        }
        
        $serialized = serialize($dataToVerify);
        $serializeHash = hash_hmac('sha1', $serialized, $this->secretKey);
        
        if (hash_equals($serializeHash, $receivedHash)) {
            Log::info('Plisio callback verified (serialize method)', [
                'order_number' => $data['order_number'] ?? 'unknown'
            ]);
            return true;
        }

        Log::warning('Plisio callback verification failed', [
            'order_number' => $data['order_number'] ?? 'unknown',
            'received_hash' => substr($receivedHash, 0, 10) . '...',
            'json_hash' => substr($jsonHash, 0, 10) . '...',
            'serialize_hash' => substr($serializeHash, 0, 10) . '...'
        ]);

        return false;
    }

    public function getTransactionStatus(string $txnId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/operations/{$txnId}", [
                    'api_key' => $this->secretKey
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get transaction status');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Plisio get transaction status failed', [
                'txn_id' => $txnId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getTransactionDetails(string $txnId): ?array
    {
        try {
            $this->ensureConfigured();
            
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/operations/{$txnId}", [
                    'api_key' => $this->secretKey
                ]);

            if (!$response->successful()) {
                Log::warning('Plisio transaction details request failed', [
                    'txn_id' => $txnId,
                    'status' => $response->status()
                ]);
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'success' || empty($data['data'])) {
                return null;
            }

            $txnData = $data['data'];
            
            return [
                'status' => $txnData['status'] ?? null,
                'source_amount' => $txnData['source_amount'] ?? null,
                'source_currency' => $txnData['source_currency'] ?? null,
                'actual_sum' => $txnData['actual_sum'] ?? null,
                'actual_sum_in_crypto' => $txnData['actual_sum_in_crypto'] ?? null,
                'amount' => $txnData['amount'] ?? null,
                'pending_amount' => $txnData['pending_amount'] ?? null,
                'currency' => $txnData['currency'] ?? null,
                'tx_url' => $txnData['tx_url'] ?? null,
                'confirmations' => $txnData['confirmations'] ?? null,
                'created_at_utc' => $txnData['created_at_utc'] ?? null,
                'expire_utc' => $txnData['expire_utc'] ?? null,
            ];

        } catch (Exception $e) {
            Log::warning('Plisio get transaction details failed', [
                'txn_id' => $txnId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function mapCurrencyToPlisio(string $currency): string
    {
        $mapping = [
            'USDT_TRC20' => 'USDT_TRX',
            'USDT_ERC20' => 'USDT',
            'USDT_BEP20' => 'USDT_BSC',
            'BTC' => 'BTC',
            'ETH' => 'ETH',
            'LTC' => 'LTC',
            'DOGE' => 'DOGE',
            'TRX' => 'TRX',
            'BNB' => 'BNB',
            'MATIC' => 'MATIC',
            'SOL' => 'SOL',
            'XRP' => 'XRP',
            'ADA' => 'ADA',
            'DOT' => 'DOT',
            'AVAX' => 'AVAX',
        ];

        return $mapping[$currency] ?? $currency;
    }

    public function getSupportedCurrencies(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/currencies", [
                    'api_key' => $this->secretKey
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get currencies');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Plisio get currencies failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
