<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\InvestmentPlan;
use App\Models\InvestmentPlanTier;
use App\Models\UserInvestment;
use App\Models\Transaction;
use App\Models\CommissionSetting;
use App\Models\ProfitSharingTransaction;
use App\Models\InvestmentExpirySetting;
use App\Services\CommissionDistributionService;
use App\Services\ReferralQualificationService;
use App\Services\DirectSponsorCommissionService;
use Exception;

class BotController extends Controller
{
    protected $commissionService;
    protected $qualificationService;
    protected $directSponsorCommissionService;

    public function __construct(
        CommissionDistributionService $commissionService, 
        ReferralQualificationService $qualificationService,
        DirectSponsorCommissionService $directSponsorCommissionService
    ) {
        $this->commissionService = $commissionService;
        $this->qualificationService = $qualificationService;
        $this->directSponsorCommissionService = $directSponsorCommissionService;
    }

    /**
     * Display Color Trading game page with investment functionality
     */
    public function colorTradingGame()
    {
        $user = User::with('profile')->find(Auth::id());

        // Get investment plan and tier information
        $investmentData = $this->getInvestmentPlanForUser($user);

        // Get user investment statistics
        $investmentStats = $this->getUserInvestmentStats($user);

        // Check if bot needs activation (first investment)
        $botActivationRequired = !$user->bot_activated_at;
        $botFee = InvestmentExpirySetting::getBotFeeAmount();
        
        // For first investment, minimum is platform minimum + bot fee
        if ($botActivationRequired && isset($investmentData['minimum_amount'])) {
            $platformMinimum = $investmentData['minimum_amount'];
            $investmentData['first_investment_minimum'] = $platformMinimum + $botFee;
        }

        return view('bot.color-trading-game', compact(
            'user', 
            'investmentData', 
            'investmentStats',
            'botActivationRequired',
            'botFee'
        ));
    }

    /**
     * Get appropriate investment plan and tier for user (using profile level)
     */
    private function getInvestmentPlanForUser(User $user): array
    {
        // Get first active investment plan
        $plan = InvestmentPlan::where('status', 'active')
            ->orderBy('sort_order')
            ->first();

        if (!$plan) {
            return [
                'plan' => null,
                'tier' => null,
                'can_invest' => false,
                'message' => 'No investment plans are currently available.'
            ];
        }

        if ($plan->is_tiered) {
            // Use profile level - if level 0, allow access to tier 1
            $profileLevel = $user->profile ? $user->profile->level : 0;
            $tierLevel = $profileLevel == 0 ? 1 : $profileLevel;

            // Find appropriate tier for user's profile level
            $tier = InvestmentPlanTier::where('investment_plan_id', $plan->id)
                ->where('is_active', true)
                ->where('tier_level', $tierLevel)
                ->orderBy('tier_level', 'desc')
                ->first();

            if (!$tier) {
                $nextTier = InvestmentPlanTier::where('investment_plan_id', $plan->id)
                    ->where('is_active', true)
                    ->where('tier_level', '>', $profileLevel)
                    ->orderBy('tier_level')
                    ->first();

                return [
                    'plan' => $plan,
                    'tier' => null,
                    'can_invest' => false,
                    'message' => $nextTier
                        ? "You need to reach tier level {$nextTier->tier_level} to access investment tiers."
                        : 'No investment tiers available for your level.',
                    'current_level' => $profileLevel,
                    'next_required_level' => $nextTier ? $nextTier->tier_level : null
                ];
            }

            return [
                'plan' => $plan,
                'tier' => $tier,
                'can_invest' => true,
                'minimum_amount' => $tier->minimum_amount,
                'maximum_amount' => $tier->maximum_amount,
                'interest_rate' => $tier->interest_rate,
                'tier_name' => $tier->tier_name,
                'tier_level' => $tier->tier_level
            ];
        } else {
            // Non-tiered plan
            return [
                'plan' => $plan,
                'tier' => null,
                'can_invest' => true,
                'minimum_amount' => $plan->minimum_amount,
                'maximum_amount' => $plan->maximum_amount,
                'interest_rate' => $plan->interest_rate
            ];
        }
    }

    /**
     * Get user investment statistics (excludes bot_fee type investments)
     */
    private function getUserInvestmentStats(User $user): array
    {
        $activeInvestments = UserInvestment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('type', '!=', UserInvestment::TYPE_BOT_FEE)
                  ->orWhereNull('type');
            })
            ->with('investmentPlan')
            ->get();

        $completedInvestments = UserInvestment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where(function($q) {
                $q->where('type', '!=', UserInvestment::TYPE_BOT_FEE)
                  ->orWhereNull('type');
            })
            ->get();

        $totalInvested = $user->total_invested ?? 0;
        $totalEarned = $user->total_earned ?? 0;

        $pendingReturns = UserInvestment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('type', '!=', UserInvestment::TYPE_BOT_FEE)
                  ->orWhereNull('type');
            })
            ->sum('total_return') - UserInvestment::where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function($q) {
                    $q->where('type', '!=', UserInvestment::TYPE_BOT_FEE)
                      ->orWhereNull('type');
                })
                ->sum('paid_return');

        return [
            'active_investments_count' => $activeInvestments->count(),
            'completed_investments_count' => $completedInvestments->count(),
            'total_invested' => $totalInvested,
            'total_earned' => $totalEarned,
            'pending_returns' => max(0, $pendingReturns),
            'active_investments' => $activeInvestments,
            'recent_investments' => UserInvestment::where('user_id', $user->id)
                ->where(function($q) {
                    $q->where('type', '!=', UserInvestment::TYPE_BOT_FEE)
                      ->orWhereNull('type');
                })
                ->with('investmentPlan')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    /**
     * Create new investment with level upgrade logic based on profile level
     */
    public function createInvestment(Request $request): JsonResponse
    {
        try {
            // Get fresh user data with profile
            $user = User::with('profile')->find(Auth::id());

            // Ensure profile exists
            if (!$user->profile) {
                $user->profile()->create([]);
                $user = User::with('profile')->find($user->id);
            }

            // Check user status - allow active and pending_verification (for new users)
            if (in_array($user->status, ['blocked', 'suspended', 'banned'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been restricted. Please contact support.'
                ], 400);
            }

            // Get investment plan data
            $investmentData = $this->getInvestmentPlanForUser($user);

            if (!$investmentData['can_invest']) {
                return response()->json([
                    'success' => false,
                    'message' => $investmentData['message'],
                    'current_level' => $investmentData['current_level'] ?? null,
                    'next_required_level' => $investmentData['next_required_level'] ?? null
                ], 400);
            }

            // Calculate effective minimum (includes bot fee for first investment)
            $botFee = InvestmentExpirySetting::getBotFeeAmount();
            $isFirstInvestment = !$user->bot_activated_at;
            $effectiveMinimum = $isFirstInvestment 
                ? $investmentData['minimum_amount'] + $botFee 
                : $investmentData['minimum_amount'];

            // Validate input
            $request->validate([
                'amount' => [
                    'required',
                    'numeric',
                    'min:' . $effectiveMinimum,
                    'max:' . min($investmentData['maximum_amount'], $user->available_balance)
                ]
            ], [
                'amount.required' => 'Investment amount is required',
                'amount.numeric' => 'Amount must be a valid number',
                'amount.min' => 'Minimum investment amount is $' . number_format($effectiveMinimum, 2) . ($isFirstInvestment ? ' (includes $' . number_format($botFee, 2) . ' bot activation fee)' : ''),
                'amount.max' => 'Maximum investment amount is $' . number_format(min($investmentData['maximum_amount'], $user->available_balance), 2)
            ]);

            $amount = floatval($request->amount);

            // Check user balance
            if (!$user->hasSufficientBalance($amount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient platform balance for this investment.',
                    'available_balance' => $user->available_balance,
                    'required_amount' => $amount
                ], 400);
            }

            $plan = $investmentData['plan'];
            $tier = $investmentData['tier'];

            DB::beginTransaction();

            try {
                // Always create a new investment (no merging)
                $result = $this->createNewInvestment($amount, $plan, $tier, $user);

                if (!$result['success']) {
                    throw new Exception($result['message']);
                }

                // **REFRESH USER DATA WITH PROFILE BEFORE LEVEL CHECK**
                $user = User::with('profile')->find($user->id);

                Log::info('User data after investment before level check', [
                    'user_id' => $user->id,
                    'profile_level' => $user->profile ? $user->profile->level : 'no_profile',
                    'total_invested' => $user->total_invested,
                    'qualifies_for_level_upgrade' => $this->userQualifiesForLevelUpgrade($user)
                ]);

                // **LEVEL UPGRADE LOGIC - CHECK PROFILE LEVEL**
                $levelUpgradeResult = $this->checkAndUpgradeProfileLevel($user);

                Log::info('Investment and level upgrade completed', [
                    'user_id' => $user->id,
                    'investment_amount' => $amount,
                    'level_upgrade' => $levelUpgradeResult
                ]);

                DB::commit();

                // Refresh user data one more time
                $user = User::with('profile')->find($user->id);

                // 🔔 SEND INVESTMENT NOTIFICATION
                try {
                    $investmentDescription = $plan->name . ($tier ? " - {$tier->tier_name}" : '');

                    $user->notify(
                        \App\Notifications\UnifiedNotification::investmentCreated(
                            $investmentDescription,
                            $amount,
                            $plan->duration_days . ' days',
                            $tier ? $tier->interest_rate : $plan->interest_rate
                        )
                    );

                    Log::info('Investment notification sent', [
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'plan' => $investmentDescription
                    ]);
                } catch (\Exception $notificationError) {
                    Log::error('Investment notification failed', [
                        'user_id' => $user->id,
                        'error' => $notificationError->getMessage()
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => array_merge($result['data'], [
                        'new_platform_balance' => $user->available_balance,
                        'new_game_balance' => $user->profile->umoney,
                        'wallet_breakdown' => $user->getWalletBreakdown(),
                        'user_total_invested' => $user->total_invested,
                        'user_total_earned' => $user->total_earned,
                        'profile_level' => $user->profile->level,
                        'level_upgrade' => $levelUpgradeResult
                    ])
                ]);

            } catch (Exception $e) {
                DB::rollback();

                Log::error('Investment transaction failed', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Investment creation failed', [
                'user_id' => Auth::id(),
                'amount' => $request->amount ?? 0,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user qualifies for level upgrade (profile level 0 to 1)
     */
    private function userQualifiesForLevelUpgrade(User $user): bool
    {
        // Check if profile exists and is at level 0 with investment
        if (!$user->profile) {
            return false;
        }

        // Cast to appropriate types for comparison
        $profileLevel = (int) $user->profile->level;
        $totalInvested = (float) $user->total_invested;

        return $profileLevel === 0 && $totalInvested > 0;
    }

    /**
     * Check and upgrade user profile level from 0 to 1 after first investment
     */
    private function checkAndUpgradeProfileLevel(User $user): array
    {
        Log::info('Checking profile level upgrade', [
            'user_id' => $user->id,
            'profile_level' => $user->profile ? $user->profile->level : 'no_profile',
            'total_invested' => $user->total_invested,
            'qualifies' => $this->userQualifiesForLevelUpgrade($user)
        ]);

        // Only upgrade if profile exists and is at level 0 with investment
        if (!$user->profile) {
            return [
                'upgraded' => false,
                'reason' => 'No profile found',
                'current_level' => null
            ];
        }

        if ((int) $user->profile->level !== 0) {
            return [
                'upgraded' => false,
                'reason' => 'Profile is not at level 0',
                'current_level' => $user->profile->level
            ];
        }

        if ($user->total_invested <= 0) {
            return [
                'upgraded' => false,
                'reason' => 'No investment amount found',
                'total_invested' => $user->total_invested
            ];
        }

        try {
            Log::info('Proceeding with profile level upgrade', [
                'user_id' => $user->id,
                'from_level' => $user->profile->level,
                'to_level' => 1,
                'total_invested' => $user->total_invested
            ]);

            // Upgrade profile level from 0 to 1
            $user->profile->update([
                'level' => 1
            ]);

            // Also update user_level for consistency (optional)
            $user->update([
                'user_level' => 1,
                'level_updated_at' => now()
            ]);

            Log::info('Profile level upgraded after first investment', [
                'user_id' => $user->id,
                'old_profile_level' => 0,
                'new_profile_level' => 1,
                'old_user_level' => 0,
                'new_user_level' => 1,
                'total_invested' => $user->total_invested
            ]);

            return [
                'upgraded' => true,
                'old_profile_level' => 0,
                'new_profile_level' => 1,
                'old_user_level' => 0,
                'new_user_level' => 1,
                'reason' => 'First investment completed',
                'total_invested' => $user->total_invested,
                'message' => 'Congratulations! You have been upgraded to Level 1 after your first investment!'
            ];

        } catch (Exception $e) {
            Log::error('Failed to upgrade profile level', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'upgraded' => false,
                'reason' => 'Upgrade failed: ' . $e->getMessage(),
                'error' => true
            ];
        }
    }

    /**
     * Merge investment with existing one
     */
    private function mergeInvestment(UserInvestment $existingInvestment, float $newAmount, InvestmentPlan $plan, ?InvestmentPlanTier $tier, User $user): array
    {
        $currentAmount = $existingInvestment->amount;
        $totalAmount = $currentAmount + $newAmount;

        // Check if merged amount exceeds tier maximum
        $maxAllowed = $tier ? $tier->maximum_amount : $plan->maximum_amount;

        if ($totalAmount > $maxAllowed) {
            return [
                'success' => false,
                'message' => "Cannot process your investments. Total amount ($" . number_format($totalAmount, 2) .
                    ") would exceed the maximum limit of $" . number_format($maxAllowed, 2) .
                    " for your tier level. Current investment: $" . number_format($currentAmount, 2) . "."
            ];
        }

        // Deduct from user's crypto wallets
        if (!$user->deductFromWallets($newAmount)) {
            return [
                'success' => false,
                'message' => 'Failed to deduct amount from wallets'
            ];
        }

        // Update user's total_invested
        $user->increment('total_invested', $newAmount);

        // Calculate new total return based on merged amount
        $newTotalReturn = $plan->calculateTotalReturn($totalAmount);

        // Update existing investment with current tier level from profile
        $existingInvestment->update([
            'amount' => $totalAmount,
            'total_return' => $newTotalReturn,
            'tier_level' => $user->profile ? $user->profile->level : 0,
            'notes' => ($existingInvestment->notes ?? '') . "\nMerged with additional investment of $" . number_format($newAmount, 2) . " on " . now()->format('Y-m-d H:i:s')
        ]);

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'INV_MERGE_' . time() . '_' . $user->id,
            'type' => 'investment',
            'amount' => $newAmount,
            'status' => 'completed',
            'description' => "Additional investment merged into {$plan->name}" . ($tier ? " - {$tier->tier_name}" : '') .
                " (Total: $" . number_format($totalAmount, 2) . ")",
            'reference_id' => $existingInvestment->id,
            'reference_type' => 'user_investment',
            'metadata' => json_encode([
                'plan_name' => $plan->name,
                'tier_name' => $tier ? $tier->tier_name : null,
                'tier_level' => $tier ? $tier->tier_level : null,
                'interest_rate' => $tier ? $tier->interest_rate : $plan->interest_rate,
                'previous_amount' => $currentAmount,
                'additional_amount' => $newAmount,
                'total_amount' => $totalAmount,
                'action_type' => 'merge_investment',
                'profile_level' => $user->profile ? $user->profile->level : 0
            ])
        ]);

        Log::info('Investment merged successfully', [
            'user_id' => $user->id,
            'investment_id' => $existingInvestment->id,
            'previous_amount' => $currentAmount,
            'additional_amount' => $newAmount,
            'total_amount' => $totalAmount,
            'user_total_invested' => $user->total_invested,
            'profile_level' => $user->profile->level,
            'transaction_id' => $transaction->transaction_id
        ]);

        $this->directSponsorCommissionService->distributeCommission($existingInvestment, $newAmount);

        return [
            'success' => true,
            'message' => "Successfully added $" . number_format($newAmount, 2) . " to your existing {$plan->name} investment! Total investment: $" . number_format($totalAmount, 2),
            'data' => [
                'investment_id' => $existingInvestment->id,
                'action_type' => 'merged',
                'amount_added' => $newAmount,
                'total_investment_amount' => $totalAmount,
                'plan_name' => $plan->name,
                'tier_name' => $tier ? $tier->tier_name : null,
                'expected_total_return' => $newTotalReturn,
                'transaction_id' => $transaction->transaction_id,
                'tier_level' => $user->profile->level
            ]
        ];
    }

    /**
     * Create new investment
     */
    private function createNewInvestment(float $amount, InvestmentPlan $plan, ?InvestmentPlanTier $tier, User $user): array
    {
        // Check if this is first investment (bot activation)
        $isFirstInvestment = !$user->bot_activated_at;
        $investmentAmount = $amount;
        
        // Deduct full amount from user's crypto wallets (includes bot fee on first investment)
        if (!$user->deductFromWallets($amount)) {
            return [
                'success' => false,
                'message' => 'Failed to deduct amount from wallets'
            ];
        }

        // Update user's total_invested (only the investment portion, not bot fee)
        $user->increment('total_invested', $investmentAmount);

        // Calculate investment parameters based on actual investment amount
        // Handle variable ROI plans - use average of min/max or the fixed interest rate
        if ($tier) {
            $interestRate = $tier->interest_rate;
        } elseif ($plan->isVariableRoi() && $plan->min_interest_rate !== null && $plan->max_interest_rate !== null) {
            // For variable ROI, use the average rate for initial calculation
            $interestRate = ((float) $plan->min_interest_rate + (float) $plan->max_interest_rate) / 2;
        } else {
            $interestRate = $plan->interest_rate ?? 0;
        }
        $durationDays = $plan->duration_days ?? 365;
        $dailyReturn = ($investmentAmount * $interestRate) / 100;
        $totalReturn = $plan->calculateTotalReturn($investmentAmount);
        $startDate = now();
        $endDate = now()->addDays($durationDays);
        
        // Create new investment with actual investment amount (excluding bot fee)
        $userInvestment = UserInvestment::create([
            'user_id' => $user->id,
            'investment_plan_id' => $plan->id,
            'amount' => $investmentAmount,
            'roi_percentage' => $interestRate,
            'duration_days' => $durationDays,
            'total_return' => $totalReturn,
            'daily_return' => $dailyReturn,
            'paid_return' => 0,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'earnings_accumulated' => 0,
            'commission_earned' => 0,
            'expiry_multiplier' => 3,
            'bot_fee_applied' => $isFirstInvestment,
        ]);
        
        // Activate bot on first investment
        if ($isFirstInvestment) {
            $user->activateBot();
            Log::info('Bot activated for user', [
                'user_id' => $user->id,
                'first_investment_id' => $userInvestment->id
            ]);
        }

        // Create transaction record for investment (using actual investment amount)
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'INV_' . time() . '_' . $user->id,
            'type' => 'investment',
            'amount' => $investmentAmount,
            'currency' => 'USD',
            'status' => 'completed',
            'description' => "Investment in {$plan->name}" . ($tier ? " - {$tier->tier_name}" : ''),
            'metadata' => json_encode([
                'investment_id' => $userInvestment->id,
                'plan_name' => $plan->name,
                'tier_name' => $tier ? $tier->tier_name : null,
                'tier_level' => $tier ? $tier->tier_level : null,
                'interest_rate' => $interestRate,
                'roi_type' => $plan->roi_type ?? 'fixed',
                'action_type' => 'new_investment',
                'profile_level' => $user->profile ? $user->profile->level : 0
            ])
        ]);
        
        Log::info('New investment created successfully', [
            'user_id' => $user->id,
            'investment_id' => $userInvestment->id,
            'amount' => $amount,
            'user_total_invested' => $user->total_invested,
            'profile_level' => $user->profile->level,
            'transaction_id' => $transaction->transaction_id
        ]);

        $this->directSponsorCommissionService->distributeCommission($userInvestment);

        return [
            'success' => true,
            'message' => "Successfully invested $" . number_format($investmentAmount, 2) . " in {$plan->name}!",
            'data' => [
                'investment_id' => $userInvestment->id,
                'action_type' => 'created',
                'amount_added' => $investmentAmount,
                'total_investment_amount' => $investmentAmount,
                'total_deducted' => $amount,
                'plan_name' => $plan->name,
                'tier_name' => $tier ? $tier->tier_name : null,
                'expected_total_return' => $userInvestment->total_return,
                'transaction_id' => $transaction->transaction_id,
                'tier_level' => $user->profile->level
            ]
        ];
    }

    /**
     * Process investment return and update user's total_earned
     */
    public function processInvestmentReturn(UserInvestment $investment): JsonResponse
    {
        try {
            // Check if user owns this investment
            if ($investment->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if a return is due
            if (!$investment->isDueForReturn()) {
                return response()->json([
                    'success' => true,
                    'return_processed' => false,
                    'message' => 'Return not yet due',
                    'next_due_date' => $investment->getNextReturnDueDate()?->toISOString()
                ]);
            }

            DB::beginTransaction();

            try {
                // Calculate the return amount
                $returnAmount = $investment->calculateSingleReturn();

                // Add return to user's account balance
                $success = $investment->user->addInvestmentReturn(
                    $returnAmount,
                    "Daily return from {$investment->investmentPlan->name} investment"
                );

                if (!$success) {
                    throw new Exception('Failed to process return payment');
                }

                // Update user's total_earned
                $investment->user->increment('total_earned', $returnAmount);

                // Update investment record with return payment
                $investment->addReturnPayment($returnAmount);

                DB::commit();

                Log::info('Investment return processed', [
                    'investment_id' => $investment->id,
                    'user_id' => $investment->user_id,
                    'return_amount' => $returnAmount,
                    'user_total_earned' => $investment->user->total_earned,
                    'next_due_date' => $investment->getNextReturnDueDate()
                ]);

                return response()->json([
                    'success' => true,
                    'return_processed' => true,
                    'amount' => $returnAmount,
                    'formatted_amount' => '$' . number_format($returnAmount, 2),
                    'user_total_earned' => $investment->user->fresh()->total_earned,
                    'next_due_date' => $investment->getNextReturnDueDate()?->toISOString(),
                    'message' => "Return of $" . number_format($returnAmount, 2) . " has been credited to your account"
                ]);

            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Return processing failed', [
                'investment_id' => $investment->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process return payment'
            ], 500);
        }
    }

    /**
     * Process all due investment returns - for scheduled task
     */
    public function processAllDueReturns(): array
    {
        $processed = 0;
        $failed = 0;
        $totalAmount = 0;
        $errors = [];

        try {
            // Get all active investments that are due for returns (exclude bot_fee type)
            $dueInvestments = UserInvestment::where('status', 'active')
                ->where(function($query) {
                    $query->where('type', '!=', UserInvestment::TYPE_BOT_FEE)
                          ->orWhereNull('type');
                })
                ->with(['user', 'investmentPlan'])
                ->get()
                ->filter(function ($investment) {
                    return $investment->isDueForReturn();
                });

            Log::info('Processing due investment returns', [
                'total_due_investments' => $dueInvestments->count(),
                'started_at' => now()->toDateTimeString()
            ]);

            foreach ($dueInvestments as $investment) {
                try {
                    // Skip users with ROI disabled (dummy users)
                    if ($investment->user && $investment->user->roi_disabled) {
                        Log::info('Skipping ROI for user with roi_disabled', [
                            'investment_id' => $investment->id,
                            'user_id' => $investment->user_id
                        ]);
                        continue;
                    }

                    $result = $this->processSingleInvestmentReturn($investment);

                    if ($result['success']) {
                        $processed++;
                        $totalAmount += $result['amount'];

                        Log::info('Investment return processed successfully', [
                            'investment_id' => $investment->id,
                            'user_id' => $investment->user_id,
                            'amount' => $result['amount']
                        ]);
                    } else {
                        $failed++;
                        $errors[] = [
                            'investment_id' => $investment->id,
                            'user_id' => $investment->user_id,
                            'error' => $result['message']
                        ];

                        Log::error('Investment return processing failed', [
                            'investment_id' => $investment->id,
                            'user_id' => $investment->user_id,
                            'error' => $result['message']
                        ]);
                    }

                } catch (Exception $e) {
                    $failed++;
                    $errors[] = [
                        'investment_id' => $investment->id,
                        'user_id' => $investment->user_id,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Investment return processing exception', [
                        'investment_id' => $investment->id,
                        'user_id' => $investment->user_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('Bulk investment return processing completed', [
                'total_processed' => $processed,
                'total_failed' => $failed,
                'total_amount_distributed' => $totalAmount,
                'completed_at' => now()->toDateTimeString()
            ]);

            return [
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'total_amount' => $totalAmount,
                'errors' => $errors,
                'message' => "Processed {$processed} returns, {$failed} failed. Total distributed: $" . number_format($totalAmount, 2)
            ];

        } catch (Exception $e) {
            Log::error('Bulk investment return processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'processed' => $processed,
                'failed' => $failed,
                'total_amount' => $totalAmount,
                'errors' => $errors,
                'message' => 'Bulk processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process a single investment return with bot fees, earnings tracking, and expiry logic
     */
    private function processSingleInvestmentReturn(UserInvestment $investment): array
    {
        DB::beginTransaction();

        try {
            $user = $investment->user;

            // Check if package has already reached expiry cap
            if ($investment->hasReachedExpiryCap()) {
                $investment->expireDueToCap();
                DB::commit();
                
                Log::info('Package already at expiry cap, marking complete', [
                    'investment_id' => $investment->id,
                    'user_id' => $user->id
                ]);
                
                return [
                    'success' => true,
                    'amount' => 0,
                    'message' => 'Package has reached its earnings cap and is now complete'
                ];
            }

            // Update expiry multiplier based on referral qualification (dynamically re-evaluated)
            // Only the OLDEST active investment gets 6x, all others stay at 3x
            $userQualifiesFor6x = $this->qualificationService->qualifiesForExtendedMultiplier($user);
            
            if ($userQualifiesFor6x) {
                // Find the oldest active investment for this user
                $oldestActiveInvestment = UserInvestment::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                // Only give 6x to the oldest investment
                if ($oldestActiveInvestment && $investment->id === $oldestActiveInvestment->id) {
                    $targetMultiplier = InvestmentExpirySetting::getExtendedMultiplier();
                } else {
                    $targetMultiplier = InvestmentExpirySetting::getBaseMultiplier();
                }
            } else {
                $targetMultiplier = InvestmentExpirySetting::getBaseMultiplier();
            }
            
            if ($investment->expiry_multiplier != $targetMultiplier) {
                $investment->update(['expiry_multiplier' => $targetMultiplier]);
            }

            // Activate bot on first package if not already activated
            if (!$user->isBotActivated() && !$investment->bot_fee_applied) {
                $investment->update(['bot_fee_applied' => true]);
                $user->activateBot();
                
                Log::info('Bot activated on first package', [
                    'user_id' => $user->id,
                    'investment_id' => $investment->id
                ]);
            }

            // Calculate the return amount
            $returnAmount = $investment->calculateSingleReturn();

            // Check if this return would exceed the expiry cap - limit it
            $remainingUntilCap = $investment->getRemainingUntilCap();
            if ($returnAmount > $remainingUntilCap) {
                $returnAmount = $remainingUntilCap;
            }

            if ($returnAmount <= 0) {
                $investment->expireDueToCap();
                DB::commit();
                return [
                    'success' => true,
                    'amount' => 0,
                    'message' => 'Package has reached its earnings cap'
                ];
            }

            // Add return to user's account balance
            $success = $user->addInvestmentReturn(
                $returnAmount,
                "Daily return from {$investment->investmentPlan->name} investment"
            );

            if (!$success) {
                throw new Exception('Failed to process return payment');
            }

            // Update user's total_earned
            $user->increment('total_earned', $returnAmount);

            // Update investment record with return payment
            $investment->addReturnPayment($returnAmount);

            // Track earnings on this package (for expiry cap calculation)
            $investment->addEarnings($returnAmount, 'roi');

            // Check if package has now reached expiry cap
            if ($investment->hasReachedExpiryCap()) {
                $investment->expireDueToCap();
                Log::info('Package reached expiry cap after ROI', [
                    'investment_id' => $investment->id,
                    'earnings' => $investment->earnings_accumulated,
                    'cap' => $investment->getExpiryCap()
                ]);
            }

            DB::commit();

            // Refresh user data
            $user->refresh();

            // Send notification
            try {
                $user->notify(
                    \App\Notifications\UnifiedNotification::investmentReturnPaid(
                        $investment->investmentPlan->name,
                        $returnAmount,
                        $user->total_earned
                    )
                );
            } catch (\Exception $notificationError) {
                Log::error('Investment return notification failed', [
                    'user_id' => $user->id,
                    'error' => $notificationError->getMessage()
                ]);
            }

            // Distribute ROI commissions to upline chain (10 levels)
            try {
                $commissionResults = $this->commissionService->distributeRoiCommissions(
                    $user,
                    $returnAmount,
                    "{$investment->investmentPlan->name} daily ROI"
                );

                if (!empty($commissionResults)) {
                    Log::info('ROI commissions distributed', [
                        'investment_id' => $investment->id,
                        'roi_amount' => $returnAmount,
                        'commissions_count' => count($commissionResults),
                        'total_commissions' => array_sum(array_column($commissionResults, 'amount'))
                    ]);
                }
            } catch (\Exception $commissionError) {
                Log::error('ROI commission distribution failed', [
                    'investment_id' => $investment->id,
                    'error' => $commissionError->getMessage()
                ]);
            }

            return [
                'success' => true,
                'amount' => $returnAmount,
                'earnings_accumulated' => $investment->earnings_accumulated,
                'expiry_cap' => $investment->getExpiryCap(),
                'user_total_earned' => $user->total_earned,
                'next_due_date' => $investment->getNextReturnDueDate()?->toISOString(),
                'message' => "Return of $" . number_format($returnAmount, 2) . " has been credited to your account"
            ];

        } catch (Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Failed to process return: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process profit sharing for investment return
     */
    private function processProfitSharingForReturn(UserInvestment $investment, float $returnAmount)
    {
        $user = $investment->user;
        $plan = $investment->investmentPlan;

        Log::info("Processing profit sharing for investment return", [
            'investment_id' => $investment->id,
            'user_id' => $user->id,
            'return_amount' => $returnAmount,
            'plan_has_profit_sharing' => $plan->profit_sharing_enabled
        ]);

        // Get referral chain data
        $referralChainData = $this->getReferralChainProfitSharingData($user, $plan, $returnAmount);

        // Process actual commission payments
        $commissionResults = $this->processCommissionPayments($referralChainData, $investment, $returnAmount);

        Log::info("Profit sharing completed for investment return", [
            'investment_id' => $investment->id,
            'total_commissions_paid' => $commissionResults['total_amount'],
            'recipients_count' => $commissionResults['recipients_count'],
            'successful_payments' => $commissionResults['successful_payments'],
            'failed_payments' => $commissionResults['failed_payments'],
        ]);

        return $commissionResults;
    }

    /**
     * Process actual commission payments for eligible sponsors
     */
    private function processCommissionPayments(array $referralChainData, UserInvestment $investment, float $returnAmount): array
    {
        $results = [
            'total_amount' => 0,
            'recipients_count' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'payment_details' => [],
            'errors' => []
        ];

        foreach ($referralChainData as $levelKey => $levelData) {
            // Skip if sponsor doesn't exist or role check failed
            if (!$levelData['exists'] || isset($levelData['role_check_failed'])) {
                continue;
            }

            // Skip if no profit sharing configuration
            if (!$levelData['profit_sharing_config'] || !$levelData['commission_calculation']) {
                continue;
            }

            $sponsorId = $levelData['sponsor_info']['id'];
            $commissionAmount = $levelData['commission_calculation']['commission_amount'];
            $commissionLevel = $levelData['commission_calculation']['level'];
            $commissionRate = $levelData['commission_calculation']['commission_rate'];

            // Skip if commission amount is zero or negative
            if ($commissionAmount <= 0) {
                continue;
            }

            try {
                DB::beginTransaction();

                $sponsor = User::find($sponsorId);
                if (!$sponsor) {
                    throw new Exception("Sponsor user not found: {$sponsorId}");
                }

                // Create ProfitSharingTransaction record
                $profitSharingTransaction = ProfitSharingTransaction::create([
                    'user_investment_id' => $investment->id,
                    'beneficiary_user_id' => $sponsor->id,
                    'source_user_id' => $investment->user_id,
                    'commission_level' => $commissionLevel,
                    'commission_amount' => $commissionAmount,
                    'source_investment_amount' => $investment->amount,
                    'commission_rate' => $commissionRate,
                    'status' => 'pending',
                    'notes' => "Commission from investment return - Return Amount: $" . number_format($returnAmount, 2),
                ]);

                // Credit the sponsor's account with commission
                $transactionId = 'PST_' . time() . '_' . $sponsor->id . '_' . uniqid();
                $creditSuccess = $sponsor->addInvestmentReturn(
                    $commissionAmount,
                    "Level {$commissionLevel} referral commission from {$investment->user->email} investment return",
                    $transactionId,
                    'profit'
                );

                if (!$creditSuccess) {
                    throw new Exception("Failed to credit commission to sponsor account");
                }

                // Mark profit sharing transaction as paid
                $profitSharingTransaction->markAsPaid($transactionId);

                // Update sponsor's total earned
                $sponsor->increment('total_earned', $commissionAmount);

                DB::commit();

                $results['total_amount'] += $commissionAmount;
                $results['recipients_count']++;
                $results['successful_payments']++;

                // 🔔 SEND REFERRAL COMMISSION NOTIFICATION
                try {
                    $sponsor->notify(
                        \App\Notifications\UnifiedNotification::commissionEarned(
                            $commissionAmount,
                            $investment->user->full_name ?? $investment->user->email
                        )
                    );

                    Log::info('Referral commission notification sent', [
                        'sponsor_id' => $sponsor->id,
                        'commission_level' => $commissionLevel,
                        'commission_amount' => $commissionAmount
                    ]);
                } catch (\Exception $notificationError) {
                    Log::error('Referral commission notification failed', [
                        'sponsor_id' => $sponsor->id,
                        'error' => $notificationError->getMessage()
                    ]);
                }

                $results['payment_details'][] = [
                    'level' => $commissionLevel,
                    'sponsor_id' => $sponsor->id,
                    'sponsor_email' => $sponsor->email,
                    'commission_amount' => $commissionAmount,
                    'commission_rate' => $commissionRate,
                    'transaction_id' => $transactionId,
                    'profit_sharing_txn_id' => $profitSharingTransaction->id,
                    'status' => 'success'
                ];

                Log::info('Profit sharing commission paid successfully', [
                    'investment_id' => $investment->id,
                    'beneficiary_user_id' => $sponsor->id,
                    'commission_level' => $commissionLevel,
                    'commission_amount' => $commissionAmount,
                    'commission_rate' => $commissionRate,
                    'transaction_id' => $transactionId,
                    'profit_sharing_txn_id' => $profitSharingTransaction->id,
                ]);

            } catch (Exception $e) {
                DB::rollback();

                $results['failed_payments']++;
                $results['errors'][] = [
                    'level' => $commissionLevel ?? 'unknown',
                    'sponsor_id' => $sponsorId,
                    'error' => $e->getMessage(),
                    'commission_amount' => $commissionAmount ?? 0
                ];

                $results['payment_details'][] = [
                    'level' => $commissionLevel ?? 'unknown',
                    'sponsor_id' => $sponsorId,
                    'sponsor_email' => $levelData['sponsor_info']['email'] ?? 'unknown',
                    'commission_amount' => $commissionAmount ?? 0,
                    'commission_rate' => $commissionRate ?? 0,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];

                Log::error('Profit sharing commission payment failed', [
                    'investment_id' => $investment->id,
                    'sponsor_id' => $sponsorId,
                    'commission_level' => $commissionLevel,
                    'commission_amount' => $commissionAmount,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get referral chain profit sharing data up to 3 levels
     */
    private function getReferralChainProfitSharingData(User $user, InvestmentPlan $plan, float $returnAmount): array
    {
        $chainData = [];
        $currentUser = $user;

        for ($level = 1; $level <= 3; $level++) {
            // Get the sponsor (referrer) at this level
            $sponsor = $currentUser->sponsor;

            if (!$sponsor) {
                $chainData["level_{$level}"] = [
                    'exists' => false,
                    'message' => 'No sponsor found at this level'
                ];
                break;
            }

            // Check if sponsor has 'user' role (skip profit sharing for staff accounts)
            if ($sponsor->role !== User::ROLE_USER) {
                $chainData["level_{$level}"] = [
                    'exists' => true,
                    'sponsor_info' => [
                        'id' => $sponsor->id,
                        'email' => $sponsor->email,
                        'role' => $sponsor->role,
                        'level' => $sponsor->profile->level ?? 0,
                    ],
                    'tier_info' => null,
                    'profit_sharing_config' => null,
                    'commission_calculation' => null,
                    'message' => "Sponsor has '{$sponsor->role}' role - profit sharing skipped (only 'user' role eligible)",
                    'role_check_failed' => true
                ];
                $currentUser = $sponsor;
                continue;
            }

            // Get sponsor's level (which should match their tier level)
            $sponsorLevel = $sponsor->profile->level ?? 0;

            // Find the tier that matches the sponsor's level
            $tier = $plan->tiers()
                ->where('tier_level', $sponsorLevel)
                ->where('is_active', true)
                ->first();

            if (!$tier) {
                $chainData["level_{$level}"] = [
                    'exists' => true,
                    'sponsor_info' => [
                        'id' => $sponsor->id,
                        'email' => $sponsor->email,
                        'level' => $sponsorLevel,
                    ],
                    'tier_info' => null,
                    'profit_sharing_config' => null,
                    'message' => "No active tier found for sponsor level {$sponsorLevel}"
                ];
                $currentUser = $sponsor;
                continue;
            }

            // Get profit sharing configuration for this tier
            $profitSharingConfig = $tier->activeProfitSharing;

            if (!$profitSharingConfig) {
                $chainData["level_{$level}"] = [
                    'exists' => true,
                    'sponsor_info' => [
                        'id' => $sponsor->id,
                        'email' => $sponsor->email,
                        'level' => $sponsorLevel,
                    ],
                    'tier_info' => [
                        'id' => $tier->id,
                        'tier_level' => $tier->tier_level,
                        'tier_name' => $tier->tier_name,
                        'minimum_amount' => $tier->minimum_amount,
                        'maximum_amount' => $tier->maximum_amount,
                        'interest_rate' => $tier->interest_rate,
                    ],
                    'profit_sharing_config' => null,
                    'message' => 'No active profit sharing configuration found for this tier'
                ];
                $currentUser = $sponsor;
                continue;
            }

            // Calculate commission for this level
            $commissionRate = match ($level) {
                1 => $profitSharingConfig->level_1_commission,
                2 => $profitSharingConfig->level_2_commission,
                3 => $profitSharingConfig->level_3_commission,
                default => 0
            };

            $commissionAmount = $profitSharingConfig->calculateCommission($level, $returnAmount);

            $chainData["level_{$level}"] = [
                'exists' => true,
                'sponsor_info' => [
                    'id' => $sponsor->id,
                    'email' => $sponsor->email,
                    'full_name' => $sponsor->full_name ?? 'N/A',
                    'level' => $sponsorLevel,
                    'status' => $sponsor->status,
                ],
                'tier_info' => [
                    'id' => $tier->id,
                    'tier_level' => $tier->tier_level,
                    'tier_name' => $tier->tier_name,
                    'minimum_amount' => $tier->minimum_amount,
                    'maximum_amount' => $tier->maximum_amount,
                    'interest_rate' => $tier->interest_rate,
                    'is_active' => $tier->is_active,
                ],
                'profit_sharing_config' => [
                    'id' => $profitSharingConfig->id,
                    'level_1_commission' => $profitSharingConfig->level_1_commission,
                    'level_2_commission' => $profitSharingConfig->level_2_commission,
                    'level_3_commission' => $profitSharingConfig->level_3_commission,
                    'max_commission_cap' => $profitSharingConfig->max_commission_cap,
                    'total_commission_rate' => $profitSharingConfig->total_commission_rate,
                    'is_active' => $profitSharingConfig->is_active,
                ],
                'commission_calculation' => [
                    'level' => $level,
                    'commission_rate' => $commissionRate,
                    'return_amount' => $returnAmount,
                    'commission_amount' => $commissionAmount,
                    'formatted_commission' => '$' . number_format($commissionAmount, 2),
                    'formatted_rate' => $commissionRate . '%',
                ],
                'message' => "Level {$level} referral commission calculated successfully"
            ];

            // Move up the chain for next iteration
            $currentUser = $sponsor;
        }

        return $chainData;
    }

    /**
     * API endpoint to manually trigger bulk return processing
     */
    public function processBulkReturns(Request $request): JsonResponse
    {
        try {
            $result = $this->processAllDueReturns();

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Manual bulk return processing failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk returns'
            ], 500);
        }
    }

    /**
     * Update user tiers and their active investments
     */
    public function updateUserTiers(): JsonResponse
    {
        try {
            $updated = 0;
            $investmentsUpdated = 0;

            $tiers = CommissionSetting::where('is_active', true)
                ->orderBy('level')
                ->get();

            if ($tiers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active commission tiers found.'
                ]);
            }

            DB::beginTransaction();

            try {
                User::with(['profile', 'activeInvestments'])->chunk(100, function ($users) use ($tiers, &$updated, &$investmentsUpdated) {
                    foreach ($users as $user) {
                        if (!$user->profile) {
                            continue;
                        }

                        $currentLevel = $user->profile->level ?? 0;
                        $newTier = null;

                        // Find the highest tier the user qualifies for
                        foreach ($tiers->reverse() as $tier) {
                            if ($this->userQualifiesForTier($user, $tier)) {
                                $newTier = $tier;
                                break;
                            }
                        }

                        if ($newTier && $currentLevel !== $newTier->level) {
                            // Update user profile tier
                            $user->profile->update(['level' => $newTier->level]);
                            $updated++;

                            // Update active investments tier level
                            $activeInvestments = $user->activeInvestments();
                            $investmentUpdateCount = $activeInvestments->update(['tier_level' => $newTier->level]);
                            $investmentsUpdated += $investmentUpdateCount;

                            Log::info('User tier updated', [
                                'user_id' => $user->id,
                                'old_level' => $currentLevel,
                                'new_level' => $newTier->level,
                                'active_investments_updated' => $investmentUpdateCount
                            ]);
                        }
                    }
                });

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Successfully updated {$updated} user tiers and {$investmentsUpdated} active investments.",
                    'updated_count' => $updated,
                    'investments_updated_count' => $investmentsUpdated
                ]);

            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Bulk user tier update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user tiers.'
            ], 500);
        }
    }

    /**
     * Check if user qualifies for a specific tier (using profile level and correct requirements)
     */
    private function userQualifiesForTier(User $user, CommissionSetting $tier): bool
    {
        // First check if the user themselves is active
        if ($user->status !== 'active') {
            return false;
        }

        // Check investment requirement using total_invested field
        $totalInvestment = $user->total_invested ?? 0;
        if ($totalInvestment < $tier->min_investment) {
            return false;
        }

        // Check direct referrals requirement
        $directReferrals = $user->directReferrals()->where('status', 'active')->count();
        if ($directReferrals < $tier->min_direct_referrals) {
            return false;
        }

        // Check indirect referrals requirement (2nd and 3rd level only)
        $indirectReferrals = $user->referrals()
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->whereIn('level', [2, 3])
            ->count();

        if ($indirectReferrals < $tier->min_indirect_referrals) {
            return false;
        }

        return true;
    }

    /**
     * Add balance to game via external API
     */
    private function addGameBalance(string $username, float $amount): array
    {
        try {
            $payload = json_encode([
                'username' => $username,
                'amount' => $amount
            ]);

            $ch = curl_init('https://spy.winlottery9.com/add-balance');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to game server'
                ];
            }

            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result['status']) && $result['status']) {
                return [
                    'success' => true,
                    'message' => $result['message'] ?? 'Balance added successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to add balance'
                ];
            }

        } catch (Exception $e) {
            Log::error('Game API error', [
                'username' => $username,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Game API error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Link game account via AJAX
     */
    public function linkGameAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'username' => 'required|string|min:10',
                'pwd' => 'required|string|min:6'
            ], [
                'username.required' => 'Username is required',
                'username.min' => 'Username must be at least 10 characters',
                'pwd.required' => 'Password is required',
                'pwd.min' => 'Password must be at least 6 characters'
            ]);

            $user = User::with('profile')->find(Auth::id());

            // Check if account is already linked
            if ($user->profile && $user->profile->uname) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game account is already linked. Please unlink first if you want to change accounts.'
                ]);
            }

            // Validate credentials with game API
            $gameApiResponse = $this->validateGameCredentials($request->username, $request->pwd);

            if (!$gameApiResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $gameApiResponse['message']
                ]);
            }

            // Create or update user profile with game account info
            if (!$user->profile) {
                $user->profile()->create([]);
                $user = User::with('profile')->find($user->id);
            }

            $user->profile->update([
                'uname' => $gameApiResponse['data']['uname'],
                'upwd' => encrypt($request->pwd),
                'umoney' => $gameApiResponse['data']['umoney'],
                'game_linked_at' => now(),
                'game_settings' => json_encode([
                    'linked_from' => request()->ip(),
                    'linked_at' => now()->toDateTimeString(),
                    'api_response' => $gameApiResponse['data']
                ])
            ]);

            Log::info('Game account linked successfully', [
                'user_id' => $user->id,
                'username' => $gameApiResponse['data']['uname'],
                'balance' => $gameApiResponse['data']['umoney']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Game account linked successfully!',
                'data' => [
                    'uname' => $gameApiResponse['data']['uname'],
                    'umoney' => $gameApiResponse['data']['umoney']
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Game account linking failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    /**
     * Unlink game account
     */
    public function unlinkGameAccount(Request $request): JsonResponse
    {
        try {
            $user = User::with('profile')->find(Auth::id());

            if (!$user->profile || !$user->profile->uname) {
                return response()->json([
                    'success' => false,
                    'message' => 'No game account is currently linked.'
                ]);
            }

            $oldUname = $user->profile->uname;

            $user->profile->update([
                'uname' => null,
                'upwd' => null,
                'umoney' => 0,
                'game_linked_at' => null,
                'game_settings' => null
            ]);

            Log::info('Game account unlinked', [
                'user_id' => $user->id,
                'old_username' => $oldUname
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Game account unlinked successfully.'
            ]);

        } catch (Exception $e) {
            Log::error('Game account unlinking failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unlink account. Please try again.'
            ], 500);
        }
    }

    /**
     * Validate game credentials with external API
     */
    private function validateGameCredentials(string $username, string $password): array
    {
        $apiUrl = config('services.game_api.url');

        if (!$apiUrl || $apiUrl === 'mock' || str_contains($apiUrl, 'your-game-api.com')) {
            return $this->mockValidateCredentials($username, $password);
        }

        try {
            $response = Http::timeout(15)
                ->retry(3, 1000)
                ->post($apiUrl, [
                    'username' => $username,
                    'pwd' => $password
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $message = strtolower($data['message'] ?? '');
                $isSuccessfulLogin = str_contains($message, 'login successful') ||
                    str_contains($message, 'success');
                $hasValidData = isset($data['uname']) && !empty($data['uname']);
                $successFlag = ($data['success'] ?? false) === true;

                if ($isSuccessfulLogin || $hasValidData || $successFlag) {
                    return [
                        'success' => true,
                        'data' => [
                            'uname' => $data['uname'] ?? $username,
                            'umoney' => floatval($data['umoney'] ?? 0)
                        ]
                    ];
                }
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'message' => $errorData['message'] ?? 'Invalid credentials'
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'message' => 'Unable to connect to game server. Please try again later.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Validation service temporarily unavailable. Please try again.'
            ];
        }
    }

    /**
     * Mock validation for testing
     */
    private function mockValidateCredentials(string $username, string $password): array
    {
        if (strlen($username) < 10) {
            return [
                'success' => false,
                'message' => 'Username must be at least 10 characters'
            ];
        }

        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'uname' => $username,
                'umoney' => rand(100, 2000)
            ]
        ];
    }


    /**
     * Get commission statistics for user
     */
    public function getCommissionStats(Request $request): JsonResponse
    {
        try {
            $user = User::with('profile')->find(Auth::id());
            $stats = $this->commissionService->getCommissionStats($user);
            $recentCommissions = $this->commissionService->getRecentCommissions($user, 5);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_commissions' => $recentCommissions->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'amount' => $transaction->amount,
                            'formatted_amount' => '$' . number_format($transaction->amount, 2),
                            'description' => $transaction->description,
                            'level' => $transaction->metadata['commission_level'] ?? 'N/A',
                            'investor_name' => $transaction->metadata['investor_name'] ?? 'Unknown',
                            'currency' => $transaction->currency,
                            'created_at' => $transaction->created_at->format('M d, Y g:i A'),
                            'created_ago' => $transaction->created_at->diffForHumans()
                        ];
                    })
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Get commission stats failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load commission statistics'
            ], 500);
        }
    }

    /**
     * API endpoints
     */
    public function getInvestmentStats(Request $request): JsonResponse
    {
        try {
            $user = User::with('profile')->find(Auth::id());
            $investmentStats = $this->getUserInvestmentStats($user);

            return response()->json([
                'success' => true,
                'data' => $investmentStats
            ]);

        } catch (Exception $e) {
            Log::error('Get investment stats failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }

    public function refreshGameBalance(Request $request): JsonResponse
    {
        try {
            $user = User::with('profile')->find(Auth::id());

            if (!$user->profile || !$user->profile->uname) {
                return response()->json([
                    'success' => false,
                    'message' => 'No game account linked'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $user->profile->umoney ?? 0,
                    'updated_at' => now()->toISOString()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Refresh game balance failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh balance'
            ], 500);
        }
    }

    public function aviator()
    {
        return redirect()->route('bot.index')
            ->with('info', 'Aviator game is coming soon!');
    }

    /**
     * DEBUG: Manual profile level upgrade check - REMOVE IN PRODUCTION
     */
    public function debugLevelUpgrade(Request $request): JsonResponse
    {
        try {
            $user = User::with('profile')->find(Auth::id());

            Log::info('DEBUG: Manual profile level upgrade check', [
                'user_id' => $user->id,
                'profile_level' => $user->profile ? $user->profile->level : 'no_profile',
                'total_invested' => $user->total_invested,
                'qualifies_for_level_upgrade' => $this->userQualifiesForLevelUpgrade($user)
            ]);

            $result = $this->checkAndUpgradeProfileLevel($user);

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'user_id' => $user->id,
                    'profile_level_before' => $user->profile ? $user->profile->level : 'no_profile',
                    'total_invested' => $user->total_invested,
                    'qualifies_for_level_upgrade' => $this->userQualifiesForLevelUpgrade($user),
                ],
                'level_upgrade_result' => $result,
                'user_after_refresh' => [
                    'profile_level' => $user->fresh()->profile ? $user->fresh()->profile->level : 'no_profile',
                    'user_level' => $user->fresh()->user_level
                ]
            ]);

        } catch (Exception $e) {
            Log::error('DEBUG: Manual profile level upgrade check failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}