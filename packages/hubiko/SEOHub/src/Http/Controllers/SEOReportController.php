<?php

namespace Hubiko\SEOHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SEOReportController extends Controller
{
    public function index()
    {
        return view('seo.reports.index');
    }

    public function create()
    {
        return view('seo.reports.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('seo.reports.index');
    }

    public function show($id)
    {
        return view('seo.reports.show', compact('id'));
    }

    public function edit($id)
    {
        return view('seo.reports.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('seo.reports.index');
    }

    public function destroy($id)
    {
        return redirect()->route('seo.reports.index');
    }

    public function generate($id)
    {
        return response()->json(['success' => true]);
    }

    public function download($id)
    {
        return response()->download('path/to/report.pdf');
    }
}
