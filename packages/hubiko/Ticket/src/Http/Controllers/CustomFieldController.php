<?php

namespace Hubiko\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\Ticket\Entities\CustomField;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('ticket customfield manage')) {
            $customFields = CustomField::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->where('module', 'ticket')
                ->get();
                
            return view('ticket::custom_fields.index', compact('customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('ticket customfield create')) {
            return view('ticket::custom_fields.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('ticket customfield create')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
                'type' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $customField = new CustomField();
            $customField->name = $request->name;
            $customField->type = $request->type;
            $customField->module = 'ticket';
            
            if (in_array($request->type, ['select', 'radio', 'checkbox'])) {
                if (empty($request->field_values)) {
                    return redirect()->back()->with('error', __('Field values are required for this field type.'));
                }
                $customField->field_value = $request->field_values;
            }
            
            $customField->created_by = creatorId();
            $customField->workspace = getActiveWorkSpace();
            $customField->save();

            return redirect()->route('ticket-customfield.index')->with('success', __('Custom field created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (Auth::user()->isAbleTo('ticket customfield edit')) {
            $customField = CustomField::find($id);
            
            if (!$customField || $customField->workspace != getActiveWorkSpace() || $customField->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Custom field not found.'));
            }
            
            return view('ticket::custom_fields.edit', compact('customField'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::user()->isAbleTo('ticket customfield edit')) {
            $customField = CustomField::find($id);
            
            if (!$customField || $customField->workspace != getActiveWorkSpace() || $customField->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Custom field not found.'));
            }
            
            $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $customField->name = $request->name;
            
            if (in_array($customField->type, ['select', 'radio', 'checkbox'])) {
                if (empty($request->field_values)) {
                    return redirect()->back()->with('error', __('Field values are required for this field type.'));
                }
                $customField->field_value = $request->field_values;
            }
            
            $customField->save();

            return redirect()->route('ticket-customfield.index')->with('success', __('Custom field updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::user()->isAbleTo('ticket customfield delete')) {
            $customField = CustomField::find($id);
            
            if (!$customField || $customField->workspace != getActiveWorkSpace() || $customField->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Custom field not found.'));
            }
            
            // Delete all custom field values associated with this field
            \App\Models\CustomFieldValue::where('field_id', $customField->id)
                ->where('module', 'ticket')
                ->delete();
                
            $customField->delete();

            return redirect()->route('ticket-customfield.index')->with('success', __('Custom field deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
} 