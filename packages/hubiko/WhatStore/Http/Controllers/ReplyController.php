<?php

namespace Modules\WhatStore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WhatStore\Entities\Reply;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{
    /**
     * Display a listing of replies
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $replies = Reply::forWorkspace()
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('trigger_keywords', 'like', "%{$search}%");
            })
            ->when($request->status, function($query, $status) {
                return $query->where('is_active', $status === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('whatstore::replies.index', compact('replies'));
    }

    /**
     * Show the form for creating a new reply
     */
    public function create()
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('whatstore::replies.create');
    }

    /**
     * Store a newly created reply
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_keywords' => 'required|string',
            'match_type' => 'required|in:exact,contains,starts_with,ends_with',
            'reply_text' => 'required|string|max:4096',
            'header_text' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:255',
            'button1' => 'nullable|string|max:20',
            'button1_id' => 'nullable|string|max:256',
            'button2' => 'nullable|string|max:20',
            'button2_id' => 'nullable|string|max:256',
            'button3' => 'nullable|string|max:20',
            'button3_id' => 'nullable|string|max:256',
            'button_name' => 'nullable|string|max:20',
            'button_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Reply::create($request->all());

        return redirect()->route('whatstore.replies.index')
            ->with('success', __('Reply created successfully.'));
    }

    /**
     * Display the specified reply
     */
    public function show(Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Reply not found.'));
        }

        return view('whatstore::replies.show', compact('reply'));
    }

    /**
     * Show the form for editing the reply
     */
    public function edit(Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Reply not found.'));
        }

        return view('whatstore::replies.edit', compact('reply'));
    }

    /**
     * Update the specified reply
     */
    public function update(Request $request, Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Reply not found.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_keywords' => 'required|string',
            'match_type' => 'required|in:exact,contains,starts_with,ends_with',
            'reply_text' => 'required|string|max:4096',
            'header_text' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:255',
            'button1' => 'nullable|string|max:20',
            'button1_id' => 'nullable|string|max:256',
            'button2' => 'nullable|string|max:20',
            'button2_id' => 'nullable|string|max:256',
            'button3' => 'nullable|string|max:20',
            'button3_id' => 'nullable|string|max:256',
            'button_name' => 'nullable|string|max:20',
            'button_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $reply->update($request->all());

        return redirect()->route('whatstore.replies.index')
            ->with('success', __('Reply updated successfully.'));
    }

    /**
     * Remove the specified reply
     */
    public function destroy(Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Reply not found.'));
        }

        $reply->delete();

        return redirect()->route('whatstore.replies.index')
            ->with('success', __('Reply deleted successfully.'));
    }

    /**
     * Toggle reply active status
     */
    public function toggle(Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return response()->json(['error' => __('Reply not found.')], 404);
        }

        $reply->update(['is_active' => !$reply->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $reply->is_active,
            'message' => $reply->is_active 
                ? __('Reply activated successfully.') 
                : __('Reply deactivated successfully.')
        ]);
    }

    /**
     * Test reply with sample message
     */
    public function test(Request $request, Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return response()->json(['error' => __('Reply not found.')], 404);
        }

        $validator = Validator::make($request->all(), [
            'test_message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a dummy customer for testing
        $dummyCustomer = new \Modules\WhatStore\Entities\Customer([
            'name' => 'Test Customer',
            'whatsapp_number' => '+1234567890',
        ]);

        $shouldTrigger = $reply->shouldTrigger($request->test_message, $dummyCustomer);

        if ($shouldTrigger) {
            $replyData = $reply->generateReply($dummyCustomer);
            return response()->json([
                'success' => true,
                'triggered' => true,
                'reply_preview' => [
                    'content' => $replyData['content'],
                    'header_text' => $replyData['header_text'],
                    'footer_text' => $replyData['footer_text'],
                    'buttons' => json_decode($replyData['buttons'], true),
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'triggered' => false,
            'message' => __('Reply would not trigger for this message.')
        ]);
    }

    /**
     * Duplicate reply
     */
    public function duplicate(Reply $reply)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($reply->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Reply not found.'));
        }

        $newReply = $reply->replicate();
        $newReply->name = $reply->name . ' (Copy)';
        $newReply->is_active = false;
        $newReply->usage_count = 0;
        $newReply->save();

        return redirect()->route('whatstore.replies.edit', $newReply)
            ->with('success', __('Reply duplicated successfully.'));
    }
}
