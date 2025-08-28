<?php

namespace Hubiko\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;

class SettingsController extends Controller
{
    /**
     * Store ticket module settings.
     */
    public function store(Request $request)
    {
        if (!\Auth::user()->isAbleTo('ticket manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        $settings = [
            'allow_customer_tickets' => $request->has('allow_customer_tickets') ? 'on' : 'off',
            'allow_file_uploads' => $request->has('allow_file_uploads') ? 'on' : 'off',
            'max_file_size' => $request->max_file_size,
            'allowed_file_types' => $request->allowed_file_types,
            'notify_admin_on_new_ticket' => $request->has('notify_admin_on_new_ticket') ? 'on' : 'off',
            'notify_customer_on_ticket_status_change' => $request->has('notify_customer_on_ticket_status_change') ? 'on' : 'off',
            'notify_agent_on_ticket_assignment' => $request->has('notify_agent_on_ticket_assignment') ? 'on' : 'off',
        ];
        
        foreach ($settings as $key => $value) {
            Settings::updateOrCreate(
                [
                    'name' => $key,
                    'created_by' => creatorId(),
                    'workspace' => getActiveWorkSpace(),
                ],
                [
                    'value' => $value
                ]
            );
        }
        
        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }
} 