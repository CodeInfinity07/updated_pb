<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommissionLevel;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminCommissionController extends Controller
{
    public function index(): View
    {
        try {
            $user = \Auth::user();
            $levels = ReferralCommissionLevel::orderBy('level')->get();
            
            if ($levels->isEmpty()) {
                ReferralCommissionLevel::seedDefaults();
                $levels = ReferralCommissionLevel::orderBy('level')->get();
            }

            $directSponsorCommission = Setting::getValue('direct_sponsor_commission', 8);
            $profitSharingShieldEnabled = Setting::getValue('profit_sharing_shield_enabled', false);
            $profitSharingShieldMinInvestment = Setting::getValue('profit_sharing_shield_min_investment', 0);

            return view('admin.commission.index', compact('levels', 'user', 'directSponsorCommission', 'profitSharingShieldEnabled', 'profitSharingShieldMinInvestment'));

        } catch (Exception $e) {
            Log::error('Commission settings page load failed', [
                'error' => $e->getMessage()
            ]);

            return view('admin.commission.index', [
                'levels' => collect([]),
                'directSponsorCommission' => 8,
                'user' => \Auth::user()
            ])->with('error', 'Failed to load commission settings.');
        }
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'levels' => 'required|array',
            'levels.*.level' => 'required|integer|min:1|max:10',
            'levels.*.percentage' => 'required|numeric|min:0|max:100',
            'direct_sponsor_commission' => 'nullable|numeric|min:0|max:100',
            'profit_sharing_shield_enabled' => 'nullable|boolean',
            'profit_sharing_shield_min_investment' => 'nullable|numeric|min:0',
        ]);

        try {
            foreach ($request->levels as $levelData) {
                ReferralCommissionLevel::updateOrCreate(
                    ['level' => $levelData['level']],
                    [
                        'percentage' => $levelData['percentage'],
                        'is_active' => true
                    ]
                );
            }

            if ($request->has('direct_sponsor_commission')) {
                Setting::setValue(
                    'direct_sponsor_commission',
                    $request->direct_sponsor_commission,
                    'float',
                    'commission',
                    'Percentage commission for direct sponsor on every investment'
                );
            }

            Setting::setValue(
                'profit_sharing_shield_enabled',
                $request->boolean('profit_sharing_shield_enabled'),
                'boolean',
                'commission',
                'When enabled, users must have at least N direct referrals to receive Level N profit share'
            );

            Setting::setValue(
                'profit_sharing_shield_min_investment',
                $request->input('profit_sharing_shield_min_investment', 0),
                'float',
                'commission',
                'Minimum combined investment required from N direct referrals to receive Level N profit share'
            );

            Log::info('Commission levels updated', [
                'levels' => $request->levels,
                'direct_sponsor_commission' => $request->direct_sponsor_commission
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commission settings updated successfully.'
            ]);

        } catch (Exception $e) {
            Log::error('Commission levels update failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update commission levels.'
            ], 500);
        }
    }

    public function resetDefaults(): JsonResponse
    {
        try {
            ReferralCommissionLevel::seedDefaults();

            return response()->json([
                'success' => true,
                'message' => 'Commission levels reset to defaults.'
            ]);

        } catch (Exception $e) {
            Log::error('Commission levels reset failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset commission levels.'
            ], 500);
        }
    }

    public function calculatePreview(Request $request): JsonResponse
    {
        $request->validate([
            'roi_amount' => 'required|numeric|min:0.01'
        ]);

        try {
            $roiAmount = $request->roi_amount;
            $levels = ReferralCommissionLevel::where('is_active', true)
                ->orderBy('level')
                ->get();

            $commissions = [];
            $totalCommission = 0;

            foreach ($levels as $level) {
                $commission = ($roiAmount * $level->percentage) / 100;
                $commissions[] = [
                    'level' => $level->level,
                    'percentage' => $level->percentage,
                    'commission' => round($commission, 2)
                ];
                $totalCommission += $commission;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'roi_amount' => $roiAmount,
                    'commissions' => $commissions,
                    'total_commission' => round($totalCommission, 2)
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate preview.'
            ], 500);
        }
    }
}
