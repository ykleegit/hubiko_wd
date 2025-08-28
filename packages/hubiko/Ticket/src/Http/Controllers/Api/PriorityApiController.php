<?php

namespace Hubiko\Ticket\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hubiko\Ticket\Entities\Priority;

class PriorityApiController extends Controller
{
    /**
     * Display a listing of priorities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket manage')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $query = Priority::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId());
        
        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Get paginated results or all results
        if ($request->has('per_page')) {
            $priorities = $query->paginate($request->per_page);
        } else {
            $priorities = $query->get();
        }
        
        return response()->json($priorities);
    }

    /**
     * Store a newly created priority.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket create')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Create priority
        $priority = new Priority();
        $priority->name = $request->name;
        $priority->description = $request->description;
        $priority->color = $request->color ?? '#3498db';
        $priority->created_by = creatorId();
        $priority->workspace = getActiveWorkSpace();
        $priority->save();
        
        return response()->json([
            'message' => 'Priority created successfully',
            'priority' => $priority
        ], 201);
    }

    /**
     * Display the specified priority.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket show')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $priority = Priority::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if (!$priority) {
            return response()->json(['error' => 'Priority not found'], 404);
        }
        
        return response()->json($priority);
    }

    /**
     * Update the specified priority.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket edit')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $priority = Priority::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if (!$priority) {
            return response()->json(['error' => 'Priority not found'], 404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Update priority
        $priority->name = $request->name;
        $priority->description = $request->description;
        if ($request->has('color')) {
            $priority->color = $request->color;
        }
        $priority->save();
        
        return response()->json([
            'message' => 'Priority updated successfully',
            'priority' => $priority
        ]);
    }

    /**
     * Remove the specified priority.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket delete')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $priority = Priority::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if (!$priority) {
            return response()->json(['error' => 'Priority not found'], 404);
        }
        
        // Check if priority is being used in tickets
        $ticketsCount = \Hubiko\Ticket\Entities\Ticket::where('priority', $id)
            ->where('workspace', getActiveWorkSpace())
            ->count();
            
        if ($ticketsCount > 0) {
            return response()->json([
                'error' => 'Priority cannot be deleted because it is being used in tickets'
            ], 422);
        }
        
        $priority->delete();
        
        return response()->json([
            'message' => 'Priority deleted successfully'
        ]);
    }
} 