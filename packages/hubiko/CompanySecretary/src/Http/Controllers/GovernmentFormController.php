<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\CompanySecretary\Models\GovernmentForm;
use Hubiko\CompanySecretary\Models\Company;
use Hubiko\CompanySecretary\Models\Document;

class GovernmentFormController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = GovernmentForm::workspace()->with(['company']);

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by form type
        if ($request->filled('form_type')) {
            $query->byType($request->form_type);
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $forms = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::government-forms.index', compact('forms', 'companies'));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::government-forms.create', compact('companies'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'company_id' => 'required|exists:comp_sec_companies,id',
            'title' => 'required|string|max:255',
            'form_type' => 'required|string',
            'form_data' => 'nullable|array',
        ]);

        $form = GovernmentForm::create([
            'company_id' => $request->company_id,
            'title' => $request->title,
            'form_type' => $request->form_type,
            'form_data' => $request->form_data ?? [],
            'status' => 'draft',
            'reference_number' => GovernmentForm::generateReferenceNumber($request->form_type),
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('companysecretary.government-forms.show', $form->id)
            ->with('success', __('Government form created successfully.'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $form = GovernmentForm::workspace()
            ->with(['company', 'document', 'creator'])
            ->findOrFail($id);

        return view('companysecretary::government-forms.show', compact('form'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $form = GovernmentForm::workspace()->findOrFail($id);

        if (!$form->canBeEdited()) {
            return redirect()->back()->with('error', __('This form cannot be edited in its current status.'));
        }

        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::government-forms.edit', compact('form', 'companies'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $form = GovernmentForm::workspace()->findOrFail($id);

        if (!$form->canBeEdited()) {
            return redirect()->back()->with('error', __('This form cannot be edited in its current status.'));
        }

        $request->validate([
            'company_id' => 'required|exists:comp_sec_companies,id',
            'title' => 'required|string|max:255',
            'form_type' => 'required|string',
            'form_data' => 'nullable|array',
        ]);

        $form->update([
            'company_id' => $request->company_id,
            'title' => $request->title,
            'form_type' => $request->form_type,
            'form_data' => $request->form_data ?? [],
        ]);

        return redirect()->route('companysecretary.government-forms.show', $form->id)
            ->with('success', __('Government form updated successfully.'));
    }

    public function generate($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $form = GovernmentForm::workspace()->findOrFail($id);

        if ($form->status !== 'draft') {
            return redirect()->back()->with('error', __('Only draft forms can be generated.'));
        }

        // Here you would implement the actual form generation logic
        // For now, we'll just update the status
        $form->update(['status' => 'generated']);

        return redirect()->route('companysecretary.government-forms.show', $form->id)
            ->with('success', __('Government form generated successfully.'));
    }

    public function submit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $form = GovernmentForm::workspace()->findOrFail($id);

        if (!$form->canBeSubmitted()) {
            return redirect()->back()->with('error', __('This form cannot be submitted in its current status.'));
        }

        $form->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return redirect()->route('companysecretary.government-forms.show', $form->id)
            ->with('success', __('Government form submitted successfully.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $form = GovernmentForm::workspace()->findOrFail($id);
        $form->delete();

        return redirect()->route('companysecretary.government-forms.index')
            ->with('success', __('Government form deleted successfully.'));
    }
}
