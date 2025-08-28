<?php

namespace Hubiko\Ticket\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hubiko\Ticket\Entities\Ticket;
use Hubiko\Ticket\Entities\Conversion;
use Hubiko\Ticket\Events\CreateTicket;
use Hubiko\Ticket\Events\UpdateTicket;
use Hubiko\Ticket\Events\DestroyTicket;
use Hubiko\Ticket\Events\TicketReply;
use Hubiko\Ticket\Events\UpdateTicketStatus;

class TicketApiController extends Controller
{
    /**
     * Display a listing of tickets.
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

        $query = Ticket::where('workspace', getActiveWorkSpace());
        
        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('is_assign') && $request->is_assign) {
            $query->where('is_assign', $request->is_assign);
        }
        
        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%")
                  ->orWhere('ticket_id', 'LIKE', "%{$search}%");
            });
        }
        
        // Get paginated results
        $perPage = $request->has('per_page') ? $request->per_page : 15;
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json($tickets);
    }

    /**
     * Store a newly created ticket.
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
            'email' => 'required|email|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'priority' => 'required|integer|exists:priorities,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:New Ticket,In Progress,On Hold,Closed,Resolved',
            'is_assign' => 'nullable|integer|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Create ticket
        $ticket = new Ticket();
        $ticket->ticket_id = time();
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->mobile_no = $request->mobile_no;
        $ticket->category_id = $request->category_id;
        $ticket->priority = $request->priority;
        $ticket->subject = $request->subject;
        $ticket->status = $request->status;
        $ticket->description = $request->description;
        $ticket->created_by = Auth::id();
        $ticket->workspace = getActiveWorkSpace();
        $ticket->is_assign = $request->is_assign;
        $ticket->type = $request->is_assign ? 'Assigned' : 'Unassigned';
        
        // Handle attachments if any
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = ('tickets/' . $ticket->ticket_id);
                $path = multipleFileUpload($file, 'attachments', $fileNameToStore, $dir);
                
                if ($path['flag'] == 1) {
                    $attachments[] = $path['url'];
                } else {
                    return response()->json(['error' => $path['msg']], 400);
                }
            }
        }
        
        $ticket->attachments = json_encode($attachments);
        $ticket->save();
        
        // Fire event
        event(new CreateTicket($ticket, $request));
        
        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket
        ], 201);
    }

    /**
     * Display the specified ticket.
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
        
        $ticket = Ticket::with('conversions')
            ->where('workspace', getActiveWorkSpace())
            ->find($id);
            
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        return response()->json($ticket);
    }

    /**
     * Update the specified ticket.
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
        
        $ticket = Ticket::where('workspace', getActiveWorkSpace())->find($id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'priority' => 'sometimes|required|integer|exists:priorities,id',
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|string|in:New Ticket,In Progress,On Hold,Closed,Resolved',
            'is_assign' => 'sometimes|nullable|integer|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Update ticket fields if provided
        if ($request->has('name')) {
            $ticket->name = $request->name;
        }
        
        if ($request->has('email')) {
            $ticket->email = $request->email;
        }
        
        if ($request->has('category_id')) {
            $ticket->category_id = $request->category_id;
        }
        
        if ($request->has('priority')) {
            $ticket->priority = $request->priority;
        }
        
        if ($request->has('subject')) {
            $ticket->subject = $request->subject;
        }
        
        if ($request->has('description')) {
            $ticket->description = $request->description;
        }
        
        if ($request->has('status')) {
            $ticket->status = $request->status;
            if ($request->status == 'Resolved') {
                $ticket->reslove_at = now();
            }
        }
        
        if ($request->has('is_assign')) {
            $ticket->is_assign = $request->is_assign;
            $ticket->type = $request->is_assign ? 'Assigned' : 'Unassigned';
        }
        
        $ticket->save();
        
        // Fire event
        event(new UpdateTicket($ticket, $request));
        
        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket
        ]);
    }

    /**
     * Remove the specified ticket.
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
        
        $ticket = Ticket::with('conversions')
            ->where('workspace', getActiveWorkSpace())
            ->find($id);
            
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Fire event before deletion
        event(new DestroyTicket($ticket));
        
        // Delete all conversions
        if ($ticket->conversions->isNotEmpty()) {
            foreach ($ticket->conversions as $conversion) {
                $conversion->delete();
            }
        }
        
        // Delete ticket attachments
        $ticketImageDirectory = ('uploads/tickets/' . $ticket->ticket_id);
        if (checkFile($ticketImageDirectory)) {
            \File::deleteDirectory($ticketImageDirectory);
        }
        
        $ticket->delete();
        
        return response()->json([
            'message' => 'Ticket deleted successfully'
        ]);
    }

    /**
     * Add a reply to a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket edit')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $ticket = Ticket::where('workspace', getActiveWorkSpace())->find($id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Create conversion (reply)
        $conversion = new Conversion();
        $conversion->ticket_id = $ticket->id;
        $conversion->description = $request->description;
        $conversion->sender = Auth::id();
        
        // Handle attachments if any
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = ('tickets/' . $ticket->ticket_id);
                $path = multipleFileUpload($file, 'attachments', $fileNameToStore, $dir);
                
                if ($path['flag'] == 1) {
                    $attachments[] = $path['url'];
                } else {
                    return response()->json(['error' => $path['msg']], 400);
                }
            }
        }
        
        $conversion->attachments = json_encode($attachments);
        $conversion->save();
        
        // Update ticket if status change is requested
        if ($request->has('status') && $ticket->status != $request->status) {
            $ticket->status = $request->status;
            if ($request->status == 'Resolved') {
                $ticket->reslove_at = now();
            }
            $ticket->save();
            
            // Fire status change event
            event(new UpdateTicketStatus($ticket, $request));
        }
        
        // Fire reply event
        event(new TicketReply($conversion, $ticket, $request));
        
        return response()->json([
            'message' => 'Reply sent successfully',
            'conversion' => $conversion
        ], 201);
    }

    /**
     * Update ticket status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket edit')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $ticket = Ticket::where('workspace', getActiveWorkSpace())->find($id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:New Ticket,In Progress,On Hold,Closed,Resolved',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $ticket->status = $request->status;
        if ($request->status == 'Resolved') {
            $ticket->reslove_at = now();
        }
        $ticket->save();
        
        // Fire status change event
        event(new UpdateTicketStatus($ticket, $request));
        
        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket
        ]);
    }

    /**
     * Assign ticket to an agent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignTicket(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket edit')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $ticket = Ticket::where('workspace', getActiveWorkSpace())->find($id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|integer|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $ticket->is_assign = $request->agent_id;
        $ticket->type = 'Assigned';
        $ticket->save();
        
        // Fire update event
        event(new UpdateTicket($ticket, $request));
        
        return response()->json([
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket
        ]);
    }

    /**
     * Create a public ticket (no authentication required).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createPublicTicket(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'workspace' => 'required|integer|exists:workspaces,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Get default priority and create ticket
        $priority = \Hubiko\Ticket\Entities\Priority::where('workspace', $request->workspace)
            ->where('name', 'Medium')
            ->first();
            
        if (!$priority) {
            $priority = \Hubiko\Ticket\Entities\Priority::where('workspace', $request->workspace)
                ->first();
        }
        
        // Create ticket
        $ticket = new Ticket();
        $ticket->ticket_id = time();
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->mobile_no = $request->mobile_no;
        $ticket->category_id = $request->category_id;
        $ticket->priority = $priority ? $priority->id : 1;
        $ticket->subject = $request->subject;
        $ticket->status = 'New Ticket';
        $ticket->description = $request->description;
        $ticket->created_by = 0; // System created
        $ticket->workspace = $request->workspace;
        $ticket->type = 'Unassigned';
        
        // Handle attachments if any
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = ('tickets/' . $ticket->ticket_id);
                $path = multipleFileUpload($file, 'attachments', $fileNameToStore, $dir);
                
                if ($path['flag'] == 1) {
                    $attachments[] = $path['url'];
                } else {
                    return response()->json(['error' => $path['msg']], 400);
                }
            }
        }
        
        $ticket->attachments = json_encode($attachments);
        $ticket->save();
        
        // Fire event
        event(new CreateTicket($ticket, $request));
        
        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket_id' => $ticket->ticket_id,
            'ticket' => $ticket
        ], 201);
    }

    /**
     * Get public ticket status.
     *
     * @param  string  $ticket_id
     * @return \Illuminate\Http\Response
     */
    public function getPublicTicketStatus($ticket_id)
    {
        $ticket = Ticket::where('ticket_id', $ticket_id)->first();
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        return response()->json([
            'ticket_id' => $ticket->ticket_id,
            'status' => $ticket->status,
            'last_updated' => $ticket->updated_at
        ]);
    }
} 