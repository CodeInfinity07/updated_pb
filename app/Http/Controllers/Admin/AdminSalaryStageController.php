<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryStage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminSalaryStageController extends Controller
{
    public function index()
    {
        $stages = SalaryStage::ordered()->get();

        return view('admin.salary.stages.index', compact('stages'));
    }

    public function edit(SalaryStage $stage)
    {
        return view('admin.salary.stages.edit', compact('stage'));
    }

    public function update(Request $request, SalaryStage $stage): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'direct_members_required' => 'required|integer|min:0',
            'self_deposit_required' => 'required|numeric|min:0',
            'team_required' => 'required|integer|min:0',
            'salary_amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $stage->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Salary stage updated successfully',
            'stage' => $stage->fresh(),
        ]);
    }

    public function toggleActive(SalaryStage $stage): JsonResponse
    {
        $stage->update(['is_active' => !$stage->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Stage ' . ($stage->is_active ? 'activated' : 'deactivated') . ' successfully',
            'is_active' => $stage->is_active,
        ]);
    }
}
