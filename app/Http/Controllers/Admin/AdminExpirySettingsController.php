<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentExpirySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminExpirySettingsController extends Controller
{
    public function index()
    {
        $settings = InvestmentExpirySetting::getAllSettings();
        
        return view('admin.expiry-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'base_multiplier' => 'required|numeric|min:1|max:20',
            'extended_multiplier' => 'required|numeric|min:1|max:20',
            'bot_fee_amount' => 'required|numeric|min:0|max:1000',
            'direct_referral_count' => 'required|integer|min:0|max:1000',
            'level_1_referrals' => 'required|integer|min:0|max:1000',
            'level_2_referrals' => 'required|integer|min:0|max:1000',
            'level_3_referrals' => 'required|integer|min:0|max:1000',
            'level_4_referrals' => 'required|integer|min:0|max:1000',
            'level_5_referrals' => 'required|integer|min:0|max:1000',
        ]);

        InvestmentExpirySetting::setValue('base_multiplier', $validated['base_multiplier']);
        InvestmentExpirySetting::setValue('extended_multiplier', $validated['extended_multiplier']);
        InvestmentExpirySetting::setValue('bot_fee_amount', $validated['bot_fee_amount']);

        InvestmentExpirySetting::setValue('qualification_option_1', [
            'type' => 'direct_referrals',
            'count' => (int) $validated['direct_referral_count']
        ]);

        InvestmentExpirySetting::setValue('qualification_option_2', [
            'type' => 'tiered_referrals',
            'levels' => [
                '1' => (int) $validated['level_1_referrals'],
                '2' => (int) $validated['level_2_referrals'],
                '3' => (int) $validated['level_3_referrals'],
                '4' => (int) $validated['level_4_referrals'],
                '5' => (int) $validated['level_5_referrals'],
            ]
        ]);

        Log::info('Expiry settings updated', $validated);

        return redirect()->route('admin.expiry-settings.index')
            ->with('success', 'Expiry settings updated successfully!');
    }

    public function reset()
    {
        InvestmentExpirySetting::setValue('base_multiplier', '3');
        InvestmentExpirySetting::setValue('extended_multiplier', '6');
        InvestmentExpirySetting::setValue('bot_fee_amount', '10');
        
        InvestmentExpirySetting::setValue('qualification_option_1', [
            'type' => 'direct_referrals',
            'count' => 30
        ]);

        InvestmentExpirySetting::setValue('qualification_option_2', [
            'type' => 'tiered_referrals',
            'levels' => ['1' => 10, '2' => 8, '3' => 5, '4' => 3, '5' => 1]
        ]);

        Log::info('Expiry settings reset to defaults');

        return redirect()->route('admin.expiry-settings.index')
            ->with('success', 'Expiry settings reset to defaults!');
    }
}
