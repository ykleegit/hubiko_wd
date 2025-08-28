<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hubiko\CompanySecretary\Models\CompanyBoard;
use Illuminate\Support\Facades\Auth;

class CompanyBoardController extends Controller
{
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $companies = CompanyBoard::where('workspace_id', $workspaceId)
            ->with(['directors', 'meetings', 'resolutions', 'filings'])
            ->paginate(10);
            
        return view('companysecretary::companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companysecretary::companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_registration_number' => 'required|string|unique:company_boards,company_registration_number',
            'incorporation_date' => 'required|date',
            'registered_address' => 'required|string',
            'company_type' => 'required|in:private,public,limited,partnership,sole_proprietorship',
            'financial_year_end' => 'required|date'
        ]);

        CompanyBoard::create([
            'name' => $request->name,
            'description' => $request->description,
            'company_registration_number' => $request->company_registration_number,
            'incorporation_date' => $request->incorporation_date,
            'registered_address' => $request->registered_address,
            'business_address' => $request->business_address,
            'company_type' => $request->company_type,
            'share_capital' => $request->share_capital ?? 0,
            'authorized_shares' => $request->authorized_shares ?? 0,
            'issued_shares' => $request->issued_shares ?? 0,
            'par_value' => $request->par_value ?? 0,
            'financial_year_end' => $request->financial_year_end,
            'workspace_id' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
            'settings' => $request->settings ?? []
        ]);

        return redirect()->route('company-secretary.companies.index')->with('success', 'Company created successfully');
    }

    public function show(CompanyBoard $company)
    {
        $company->load(['directors', 'meetings', 'resolutions', 'filings']);
        return view('companysecretary::companies.show', compact('company'));
    }

    public function edit(CompanyBoard $company)
    {
        return view('companysecretary::companies.edit', compact('company'));
    }

    public function update(Request $request, CompanyBoard $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_registration_number' => 'required|string|unique:company_boards,company_registration_number,' . $company->id,
            'incorporation_date' => 'required|date',
            'registered_address' => 'required|string',
            'company_type' => 'required|in:private,public,limited,partnership,sole_proprietorship',
            'financial_year_end' => 'required|date'
        ]);

        $company->update([
            'name' => $request->name,
            'description' => $request->description,
            'company_registration_number' => $request->company_registration_number,
            'incorporation_date' => $request->incorporation_date,
            'registered_address' => $request->registered_address,
            'business_address' => $request->business_address,
            'company_type' => $request->company_type,
            'share_capital' => $request->share_capital ?? 0,
            'authorized_shares' => $request->authorized_shares ?? 0,
            'issued_shares' => $request->issued_shares ?? 0,
            'par_value' => $request->par_value ?? 0,
            'financial_year_end' => $request->financial_year_end,
            'is_active' => $request->has('is_active'),
            'settings' => $request->settings ?? []
        ]);

        return redirect()->route('company-secretary.companies.index')->with('success', 'Company updated successfully');
    }

    public function destroy(CompanyBoard $company)
    {
        $company->delete();
        return redirect()->route('company-secretary.companies.index')->with('success', 'Company deleted successfully');
    }

    public function dashboard(CompanyBoard $company)
    {
        $stats = [
            'total_directors' => $company->directors()->count(),
            'active_directors' => $company->directors()->active()->count(),
            'upcoming_meetings' => $company->meetings()->where('meeting_date', '>', now())->count(),
            'pending_filings' => $company->filings()->where('status', 'pending')->count(),
            'overdue_filings' => $company->filings()->overdue()->count(),
            'recent_resolutions' => $company->resolutions()->latest()->take(5)->get()
        ];

        return view('companysecretary::companies.dashboard', compact('company', 'stats'));
    }
}
