<?php

namespace Hubiko\AIContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\AIContent\Entities\AIContent;
use Hubiko\AIContent\Entities\AITemplate;
use Hubiko\AIContent\Entities\AIUsage;

class AIDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display AI Content dashboard
     */
    public function index()
    {
        if (!Auth::user()->isAbleTo('ai content dashboard')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspaceId = getActiveWorkSpace();
        $userId = Auth::id();

        // Get dashboard statistics
        $stats = $this->getDashboardStats($workspaceId, $userId);
        
        // Get recent content
        $recentContent = AIContent::workspace($workspaceId)
            ->with(['template', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get usage analytics
        $usageAnalytics = $this->getUsageAnalytics($workspaceId, $userId);
        
        // Get popular templates
        $popularTemplates = AITemplate::workspace($workspaceId)
            ->withCount('contents')
            ->orderBy('contents_count', 'desc')
            ->limit(5)
            ->get();

        return view('AIContent::dashboard.index', compact(
            'stats',
            'recentContent',
            'usageAnalytics',
            'popularTemplates'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($workspaceId, $userId)
    {
        $totalContent = AIContent::workspace($workspaceId)->count();
        $publishedContent = AIContent::workspace($workspaceId)->published()->count();
        $draftContent = AIContent::workspace($workspaceId)->draft()->count();
        $totalTemplates = AITemplate::workspace($workspaceId)->count();
        
        $totalTokensUsed = AIUsage::workspace($workspaceId)
            ->successful()
            ->sum('tokens_consumed');
            
        $totalCost = AIUsage::workspace($workspaceId)
            ->successful()
            ->sum('cost');
            
        $contentThisMonth = AIContent::workspace($workspaceId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $avgGenerationTime = AIUsage::workspace($workspaceId)
            ->successful()
            ->avg('response_time') ?? 0;

        return [
            'total_content' => $totalContent,
            'published_content' => $publishedContent,
            'draft_content' => $draftContent,
            'total_templates' => $totalTemplates,
            'total_tokens_used' => $totalTokensUsed,
            'total_cost' => $totalCost,
            'content_this_month' => $contentThisMonth,
            'avg_generation_time' => round($avgGenerationTime, 2),
        ];
    }

    /**
     * Get usage analytics data
     */
    private function getUsageAnalytics($workspaceId, $userId)
    {
        // Daily usage for the last 30 days
        $dailyUsage = AIUsage::workspace($workspaceId)
            ->successful()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(tokens_consumed) as tokens')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Content type distribution
        $contentTypeDistribution = AIContent::workspace($workspaceId)
            ->selectRaw('content_type, COUNT(*) as count')
            ->groupBy('content_type')
            ->get();

        return [
            'daily_usage' => $dailyUsage,
            'content_type_distribution' => $contentTypeDistribution,
        ];
    }

    /**
     * Get usage chart data for AJAX requests
     */
    public function getUsageChartData(Request $request)
    {
        if (!Auth::user()->isAbleTo('ai content dashboard')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $workspaceId = getActiveWorkSpace();
        $days = $request->get('days', 30);

        $usage = AIUsage::workspace($workspaceId)
            ->successful()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(tokens_consumed) as tokens, SUM(cost) as cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $usage->pluck('date'),
            'usage_count' => $usage->pluck('count'),
            'tokens_used' => $usage->pluck('tokens'),
            'cost' => $usage->pluck('cost'),
        ]);
    }

    /**
     * Get content type distribution data
     */
    public function getContentTypeData(Request $request)
    {
        if (!Auth::user()->isAbleTo('ai content dashboard')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $workspaceId = getActiveWorkSpace();

        $distribution = AIContent::workspace($workspaceId)
            ->selectRaw('content_type, COUNT(*) as count')
            ->groupBy('content_type')
            ->get();

        return response()->json([
            'labels' => $distribution->pluck('content_type'),
            'data' => $distribution->pluck('count'),
        ]);
    }
}
