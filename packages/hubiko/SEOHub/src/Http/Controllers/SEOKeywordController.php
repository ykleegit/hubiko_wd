<?php

namespace Hubiko\SEOHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SEOKeywordController extends Controller
{
    public function index()
    {
        return view('seo.keywords.index');
    }

    public function create()
    {
        return view('seo.keywords.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('seo.keywords.index');
    }

    public function show($id)
    {
        return view('seo.keywords.show', compact('id'));
    }

    public function edit($id)
    {
        return view('seo.keywords.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('seo.keywords.index');
    }

    public function destroy($id)
    {
        return redirect()->route('seo.keywords.index');
    }

    public function bulkImport(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function checkRanking($id)
    {
        return response()->json(['success' => true]);
    }
}
