<?php

namespace Hubiko\SEOHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SEOIssueController extends Controller
{
    public function index()
    {
        return view('seo.issues.index');
    }

    public function show($id)
    {
        return view('seo.issues.show', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('seo.issues.index');
    }

    public function markAsFixed($id)
    {
        return response()->json(['success' => true]);
    }

    public function markAsIgnored($id)
    {
        return response()->json(['success' => true]);
    }

    public function bulkAction(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
