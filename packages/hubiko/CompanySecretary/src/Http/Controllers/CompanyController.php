<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\CompanySecretary\Models\Company;
use Hubiko\CompanySecretary\Models\DirectorShareholder;
use Hubiko\CompanySecretary\Models\Document;
use Hubiko\CompanySecretary\Models\Address;

class CompanyController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $companies = Company::workspace()
            ->with(['directors', 'shareholders', 'documents'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('companysecretary::companies.index', compact('companies'));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('companysecretary::companies.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'company_name_en' => 'required|string|max:255',
            'company_name_zh' => 'nullable|string|max:255',
            'business_registration_number' => 'nullable|string|max:255',
            'incorporation_number' => 'nullable|string|max:255',
            'incorporation_date' => 'nullable|date',
            'business_registration_expiry' => 'nullable|date',
            'company_type' => 'required|string',
            'business_nature' => 'nullable|string',
            'annual_return_date' => 'nullable|date',
            'total_shares' => 'nullable|integer|min:1',
            'business_address' => 'nullable|string',
            'registered_address' => 'nullable|string',
        ]);

        $company = Company::create([
            'company_name_en' => $request->company_name_en,
            'company_name_zh' => $request->company_name_zh,
            'business_registration_number' => $request->business_registration_number,
            'incorporation_number' => $request->incorporation_number,
            'incorporation_date' => $request->incorporation_date,
            'business_registration_expiry' => $request->business_registration_expiry,
            'company_type' => $request->company_type,
            'business_nature' => $request->business_nature,
            'annual_return_date' => $request->annual_return_date,
            'total_shares' => $request->total_shares,
            'business_address' => $request->business_address,
            'registered_address' => $request->registered_address,
            'status' => 'active',
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('companysecretary.companies.index')
            ->with('success', __('Company created successfully.'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $company = Company::workspace()
            ->with([
                'directorsAndShareholders',
                'documents',
                'addresses',
                'amlScreenings',
                'governmentForms',
                'referrals'
            ])
            ->findOrFail($id);

        $stats = [
            'directors_count' => $company->directors()->count(),
            'shareholders_count' => $company->shareholders()->count(),
            'documents_count' => $company->documents()->count(),
            'pending_aml' => $company->amlScreenings()->pending()->count(),
            'pending_forms' => $company->governmentForms()->byStatus('draft')->count(),
        ];

        return view('companysecretary::companies.show', compact('company', 'stats'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $company = Company::workspace()->findOrFail($id);

        return view('companysecretary::companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $company = Company::workspace()->findOrFail($id);

        $request->validate([
            'company_name_en' => 'required|string|max:255',
            'company_name_zh' => 'nullable|string|max:255',
            'business_registration_number' => 'nullable|string|max:255',
            'incorporation_number' => 'nullable|string|max:255',
            'incorporation_date' => 'nullable|date',
            'business_registration_expiry' => 'nullable|date',
            'company_type' => 'required|string',
            'business_nature' => 'nullable|string',
            'annual_return_date' => 'nullable|date',
            'total_shares' => 'nullable|integer|min:1',
            'business_address' => 'nullable|string',
            'registered_address' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $company->update($request->only([
            'company_name_en',
            'company_name_zh',
            'business_registration_number',
            'incorporation_number',
            'incorporation_date',
            'business_registration_expiry',
            'company_type',
            'business_nature',
            'annual_return_date',
            'total_shares',
            'business_address',
            'registered_address',
            'status',
        ]));

        return redirect()->route('companysecretary.companies.show', $company->id)
            ->with('success', __('Company updated successfully.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $company = Company::workspace()->findOrFail($id);
        $company->delete();

        return redirect()->route('companysecretary.companies.index')
            ->with('success', __('Company deleted successfully.'));
    }

    public function dashboard()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $stats = [
            'total_companies' => Company::workspace()->count(),
            'active_companies' => Company::workspace()->where('status', 'active')->count(),
            'expiring_br' => Company::workspace()->get()->filter(function ($company) {
                return $company->isBrExpiringSoon();
            })->count(),
            'upcoming_returns' => Company::workspace()->get()->filter(function ($company) {
                return $company->isAnnualReturnComing();
            })->count(),
        ];

        $recentCompanies = Company::workspace()
            ->with(['directors', 'shareholders'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('companysecretary::dashboard', compact('stats', 'recentCompanies'));
    }
}
