<?php

namespace Modules\WhatStore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WhatStore\Entities\Customer;
use Modules\WhatStore\Entities\Message;
use Modules\WhatStore\Entities\Reply;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Display chat dashboard
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customers = Customer::forWorkspace()
            ->where('has_chat', true)
            ->when($request->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('whatsapp_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function($query, $status) {
                return match($status) {
                    'unread' => $query->where('is_last_message_by_customer', true),
                    'subscribed' => $query->where('subscribed', true),
                    'unsubscribed' => $query->where('subscribed', false),
                    default => $query
                };
            })
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_reply_at', 'desc')
            ->paginate(20);

        return view('whatstore::chat.index', compact('customers'));
    }

    /**
     * Show chat conversation with specific customer
     */
    public function show(Customer $customer)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($customer->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Customer not found.'));
        }

        $messages = Message::where('customer_id', $customer->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark customer messages as read
        $customer->update(['is_last_message_by_customer' => false]);

        return view('whatstore::chat.show', compact('customer', 'messages'));
    }

    /**
     * Send message to customer
     */
    public function sendMessage(Request $request, Customer $customer)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if ($customer->workspace != getActiveWorkSpace()) {
            return response()->json(['error' => __('Customer not found.')], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:4096',
            'type' => 'in:text,image,video,document,audio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $message = $customer->sendMessage(
                $request->message,
                false, // not from customer
                false, // not campaign message
                $request->type ?? 'text'
            );

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->display_content,
                    'type' => $message->type,
                    'created_at' => $message->created_at->format('H:i'),
                    'sender_name' => auth()->user()->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add note to customer
     */
    public function addNote(Request $request, Customer $customer)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if ($customer->workspace != getActiveWorkSpace()) {
            return response()->json(['error' => __('Customer not found.')], 404);
        }

        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $note = $customer->addNote($request->note);

            return response()->json([
                'success' => true,
                'note' => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'created_at' => $note->created_at->format('M d, Y H:i'),
                    'sender_name' => auth()->user()->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle customer subscription status
     */
    public function toggleSubscription(Customer $customer)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if ($customer->workspace != getActiveWorkSpace()) {
            return response()->json(['error' => __('Customer not found.')], 404);
        }

        $customer->update(['subscribed' => !$customer->subscribed]);

        return response()->json([
            'success' => true,
            'subscribed' => $customer->subscribed,
            'message' => $customer->subscribed 
                ? __('Customer subscribed successfully.') 
                : __('Customer unsubscribed successfully.')
        ]);
    }

    /**
     * Toggle AI bot for customer
     */
    public function toggleBot(Customer $customer)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if ($customer->workspace != getActiveWorkSpace()) {
            return response()->json(['error' => __('Customer not found.')], 404);
        }

        $customer->update(['enabled_ai_bot' => !$customer->enabled_ai_bot]);

        return response()->json([
            'success' => true,
            'enabled_ai_bot' => $customer->enabled_ai_bot,
            'message' => $customer->enabled_ai_bot 
                ? __('AI bot enabled for customer.') 
                : __('AI bot disabled for customer.')
        ]);
    }

    /**
     * Get chat statistics
     */
    public function statistics()
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $stats = [
            'total_customers' => Customer::forWorkspace()->count(),
            'active_chats' => Customer::forWorkspace()->where('has_chat', true)->count(),
            'unread_messages' => Customer::forWorkspace()->where('is_last_message_by_customer', true)->count(),
            'subscribed_customers' => Customer::forWorkspace()->where('subscribed', true)->count(),
            'messages_today' => Message::forWorkspace()->whereDate('created_at', today())->count(),
            'bot_replies_today' => Message::forWorkspace()
                ->where('is_automated', true)
                ->whereDate('created_at', today())
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get recent messages for live updates
     */
    public function recentMessages(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $lastMessageId = $request->get('last_message_id', 0);

        $messages = Message::forWorkspace()
            ->where('id', '>', $lastMessageId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'customer_id' => $message->customer_id,
                    'customer_name' => $message->customer->name ?? $message->customer->whatsapp_number,
                    'content' => $message->display_content,
                    'is_from_customer' => $message->is_from_customer,
                    'created_at' => $message->created_at->format('H:i'),
                ];
            })
        ]);
    }

    /**
     * Search customers and messages
     */
    public function search(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json(['customers' => [], 'messages' => []]);
        }

        $customers = Customer::forWorkspace()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('whatsapp_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $messages = Message::forWorkspace()
            ->where('content', 'like', "%{$query}%")
            ->with('customer')
            ->limit(10)
            ->get();

        return response()->json([
            'customers' => $customers->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name ?? $customer->whatsapp_number,
                    'whatsapp_number' => $customer->whatsapp_number,
                    'last_message' => $customer->last_message,
                ];
            }),
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'customer_name' => $message->customer->name ?? $message->customer->whatsapp_number,
                    'content' => $message->display_content,
                    'created_at' => $message->created_at->format('M d, H:i'),
                ];
            })
        ]);
    }
}
