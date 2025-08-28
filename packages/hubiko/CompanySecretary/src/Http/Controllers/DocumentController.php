<?php

namespace Hubiko\CompanySecretary\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Hubiko\CompanySecretary\Models\Document;
use Hubiko\CompanySecretary\Models\Company;
use Hubiko\CompanySecretary\Models\DirectorShareholder;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = Document::workspace()->with(['company', 'person']);

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            if ($request->verified == 'yes') {
                $query->verified();
            } else {
                $query->unverified();
            }
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = Company::workspace()->orderBy('company_name_en')->get();

        return view('companysecretary::documents.index', compact('documents', 'companies'));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $companies = Company::workspace()->orderBy('company_name_en')->get();
        $persons = DirectorShareholder::workspace()->orderBy('name')->get();

        return view('companysecretary::documents.create', compact('companies', 'persons'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'file' => 'required|file|max:10240', // 10MB max
            'company_id' => 'nullable|exists:comp_sec_companies,id',
            'person_id' => 'nullable|exists:comp_sec_directors_shareholders,id',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('companysecretary/documents', $fileName, 'public');

        $document = Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_extension' => $file->getClientOriginalExtension(),
            'company_id' => $request->company_id,
            'person_id' => $request->person_id,
            'is_verified' => false,
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('companysecretary.documents.show', $document->id)
            ->with('success', __('Document uploaded successfully.'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $document = Document::workspace()
            ->with(['company', 'person', 'verifier', 'creator'])
            ->findOrFail($id);

        return view('companysecretary::documents.show', compact('document'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $document = Document::workspace()->findOrFail($id);
        $companies = Company::workspace()->orderBy('company_name_en')->get();
        $persons = DirectorShareholder::workspace()->orderBy('name')->get();

        return view('companysecretary::documents.edit', compact('document', 'companies', 'persons'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $document = Document::workspace()->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'file' => 'nullable|file|max:10240', // 10MB max
            'company_id' => 'nullable|exists:comp_sec_companies,id',
            'person_id' => 'nullable|exists:comp_sec_directors_shareholders,id',
        ]);

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'company_id' => $request->company_id,
            'person_id' => $request->person_id,
        ];

        // Handle file upload if new file provided
        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('companysecretary/documents', $fileName, 'public');

            $updateData['file_path'] = $filePath;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
            $updateData['file_extension'] = $file->getClientOriginalExtension();
            $updateData['is_verified'] = false; // Reset verification if file changed
        }

        $document->update($updateData);

        return redirect()->route('companysecretary.documents.show', $document->id)
            ->with('success', __('Document updated successfully.'));
    }

    public function verify($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $document = Document::workspace()->findOrFail($id);

        $document->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->route('companysecretary.documents.show', $document->id)
            ->with('success', __('Document verified successfully.'));
    }

    public function download($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $document = Document::workspace()->findOrFail($id);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('companysecretary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $document = Document::workspace()->findOrFail($id);

        // Delete file from storage
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('companysecretary.documents.index')
            ->with('success', __('Document deleted successfully.'));
    }
}
