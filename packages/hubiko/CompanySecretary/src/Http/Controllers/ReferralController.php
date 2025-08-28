<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\CompanySecretary\Models\Referral;
use Hubiko\CompanySecretary\Models\Company;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = Referral::workspace()->with(['company']);

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by referral type
        if ($request->filled('referral_type')) {
            $query->where('referral_type', $request->referral_type);
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $referrals = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::referrals.index', compact('referrals', 'companies'));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::referrals.create', compact('companies'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'company_id' => 'required|exists:comp_sec_companies,id',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'referral_type' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $referral = Referral::create([
            'company_id' => $request->company_id,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'referral_type' => $request->referral_type,
            'referral_code' => Referral::generateReferralCode(),
            'status' => 'pending',
            'notes' => $request->notes,
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('companysecretary.referrals.show', $referral->id)
            ->with('success', __('Referral created successfully.'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $referral = Referral::workspace()
            ->with(['company', 'creator'])
            ->findOrFail($id);

        return view('companysecretary::referrals.show', compact('referral'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $referral = Referral::workspace()->findOrFail($id);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::referrals.edit', compact('referral', 'companies'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $referral = Referral::workspace()->findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:comp_sec_companies,id',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'referral_type' => 'required|string',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $referral->update($request->only([
            'company_id',
            'contact_name',
            'contact_email',
            'contact_phone',
            'referral_type',
            'status',
            'notes',
        ]));

        return redirect()->route('companysecretary.referrals.show', $referral->id)
            ->with('success', __('Referral updated successfully.'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $referral = Referral::workspace()->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,contacted,converted,declined',
        ]);

        $referral->update([
            'status' => $request->status,
            'last_invited_at' => $request->status === 'contacted' ? now() : $referral->last_invited_at,
        ]);

        return redirect()->route('companysecretary.referrals.show', $referral->id)
            ->with('success', __('Referral status updated successfully.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $referral = Referral::workspace()->findOrFail($id);
        $referral->delete();

        return redirect()->route('companysecretary.referrals.index')
            ->with('success', __('Referral deleted successfully.'));
    }
}
