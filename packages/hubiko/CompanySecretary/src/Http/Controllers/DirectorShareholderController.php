<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\CompanySecretary\Models\DirectorShareholder;
use Hubiko\CompanySecretary\Models\Company;
use Hubiko\CompanySecretary\Models\AmlScreening;

class DirectorShareholderController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = DirectorShareholder::workspace()->with(['company']);

        // Filter by type
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'director':
                    $query->directors();
                    break;
                case 'shareholder':
                    $query->shareholders();
                    break;
                case 'both':
                    $query->where('type', 'both');
                    break;
            }
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $directorsAndShareholders = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::directors-shareholders.index', compact('directorsAndShareholders', 'companies'));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::directors-shareholders.create', compact('companies'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'company_id' => 'required|exists:comp_sec_companies,id',
            'type' => 'required|in:director,shareholder,both',
            'name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'appointment_date' => 'nullable|date',
            'shares' => 'nullable|integer|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'position' => 'nullable|string|max:255',
        ]);

        $directorShareholder = DirectorShareholder::create([
            'company_id' => $request->company_id,
            'type' => $request->type,
            'name' => $request->name,
            'id_number' => $request->id_number,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'email' => $request->email,
            'phone' => $request->phone,
            'appointment_date' => $request->appointment_date,
            'shares' => $request->shares,
            'percentage' => $request->percentage,
            'position' => $request->position,
            'status' => 'active',
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('companysecretary.directors-shareholders.index')
            ->with('success', __('Director/Shareholder created successfully.'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $directorShareholder = DirectorShareholder::workspace()
            ->with(['company', 'documents', 'amlScreening'])
            ->findOrFail($id);

        return view('companysecretary::directors-shareholders.show', compact('directorShareholder'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $directorShareholder = DirectorShareholder::workspace()->findOrFail($id);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::directors-shareholders.edit', compact('directorShareholder', 'companies'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $directorShareholder = DirectorShareholder::workspace()->findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:comp_sec_companies,id',
            'type' => 'required|in:director,shareholder,both',
            'name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'appointment_date' => 'nullable|date',
            'resignation_date' => 'nullable|date',
            'shares' => 'nullable|integer|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'position' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $directorShareholder->update($request->only([
            'company_id',
            'type',
            'name',
            'id_number',
            'nationality',
            'address',
            'email',
            'phone',
            'appointment_date',
            'resignation_date',
            'shares',
            'percentage',
            'position',
            'status',
        ]));

        return redirect()->route('companysecretary.directors-shareholders.show', $directorShareholder->id)
            ->with('success', __('Director/Shareholder updated successfully.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $directorShareholder = DirectorShareholder::workspace()->findOrFail($id);
        $directorShareholder->delete();

        return redirect()->route('companysecretary.directors-shareholders.index')
            ->with('success', __('Director/Shareholder deleted successfully.'));
    }

    public function initiateAmlScreening($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $directorShareholder = DirectorShareholder::workspace()->findOrFail($id);

        // Check if AML screening already exists
        if ($directorShareholder->amlScreening) {
            return redirect()->back()->with('error', __('AML screening already exists for this person.'));
        }

        $amlScreening = AmlScreening::create([
            'person_id' => $directorShareholder->id,
            'company_id' => $directorShareholder->company_id,
            'status' => 'pending',
            'reference_number' => AmlScreening::generateReferenceNumber(),
            'screening_source' => 'manual',
            'auto_manual_flag' => 'manual',
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('companysecretary.aml-screenings.show', $amlScreening->id)
            ->with('success', __('AML screening initiated successfully.'));
    }
}
