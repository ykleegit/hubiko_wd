<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\CompanySecretary\Models\AmlScreening;
use Hubiko\CompanySecretary\Models\DirectorShareholder;
use Hubiko\CompanySecretary\Models\Company;

class AmlScreeningController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = AmlScreening::workspace()->with(['person', 'company']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified == 'yes');
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $screenings = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::aml-screenings.index', compact('screenings', 'companies'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $screening = AmlScreening::workspace()
            ->with(['person', 'company', 'screener', 'verifier'])
            ->findOrFail($id);

        return view('companysecretary::aml-screenings.show', compact('screening'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $screening = AmlScreening::workspace()->findOrFail($id);

        return view('companysecretary::aml-screenings.edit', compact('screening'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $screening = AmlScreening::workspace()->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,failed',
            'screening_source' => 'nullable|string|max:255',
            'result' => 'nullable|string',
            'risk_score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'follow_up_action' => 'nullable|string|max:255',
        ]);

        $updateData = $request->only([
            'status',
            'screening_source',
            'result',
            'risk_score',
            'notes',
            'follow_up_action',
        ]);

        // Set screened_at and screened_by when status changes to completed
        if ($request->status === 'completed' && $screening->status !== 'completed') {
            $updateData['screened_at'] = now();
            $updateData['screened_by'] = Auth::id();
        }

        $screening->update($updateData);

        return redirect()->route('companysecretary.aml-screenings.show', $screening->id)
            ->with('success', __('AML screening updated successfully.'));
    }

    public function verify($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $screening = AmlScreening::workspace()->findOrFail($id);

        if ($screening->status !== 'completed') {
            return redirect()->back()->with('error', __('Only completed screenings can be verified.'));
        }

        $screening->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->route('companysecretary.aml-screenings.show', $screening->id)
            ->with('success', __('AML screening verified successfully.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $screening = AmlScreening::workspace()->findOrFail($id);
        $screening->delete();

        return redirect()->route('companysecretary.aml-screenings.index')
            ->with('success', __('AML screening deleted successfully.'));
    }

    public function dashboard()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $stats = [
            'total_screenings' => AmlScreening::workspace()->count(),
            'pending_screenings' => AmlScreening::workspace()->pending()->count(),
            'completed_screenings' => AmlScreening::workspace()->completed()->count(),
            'high_risk_screenings' => AmlScreening::workspace()->where('risk_score', '>=', 80)->count(),
        ];

        $recentScreenings = AmlScreening::workspace()
            ->with(['person', 'company'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('companysecretary::aml-screenings.dashboard', compact('stats', 'recentScreenings'));
    }
}
