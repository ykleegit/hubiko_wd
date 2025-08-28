<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hubiko\CompanySecretary\Models\Director;
use Hubiko\CompanySecretary\Models\CompanyBoard;
use Illuminate\Support\Facades\Auth;

class DirectorController extends Controller
{
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $directors = Director::whereHas('companyBoard', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->with('companyBoard')->paginate(10);
        
        return view('companysecretary::directors.index', compact('directors'));
    }

    public function create()
    {
        $workspaceId = getActiveWorkSpace();
        $companies = CompanyBoard::where('workspace_id', $workspaceId)->active()->get();
        return view('companysecretary::directors.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_board_id' => 'required|exists:company_boards,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'appointment_date' => 'required|date',
            'director_type' => 'required|in:executive,non_executive,independent'
        ]);

        Director::create([
            'company_board_id' => $request->company_board_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'nationality' => $request->nationality,
            'identification_number' => $request->identification_number,
            'identification_type' => $request->identification_type,
            'date_of_birth' => $request->date_of_birth,
            'appointment_date' => $request->appointment_date,
            'resignation_date' => $request->resignation_date,
            'director_type' => $request->director_type,
            'is_chairman' => $request->has('is_chairman'),
            'is_independent' => $request->has('is_independent'),
            'qualifications' => $request->qualifications ? explode(',', $request->qualifications) : [],
            'experience' => $request->experience,
            'other_directorships' => $request->other_directorships ? explode(',', $request->other_directorships) : [],
            'created_by' => Auth::id()
        ]);

        return redirect()->route('company-secretary.directors.index')->with('success', 'Director created successfully');
    }

    public function show(Director $director)
    {
        $director->load(['companyBoard', 'meetingAttendances.meeting']);
        return view('companysecretary::directors.show', compact('director'));
    }

    public function edit(Director $director)
    {
        $workspaceId = getActiveWorkSpace();
        $companies = CompanyBoard::where('workspace_id', $workspaceId)->active()->get();
        return view('companysecretary::directors.edit', compact('director', 'companies'));
    }

    public function update(Request $request, Director $director)
    {
        $request->validate([
            'company_board_id' => 'required|exists:company_boards,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'appointment_date' => 'required|date',
            'director_type' => 'required|in:executive,non_executive,independent'
        ]);

        $director->update([
            'company_board_id' => $request->company_board_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'nationality' => $request->nationality,
            'identification_number' => $request->identification_number,
            'identification_type' => $request->identification_type,
            'date_of_birth' => $request->date_of_birth,
            'appointment_date' => $request->appointment_date,
            'resignation_date' => $request->resignation_date,
            'director_type' => $request->director_type,
            'is_chairman' => $request->has('is_chairman'),
            'is_independent' => $request->has('is_independent'),
            'is_active' => $request->has('is_active'),
            'qualifications' => $request->qualifications ? explode(',', $request->qualifications) : [],
            'experience' => $request->experience,
            'other_directorships' => $request->other_directorships ? explode(',', $request->other_directorships) : []
        ]);

        return redirect()->route('company-secretary.directors.index')->with('success', 'Director updated successfully');
    }

    public function destroy(Director $director)
    {
        $director->delete();
        return redirect()->route('company-secretary.directors.index')->with('success', 'Director deleted successfully');
    }
}
