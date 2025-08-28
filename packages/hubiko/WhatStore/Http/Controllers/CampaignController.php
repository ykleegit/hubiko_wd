<?php

namespace Modules\WhatStore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WhatStore\Entities\Campaign;
use Modules\WhatStore\Entities\Template;
use Modules\WhatStore\Entities\Customer;
use Modules\WhatStore\Entities\CustomerGroup;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $campaigns = Campaign::forWorkspace()
            ->with(['template'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('whatstore::campaigns.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new campaign
     */
    public function create()
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $templates = Template::forWorkspace()->approved()->get();
        $customerGroups = CustomerGroup::forWorkspace()->get();

        return view('whatstore::campaigns.create', compact('templates', 'customerGroups'));
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:whatstore_templates,id',
            'target_type' => 'required|in:all_customers,segment,specific_customers',
            'target_criteria' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
            'is_bot' => 'boolean',
            'bot_type' => 'nullable|in:exact_match,contains',
            'trigger' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $campaign = Campaign::create([
            'name' => $request->name,
            'description' => $request->description,
            'template_id' => $request->template_id,
            'target_type' => $request->target_type,
            'target_criteria' => $request->target_criteria,
            'scheduled_at' => $request->scheduled_at,
            'is_bot' => $request->boolean('is_bot'),
            'is_bot_active' => $request->boolean('is_bot') && $request->boolean('is_bot_active'),
            'bot_type' => $request->bot_type,
            'trigger' => $request->trigger,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
        ]);

        return redirect()->route('whatstore.campaigns.show', $campaign)
            ->with('success', __('Campaign created successfully.'));
    }

    /**
     * Display the specified campaign
     */
    public function show(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        $campaign->load(['template', 'messages.customer']);

        return view('whatstore::campaigns.show', compact('campaign'));
    }

    /**
     * Show the form for editing the campaign
     */
    public function edit(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if (in_array($campaign->status, ['running', 'completed'])) {
            return redirect()->back()->with('error', __('Cannot edit running or completed campaigns.'));
        }

        $templates = Template::forWorkspace()->approved()->get();
        $customerGroups = CustomerGroup::forWorkspace()->get();

        return view('whatstore::campaigns.edit', compact('campaign', 'templates', 'customerGroups'));
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if (in_array($campaign->status, ['running', 'completed'])) {
            return redirect()->back()->with('error', __('Cannot edit running or completed campaigns.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:whatstore_templates,id',
            'target_type' => 'required|in:all_customers,segment,specific_customers',
            'target_criteria' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
            'is_bot' => 'boolean',
            'bot_type' => 'nullable|in:exact_match,contains',
            'trigger' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $campaign->update([
            'name' => $request->name,
            'description' => $request->description,
            'template_id' => $request->template_id,
            'target_type' => $request->target_type,
            'target_criteria' => $request->target_criteria,
            'scheduled_at' => $request->scheduled_at,
            'is_bot' => $request->boolean('is_bot'),
            'is_bot_active' => $request->boolean('is_bot') && $request->boolean('is_bot_active'),
            'bot_type' => $request->bot_type,
            'trigger' => $request->trigger,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
        ]);

        return redirect()->route('whatstore.campaigns.show', $campaign)
            ->with('success', __('Campaign updated successfully.'));
    }

    /**
     * Remove the specified campaign
     */
    public function destroy(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if ($campaign->status === 'running') {
            return redirect()->back()->with('error', __('Cannot delete running campaigns.'));
        }

        $campaign->delete();

        return redirect()->route('whatstore.campaigns.index')
            ->with('success', __('Campaign deleted successfully.'));
    }

    /**
     * Launch campaign immediately
     */
    public function launch(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if (!in_array($campaign->status, ['draft', 'scheduled', 'paused'])) {
            return redirect()->back()->with('error', __('Campaign cannot be launched.'));
        }

        try {
            $messages = $campaign->generateMessages();
            
            $campaign->update([
                'status' => 'running',
                'started_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', __('Campaign launched successfully. :count messages queued.', ['count' => count($messages)]));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to launch campaign: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Pause campaign
     */
    public function pause(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if ($campaign->status !== 'running') {
            return redirect()->back()->with('error', __('Only running campaigns can be paused.'));
        }

        $campaign->update(['status' => 'paused']);

        return redirect()->back()
            ->with('success', __('Campaign paused successfully.'));
    }

    /**
     * Resume paused campaign
     */
    public function resume(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if ($campaign->status !== 'paused') {
            return redirect()->back()->with('error', __('Only paused campaigns can be resumed.'));
        }

        $campaign->update(['status' => 'running']);

        return redirect()->back()
            ->with('success', __('Campaign resumed successfully.'));
    }

    /**
     * Cancel campaign
     */
    public function cancel(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        if (in_array($campaign->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', __('Campaign is already completed or cancelled.'));
        }

        $campaign->update(['status' => 'cancelled']);

        return redirect()->back()
            ->with('success', __('Campaign cancelled successfully.'));
    }

    /**
     * Duplicate campaign
     */
    public function duplicate(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        $newCampaign = $campaign->replicate();
        $newCampaign->name = $campaign->name . ' (Copy)';
        $newCampaign->status = 'draft';
        $newCampaign->scheduled_at = null;
        $newCampaign->started_at = null;
        $newCampaign->completed_at = null;
        $newCampaign->total_recipients = 0;
        $newCampaign->sent_count = 0;
        $newCampaign->delivered_count = 0;
        $newCampaign->read_count = 0;
        $newCampaign->replied_count = 0;
        $newCampaign->used = 0;
        $newCampaign->save();

        return redirect()->route('whatstore.campaigns.edit', $newCampaign)
            ->with('success', __('Campaign duplicated successfully.'));
    }

    /**
     * Get campaign analytics
     */
    public function analytics(Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        $analytics = [
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'delivered_count' => $campaign->delivered_count,
            'read_count' => $campaign->read_count,
            'replied_count' => $campaign->replied_count,
            'delivery_rate' => $campaign->delivery_rate,
            'read_rate' => $campaign->read_rate,
            'reply_rate' => $campaign->reply_rate,
        ];

        return view('whatstore::campaigns.analytics', compact('campaign', 'analytics'));
    }

    /**
     * Test campaign with single customer
     */
    public function test(Request $request, Campaign $campaign)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($campaign->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Campaign not found.'));
        }

        $validator = Validator::make($request->all(), [
            'test_phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            // Find or create test customer
            $testCustomer = Customer::forWorkspace()
                ->where('whatsapp_number', $request->test_phone)
                ->first();

            if (!$testCustomer) {
                $testCustomer = Customer::create([
                    'whatsapp_number' => $request->test_phone,
                    'name' => 'Test Customer',
                ]);
            }

            $messages = $campaign->generateMessages(null, $testCustomer);

            return redirect()->back()
                ->with('success', __('Test message sent successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to send test message: :error', ['error' => $e->getMessage()]));
        }
    }
}
