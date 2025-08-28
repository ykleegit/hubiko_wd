<?php

namespace Hubiko\SEOHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Hubiko\SEOHub\Entities\SEOWebsite;
use Hubiko\SEOHub\Entities\SEOAudit;
use Hubiko\SEOHub\Entities\SEOKeyword;
use Hubiko\SEOHub\Entities\SEOIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SEODashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display SEO dashboard
     */
    public function index()
    {
        // Check permission
        if (!Auth::user()->isAbleTo('view seo dashboard')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspaceId = getActiveWorkSpace();
        $userId = Auth::id();

        // Get dashboard statistics
        $stats = $this->getDashboardStats($workspaceId, $userId);
        
        // Get recent audits
        $recentAudits = SEOAudit::forWorkspace($workspaceId)
            ->forUser($userId)
            ->with('website')
            ->completed()
            ->latest('completed_at')
            ->limit(5)
            ->get();

        // Get websites needing attention
        $websitesNeedingAudit = SEOWebsite::forWorkspace($workspaceId)
            ->forUser($userId)
            ->active()
            ->where(function($query) {
                $query->whereNull('last_audit_at')
                      ->orWhere('next_audit_at', '<=', now());
            })
            ->limit(5)
            ->get();

        // Get top issues
        $topIssues = SEOIssue::forWorkspace($workspaceId)
            ->forUser($userId)
            ->open()
            ->selectRaw('type, severity, COUNT(*) as count')
            ->groupBy('type', 'severity')
            ->orderByRaw("FIELD(severity, 'major', 'moderate', 'minor')")
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Get keyword performance
        $keywordPerformance = SEOKeyword::forWorkspace($workspaceId)
            ->forUser($userId)
            ->tracking()
            ->whereNotNull('current_position')
            ->orderBy('current_position')
            ->limit(10)
            ->get();

        return view('seohub::dashboard.index', compact(
            'stats',
            'recentAudits',
            'websitesNeedingAudit',
            'topIssues',
            'keywordPerformance'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($workspaceId, $userId)
    {
        $totalWebsites = SEOWebsite::forWorkspace($workspaceId)
            ->forUser($userId)
            ->count();

        $totalAudits = SEOAudit::forWorkspace($workspaceId)
            ->forUser($userId)
            ->completed()
            ->count();

        $totalKeywords = SEOKeyword::forWorkspace($workspaceId)
            ->forUser($userId)
            ->tracking()
            ->count();

        $openIssues = SEOIssue::forWorkspace($workspaceId)
            ->forUser($userId)
            ->open()
            ->count();

        $majorIssues = SEOIssue::forWorkspace($workspaceId)
            ->forUser($userId)
            ->open()
            ->bySeverity('major')
            ->count();

        $averageScore = SEOAudit::forWorkspace($workspaceId)
            ->forUser($userId)
            ->completed()
            ->avg('score') ?? 0;

        $auditsThisMonth = SEOAudit::forWorkspace($workspaceId)
            ->forUser($userId)
            ->completed()
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        $improvingKeywords = SEOKeyword::forWorkspace($workspaceId)
            ->forUser($userId)
            ->tracking()
            ->whereNotNull('current_position')
            ->whereNotNull('previous_position')
            ->whereRaw('current_position < previous_position')
            ->count();

        return [
            'total_websites' => $totalWebsites,
            'total_audits' => $totalAudits,
            'total_keywords' => $totalKeywords,
            'open_issues' => $openIssues,
            'major_issues' => $majorIssues,
            'average_score' => round($averageScore, 1),
            'audits_this_month' => $auditsThisMonth,
            'improving_keywords' => $improvingKeywords,
        ];
    }

    /**
     * Get audit performance chart data
     */
    public function getAuditChartData(Request $request)
    {
        if (!Auth::user()->isAbleTo('view seo dashboard')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $workspaceId = getActiveWorkSpace();
        $userId = Auth::id();
        $days = $request->get('days', 30);

        $audits = SEOAudit::forWorkspace($workspaceId)
            ->forUser($userId)
            ->completed()
            ->where('completed_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(completed_at) as date, AVG(score) as avg_score, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $audits->pluck('date'),
            'scores' => $audits->pluck('avg_score'),
            'counts' => $audits->pluck('count'),
        ]);
    }

    /**
     * Get issues distribution data
     */
    public function getIssuesDistribution(Request $request)
    {
        if (!Auth::user()->isAbleTo('view seo dashboard')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $workspaceId = getActiveWorkSpace();
        $userId = Auth::id();

        $issues = SEOIssue::forWorkspace($workspaceId)
            ->forUser($userId)
            ->open()
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->get();

        return response()->json([
            'labels' => $issues->pluck('severity'),
            'data' => $issues->pluck('count'),
        ]);
    }
}
