<?php

namespace Hubiko\SEOHub\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hubiko\SEOHub\Models\SEOWebsite;
use Illuminate\Support\Facades\Auth;

class SEOWebsiteController extends Controller
{
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $websites = SEOWebsite::where('workspace_id', $workspaceId)
            ->with(['audits', 'keywords', 'issues'])
            ->paginate(10);
            
        return view('seohub::websites.index', compact('websites'));
    }

    public function create()
    {
        return view('seohub::websites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'industry' => 'nullable|string',
            'crawl_frequency' => 'required|in:daily,weekly,monthly'
        ]);

        $domain = parse_url($request->url, PHP_URL_HOST);

        SEOWebsite::create([
            'name' => $request->name,
            'url' => $request->url,
            'domain' => $domain,
            'description' => $request->description,
            'industry' => $request->industry,
            'target_keywords' => $request->target_keywords ? explode(',', $request->target_keywords) : [],
            'competitors' => $request->competitors ? explode(',', $request->competitors) : [],
            'google_analytics_id' => $request->google_analytics_id,
            'google_search_console_id' => $request->google_search_console_id,
            'crawl_frequency' => $request->crawl_frequency,
            'workspace_id' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
            'settings' => $request->settings ?? []
        ]);

        return redirect()->route('seo.websites.index')->with('success', 'Website created successfully');
    }

    public function show(SEOWebsite $website)
    {
        $website->load(['audits', 'keywords', 'issues']);
        return view('seohub::websites.show', compact('website'));
    }

    public function edit(SEOWebsite $website)
    {
        return view('seohub::websites.edit', compact('website'));
    }

    public function update(Request $request, SEOWebsite $website)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'industry' => 'nullable|string',
            'crawl_frequency' => 'required|in:daily,weekly,monthly'
        ]);

        $domain = parse_url($request->url, PHP_URL_HOST);

        $website->update([
            'name' => $request->name,
            'url' => $request->url,
            'domain' => $domain,
            'description' => $request->description,
            'industry' => $request->industry,
            'target_keywords' => $request->target_keywords ? explode(',', $request->target_keywords) : [],
            'competitors' => $request->competitors ? explode(',', $request->competitors) : [],
            'google_analytics_id' => $request->google_analytics_id,
            'google_search_console_id' => $request->google_search_console_id,
            'crawl_frequency' => $request->crawl_frequency,
            'is_active' => $request->has('is_active'),
            'settings' => $request->settings ?? []
        ]);

        return redirect()->route('seo.websites.index')->with('success', 'Website updated successfully');
    }

    public function destroy(SEOWebsite $website)
    {
        $website->delete();
        return redirect()->route('seo.websites.index')->with('success', 'Website deleted successfully');
    }

    public function crawl(SEOWebsite $website)
    {
        // Update last crawled timestamp
        $website->update(['last_crawled_at' => now()]);
        
        return response()->json(['success' => true, 'message' => 'Crawl started']);
    }

    public function analytics(SEOWebsite $website)
    {
        $analytics = [
            'total_keywords' => $website->keywords()->count(),
            'ranking_keywords' => $website->keywords()->where('current_position', '<=', 100)->count(),
            'top_10_keywords' => $website->keywords()->where('current_position', '<=', 10)->count(),
            'total_issues' => $website->issues()->where('status', 'open')->count(),
            'critical_issues' => $website->issues()->where('severity', 'critical')->where('status', 'open')->count()
        ];
        
        return view('seohub::websites.analytics', compact('website', 'analytics'));
    }

    public function reports(SEOWebsite $website)
    {
        $reports = $website->audits()->latest()->take(10)->get();
        return view('seohub::websites.reports', compact('website', 'reports'));
    }

    public function settings(SEOWebsite $website)
    {
        return view('seohub::websites.settings', compact('website'));
    }
}
