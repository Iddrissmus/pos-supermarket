<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all();
        return view('superadmin.plans.index', compact('plans'));
    }

    public function edit(SubscriptionPlan $plan)
    {
        $featureGroups = config('features.groups');
        return view('superadmin.plans.edit', compact('plan', 'featureGroups'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        // If features came from checkboxes, they are already in the features array
        // We validate that features is an array
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'max_branches' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);
        
        // Checkbox handling for is_active
        $validated['is_active'] = $request->has('is_active');

        $plan->update($validated);

        // Sync max_branches to all businesses subscribed to this plan
        if ($plan->wasChanged('max_branches')) {
            \App\Models\Business::where('current_plan_id', $plan->id)
                ->update(['max_branches' => $validated['max_branches']]);
        }

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plan updated successfully. Changes synced to subscribers.');
    }
}
