<?php

namespace Hubiko\SEOHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SEOAuditController extends Controller
{
    public function index()
    {
        return view('seo.audits.index');
    }

    public function show($id)
    {
        return view('seo.audits.show', compact('id'));
    }

    public function destroy($id)
    {
        return redirect()->route('seo.audits.index');
    }

    public function refresh($id)
    {
        return response()->json(['success' => true]);
    }

    public function export($id)
    {
        return response()->download('path/to/audit-export.pdf');
    }
}
