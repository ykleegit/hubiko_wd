<?php

namespace Hubiko\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\Ticket\Entities\Priority;

class PriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->can('ticket priority manage')) {
            $priorities = Priority::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            return view('ticket::priorities.index', compact('priorities'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->can('ticket priority create')) {
            return view('ticket::priorities.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('ticket priority create')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
                'color' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $priority = new Priority();
            $priority->name = $request->name;
            $priority->color = $request->color;
            $priority->created_by = creatorId();
            $priority->workspace = getActiveWorkSpace();
            $priority->save();

            return redirect()->route('ticket-priority.index')->with('success', __('Priority created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (Auth::user()->can('ticket priority edit')) {
            $priority = Priority::find($id);
            
            if (!$priority || $priority->workspace != getActiveWorkSpace() || $priority->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Priority not found.'));
            }
            
            return view('ticket::priorities.edit', compact('priority'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::user()->can('ticket priority edit')) {
            $priority = Priority::find($id);
            
            if (!$priority || $priority->workspace != getActiveWorkSpace() || $priority->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Priority not found.'));
            }
            
            $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
                'color' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $priority->name = $request->name;
            $priority->color = $request->color;
            $priority->save();

            return redirect()->route('ticket-priority.index')->with('success', __('Priority updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::user()->can('ticket priority delete')) {
            $priority = Priority::find($id);
            
            if (!$priority || $priority->workspace != getActiveWorkSpace() || $priority->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Priority not found.'));
            }
            
            // Check if priority has tickets
            if ($priority->tickets()->count() > 0) {
                return redirect()->back()->with('error', __('Cannot delete priority that has tickets.'));
            }
            
            $priority->delete();

            return redirect()->route('ticket-priority.index')->with('success', __('Priority deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
} 