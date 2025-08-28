<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hubiko\CompanySecretary\Models\BoardMeeting;
use Hubiko\CompanySecretary\Models\CompanyBoard;
use Hubiko\CompanySecretary\Models\Director;
use Illuminate\Support\Facades\Auth;

class BoardMeetingController extends Controller
{
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $meetings = BoardMeeting::whereHas('companyBoard', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->with(['companyBoard', 'chairman', 'secretary'])->paginate(10);
        
        return view('companysecretary::meetings.index', compact('meetings'));
    }

    public function create()
    {
        $workspaceId = getActiveWorkSpace();
        $companies = CompanyBoard::where('workspace_id', $workspaceId)->active()->get();
        $directors = Director::whereHas('companyBoard', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->active()->get();
        
        return view('companysecretary::meetings.create', compact('companies', 'directors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_board_id' => 'required|exists:company_boards,id',
            'title' => 'required|string|max:255',
            'meeting_type' => 'required|in:regular,special,annual,extraordinary',
            'meeting_date' => 'required|date',
            'meeting_time' => 'required|date_format:H:i',
            'quorum_required' => 'required|integer|min:1'
        ]);

        $meetingDateTime = $request->meeting_date . ' ' . $request->meeting_time;

        BoardMeeting::create([
            'company_board_id' => $request->company_board_id,
            'title' => $request->title,
            'meeting_type' => $request->meeting_type,
            'meeting_date' => $request->meeting_date,
            'meeting_time' => $meetingDateTime,
            'location' => $request->location,
            'virtual_meeting_link' => $request->virtual_meeting_link,
            'agenda' => $request->agenda ? explode("\n", $request->agenda) : [],
            'quorum_required' => $request->quorum_required,
            'chairman_id' => $request->chairman_id,
            'secretary_id' => $request->secretary_id,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('company-secretary.meetings.index')->with('success', 'Meeting created successfully');
    }

    public function show(BoardMeeting $meeting)
    {
        $meeting->load(['companyBoard', 'chairman', 'secretary', 'attendances.director', 'resolutions']);
        return view('companysecretary::meetings.show', compact('meeting'));
    }

    public function edit(BoardMeeting $meeting)
    {
        $workspaceId = getActiveWorkSpace();
        $companies = CompanyBoard::where('workspace_id', $workspaceId)->active()->get();
        $directors = Director::whereHas('companyBoard', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->active()->get();
        
        return view('companysecretary::meetings.edit', compact('meeting', 'companies', 'directors'));
    }

    public function update(Request $request, BoardMeeting $meeting)
    {
        $request->validate([
            'company_board_id' => 'required|exists:company_boards,id',
            'title' => 'required|string|max:255',
            'meeting_type' => 'required|in:regular,special,annual,extraordinary',
            'meeting_date' => 'required|date',
            'meeting_time' => 'required|date_format:H:i',
            'quorum_required' => 'required|integer|min:1'
        ]);

        $meetingDateTime = $request->meeting_date . ' ' . $request->meeting_time;

        $meeting->update([
            'company_board_id' => $request->company_board_id,
            'title' => $request->title,
            'meeting_type' => $request->meeting_type,
            'meeting_date' => $request->meeting_date,
            'meeting_time' => $meetingDateTime,
            'location' => $request->location,
            'virtual_meeting_link' => $request->virtual_meeting_link,
            'agenda' => $request->agenda ? explode("\n", $request->agenda) : [],
            'minutes' => $request->minutes,
            'status' => $request->status ?? 'scheduled',
            'quorum_required' => $request->quorum_required,
            'quorum_present' => $request->quorum_present ?? 0,
            'chairman_id' => $request->chairman_id,
            'secretary_id' => $request->secretary_id
        ]);

        return redirect()->route('company-secretary.meetings.index')->with('success', 'Meeting updated successfully');
    }

    public function destroy(BoardMeeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('company-secretary.meetings.index')->with('success', 'Meeting deleted successfully');
    }
}
