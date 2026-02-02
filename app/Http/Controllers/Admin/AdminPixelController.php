<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingPixel;
use Illuminate\Http\Request;

class AdminPixelController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $platforms = TrackingPixel::getAvailablePlatforms();
        $pixels = TrackingPixel::all()->keyBy('platform');

        return view('admin.pixels.index', compact('platforms', 'pixels', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pixels' => 'required|array',
            'pixels.*.platform' => 'required|string|in:facebook,google,tiktok',
            'pixels.*.pixel_id' => 'nullable|string|max:255',
            'pixels.*.pixel_code' => 'nullable|string',
            'pixels.*.is_active' => 'nullable|boolean',
        ]);

        foreach ($request->pixels as $pixelData) {
            TrackingPixel::updateOrCreate(
                ['platform' => $pixelData['platform']],
                [
                    'pixel_id' => $pixelData['pixel_id'] ?? null,
                    'pixel_code' => $pixelData['pixel_code'] ?? null,
                    'is_active' => isset($pixelData['is_active']) && $pixelData['is_active'],
                ]
            );
        }

        return redirect()->route('admin.pixels.index')
            ->with('success', 'Tracking pixels updated successfully.');
    }

    public function toggle(Request $request, string $platform)
    {
        $pixel = TrackingPixel::where('platform', $platform)->first();

        if ($pixel) {
            $pixel->update(['is_active' => !$pixel->is_active]);
            $status = $pixel->is_active ? 'enabled' : 'disabled';
        } else {
            return response()->json(['success' => false, 'message' => 'Pixel not found'], 404);
        }

        return response()->json([
            'success' => true,
            'is_active' => $pixel->is_active,
            'message' => ucfirst($platform) . " pixel {$status} successfully.",
        ]);
    }
}
