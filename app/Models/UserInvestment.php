<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserInvestment extends Model
{
    use HasFactory;

    const TYPE_INVESTMENT = 'investment';
    const TYPE_BOT_FEE = 'bot_fee';

    protected $fillable = [
        'user_id',
        'investment_plan_id',
        'type',
        'amount',
        'roi_percentage',
        'duration_days',
        'total_return',
        'daily_return',
        'paid_return',
        'status',
        'start_date',
        'end_date',
        'last_payout_date',
        'earnings_accumulated',
        'commission_earned',
        'expiry_multiplier',
        'bot_fee_applied',
        'status_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'roi_percentage' => 'decimal:2',
            'total_return' => 'decimal:2',
            'daily_return' => 'decimal:2',
            'paid_return' => 'decimal:2',
            'earnings_accumulated' => 'decimal:2',
            'commission_earned' => 'decimal:2',
            'expiry_multiplier' => 'integer',
            'bot_fee_applied' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_payout_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who owns this investment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment plan.
     */
    public function investmentPlan(): BelongsTo
    {
        return $this->belongsTo(InvestmentPlan::class);
    }

    /**
     * Get all return payments for this investment.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(InvestmentReturn::class);
    }

    /**
     * Get pending return payments.
     */
    public function pendingReturns(): HasMany
    {
        return $this->hasMany(InvestmentReturn::class)->where('status', 'pending');
    }

    /**
     * Get paid return payments.
     */
    public function paidReturns(): HasMany
    {
        return $this->hasMany(InvestmentReturn::class)->where('status', 'paid');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted total return.
     */
    public function getFormattedTotalReturnAttribute(): string
    {
        return '$' . number_format($this->total_return, 2);
    }

    /**
     * Get formatted paid return.
     */
    public function getFormattedPaidReturnAttribute(): string
    {
        return '$' . number_format($this->paid_return, 2);
    }

    /**
     * Get remaining return amount.
     */
    public function getRemainingReturnAttribute(): float
    {
        return $this->total_return - $this->paid_return;
    }

    /**
     * Get formatted remaining return.
     */
    public function getFormattedRemainingReturnAttribute(): string
    {
        return '$' . number_format($this->getRemainingReturnAttribute(), 2);
    }

    /**
     * Get investment progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->status === 'completed') {
            return 100;
        }

        $startDate = $this->start_date instanceof Carbon ? $this->start_date : Carbon::parse($this->start_date);
        $endDate = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);
        
        $totalDays = $startDate->diffInDays($endDate);
        $passedDays = $startDate->diffInDays(now());

        return min(100, max(0, ($passedDays / $totalDays) * 100));
    }

    /**
     * Get days remaining.
     */
    public function getDaysRemainingAttribute(): int
    {
        $endDate = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);
        
        if ($this->status === 'completed' || now()->isAfter($endDate)) {
            return 0;
        }

        return now()->diffInDays($endDate);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'completed' => 'bg-primary',
            'cancelled' => 'bg-danger',
            'paused' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Get status icon.
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'active' => 'iconamoon:check-circle-duotone',
            'completed' => 'iconamoon:star-duotone',
            'cancelled' => 'iconamoon:close-circle-duotone',
            'paused' => 'iconamoon:clock-duotone',
            default => 'iconamoon:question-circle-duotone'
        };
    }

    /**
     * Get formatted start date.
     */
    public function getFormattedStartDateAttribute(): string
    {
        $startDate = $this->start_date instanceof Carbon ? $this->start_date : Carbon::parse($this->start_date);
        return $startDate->format('M d, Y');
    }

    /**
     * Get formatted end date.
     */
    public function getFormattedEndDateAttribute(): string
    {
        $endDate = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);
        return $endDate->format('M d, Y');
    }

    /**
     * Get how long ago investment was created.
     */
    public function getCreatedAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get maturity status text.
     */
    public function getMaturityStatusAttribute(): string
    {
        if ($this->status === 'completed') {
            return 'Completed';
        }

        $endDate = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);
        if (now()->isAfter($endDate)) {
            return 'Matured';
        }

        return 'Active';
    }

    /**
     * Get expected maturity amount.
     */
    public function getExpectedMaturityAmountAttribute(): float
    {
        return $this->investmentPlan->capital_return
            ? $this->amount + $this->total_return
            : $this->total_return;
    }

    /**
     * Get formatted expected maturity amount.
     */
    public function getFormattedExpectedMaturityAmountAttribute(): string
    {
        return '$' . number_format($this->getExpectedMaturityAmountAttribute(), 2);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS CHECK METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if investment is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if investment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if investment is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if investment is paused.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Check if investment has matured.
     */
    public function hasMatured(): bool
    {
        return now()->isAfter($this->ends_at);
    }

    /**
     * Check if investment is due for return payment.
     */
    public function isDueForReturn(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $plan = $this->investmentPlan;
        $nextDueDate = $this->getNextReturnDueDate();

        return $nextDueDate && now()->isAfter($nextDueDate);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATION METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get next return due date.
     */
    public function getNextReturnDueDate(): ?Carbon
    {
        if (!$this->isActive()) {
            return null;
        }

        $plan = $this->investmentPlan;
        
        // Use last_payout_date if available, otherwise use created_at for exact timestamp
        $lastReturn = $this->last_payout_date 
            ? Carbon::parse($this->last_payout_date) 
            : $this->created_at;
        
        // Ensure we have a Carbon instance
        if (!$lastReturn) {
            return null;
        }
        
        if (!$lastReturn instanceof Carbon) {
            $lastReturn = Carbon::parse($lastReturn);
        }

        // Use copy() to avoid modifying the original Carbon instance
        return match ($plan->interest_type) {
            'daily' => $lastReturn->copy()->addDay(),
            'weekly' => $lastReturn->copy()->addWeek(),
            'monthly' => $lastReturn->copy()->addMonth(),
            'yearly' => $lastReturn->copy()->addYear(),
            default => null
        };
    }

    /**
     * Calculate single return amount.
     * Supports both fixed ROI (static rate) and variable ROI (random rate within range).
     */
    public function calculateSingleReturn(): float
    {
        // Ensure investment plan is loaded
        if (!$this->relationLoaded('investmentPlan')) {
            $this->load('investmentPlan');
        }

        // Check if investment plan exists
        if (!$this->investmentPlan) {
            return 0.00;
        }

        $plan = $this->investmentPlan;
        $investmentAmount = floatval($this->amount ?? 0);

        // Determine interest rate based on ROI type
        if ($plan->roi_type === 'variable' && $plan->min_interest_rate !== null && $plan->max_interest_rate !== null) {
            // Variable ROI: generate random rate between min and max
            // Use date + plan ID as seed so all users get the same rate for the same day
            $minRate = floatval($plan->min_interest_rate);
            $maxRate = floatval($plan->max_interest_rate);
            
            // Guard: ensure valid range (min < max)
            if ($minRate >= $maxRate) {
                // Fallback to min rate if range is invalid
                $interestRate = $minRate;
            } else {
                // Create deterministic seed from today's date and plan ID
                // This ensures all users with the same plan get the same rate each day
                $dateSeed = (int) date('Ymd');
                $seed = $dateSeed * 1000 + $plan->id;
                mt_srand($seed);
                
                // Generate random rate with 2 decimal precision
                $interestRate = $minRate + (mt_rand() / mt_getrandmax()) * ($maxRate - $minRate);
                $interestRate = round($interestRate, 2);
                
                // Reset to truly random for other operations
                mt_srand();
            }
        } else {
            // Fixed ROI: use the static interest_rate
            $interestRate = floatval($plan->interest_rate ?? 0);
        }

        // Calculate return: amount × (rate / 100)
        $rate = $interestRate / 100;
        $returnAmount = $investmentAmount * $rate;

        return round($returnAmount, 2);
    }

    /**
     * Calculate total expected return.
     */
    public function calculateExpectedReturn(): float
    {
        return $this->investmentPlan->calculateTotalReturn($this->amount);
    }

    /**
     * Calculate ROI percentage.
     */
    public function getROIPercentage(): float
    {
        return ($this->total_return / $this->amount) * 100;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTION METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Complete the investment.
     */
    public function complete(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel the investment.
     */
    public function cancel(): bool
    {
        // Mark all pending returns as failed
        $this->pendingReturns()->update(['status' => 'failed']);

        return $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Pause the investment.
     */
    public function pause(): bool
    {
        return $this->update(['status' => 'paused']);
    }

    /**
     * Resume the investment.
     */
    public function resume(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Add a return payment.
     */
    public function addReturnPayment(float $amount, string $type = 'interest'): bool
    {
        $this->increment('paid_return', $amount);
        $this->update(['last_payout_date' => now()]);

        // Update return history
        $history = $this->return_history ?? [];
        $history[] = [
            'amount' => $amount,
            'type' => $type,
            'date' => now()->toISOString(),
        ];

        $this->update(['return_history' => $history]);

        return true;
    }

    /**
     * Add earnings to this investment package (ROI + commissions)
     */
    public function addEarnings(float $amount, string $type = 'roi'): bool
    {
        $this->increment('earnings_accumulated', $amount);
        
        if ($type === 'commission') {
            $this->increment('commission_earned', $amount);
        }

        return true;
    }

    /**
     * Get the expiry cap amount (principal × multiplier)
     */
    public function getExpiryCap(): float
    {
        $multiplier = $this->expiry_multiplier ?? 3;
        return floatval($this->amount) * $multiplier;
    }

    /**
     * Check if this package has reached its expiry cap
     */
    public function hasReachedExpiryCap(): bool
    {
        $cap = $this->getExpiryCap();
        $earnings = floatval($this->earnings_accumulated ?? 0);
        return $earnings >= $cap;
    }

    /**
     * Get remaining earnings until expiry cap
     */
    public function getRemainingUntilCap(): float
    {
        $cap = $this->getExpiryCap();
        $earnings = floatval($this->earnings_accumulated ?? 0);
        return max(0, $cap - $earnings);
    }

    /**
     * Get earnings progress percentage
     */
    public function getEarningsProgressPercentage(): float
    {
        $cap = $this->getExpiryCap();
        if ($cap <= 0) return 0;
        $earnings = floatval($this->earnings_accumulated ?? 0);
        return min(100, ($earnings / $cap) * 100);
    }

    /**
     * Expire this package due to reaching cap
     */
    public function expireDueToCap(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'status_reason' => 'Reached ' . $this->expiry_multiplier . 'x earnings cap'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for active investments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed investments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled investments.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for paused investments.
     */
    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    /**
     * Scope for matured investments.
     */
    public function scopeMatured($query)
    {
        return $query->where('end_date', '<=', now());
    }

    /**
     * Scope for investments due for return.
     */
    public function scopeDueForReturn($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('last_payout_date')
                    ->orWhere('last_payout_date', '<=', now()->subDay());
            });
    }

    /**
     * Scope by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope by investment plan.
     */
    public function scopeByPlan($query, $planId)
    {
        return $query->where('investment_plan_id', $planId);
    }

    /**
     * Scope with related data.
     */
    public function scopeWithDetails($query)
    {
        return $query->with(['user', 'investmentPlan', 'returns']);
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get available statuses.
     */
    public static function getAvailableStatuses(): array
    {
        return [
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'paused' => 'Paused',
        ];
    }

    /**
     * Get statistics for dashboard.
     */
    public static function getStatistics(): array
    {
        return [
            'total_investments' => self::count(),
            'active_investments' => self::active()->count(),
            'completed_investments' => self::completed()->count(),
            'total_invested_amount' => self::sum('amount'),
            'total_returns_paid' => self::sum('paid_return'),
            'matured_investments' => self::matured()->count(),
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($investment) {
            $plan = $investment->investmentPlan;

            if ($plan) {
                // Calculate total return if not already set
                if (!$investment->total_return) {
                    $investment->total_return = $plan->calculateTotalReturn($investment->amount);
                }

                // Set end date if not already set
                if (!$investment->end_date && $investment->start_date) {
                    $startDate = $investment->start_date instanceof \Carbon\Carbon 
                        ? $investment->start_date 
                        : \Carbon\Carbon::parse($investment->start_date);
                    $investment->end_date = $startDate->addDays($plan->duration_days ?? 90);
                }
            }
        });

        static::created(function ($investment) {
            // Update plan statistics
            if ($investment->investmentPlan) {
                $investment->investmentPlan->addInvestment($investment->amount);
            }
        });
    }

    /**
     * Create return schedule for this investment.
     */
    public function createReturnSchedule(): void
    {
        $plan = $this->investmentPlan;
        if (!$plan) {
            return;
        }

        $periods = $plan->getReturnPeriods();
        $startDate = $this->start_date instanceof \Carbon\Carbon 
            ? $this->start_date 
            : \Carbon\Carbon::parse($this->start_date);
        $endDate = $this->end_date instanceof \Carbon\Carbon 
            ? $this->end_date 
            : \Carbon\Carbon::parse($this->end_date);

        for ($i = 0; $i < $periods; $i++) {
            $dueDate = match ($plan->interest_type) {
                'daily' => $startDate->copy()->addDays($i + 1),
                'weekly' => $startDate->copy()->addWeeks($i + 1),
                'monthly' => $startDate->copy()->addMonths($i + 1),
                'yearly' => $startDate->copy()->addYears($i + 1),
                default => $startDate->copy()->addDays($i + 1)
            };

            // Don't create returns beyond the investment end date
            if ($dueDate->isAfter($endDate)) {
                break;
            }

            InvestmentReturn::create([
                'user_investment_id' => $this->id,
                'user_id' => $this->user_id,
                'amount' => $this->calculateSingleReturn(),
                'type' => 'interest',
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);
        }

        // Create capital return if applicable
        if ($plan->capital_return) {
            InvestmentReturn::create([
                'user_investment_id' => $this->id,
                'user_id' => $this->user_id,
                'amount' => $this->amount,
                'type' => 'capital',
                'due_date' => $endDate,
                'status' => 'pending',
            ]);
        }
    }
}