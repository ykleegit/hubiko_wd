<?php

namespace Hubiko\AIContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\AIContent\Entities\AIContent;
use Hubiko\AIContent\Entities\AITemplate;
use Hubiko\AIContent\Entities\AIUsage;
use Hubiko\AIContent\Services\AIService;

class AIContentController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->middleware('auth');
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of AI content
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('ai content manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspaceId = getActiveWorkSpace();
        
        $contents = AIContent::workspace($workspaceId)
            ->with(['template', 'creator'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->content_type, function($query, $type) {
                return $query->where('content_type', $type);
            })
            ->when($request->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('generated_content', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $templates = AITemplate::workspace($workspaceId)->active()->get();
        $contentTypes = ['article', 'blog_post', 'social_media', 'email', 'product_description', 'ad_copy'];

        return view('AIContent::content.index', compact('contents', 'templates', 'contentTypes'));
    }

    /**
     * Show the form for creating new AI content
     */
    public function create()
    {
        if (!Auth::user()->isAbleTo('ai content create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspaceId = getActiveWorkSpace();
        $templates = AITemplate::workspace($workspaceId)->active()->get();
        
        $contentTypes = [
            'article' => 'Article',
            'blog_post' => 'Blog Post',
            'social_media' => 'Social Media Post',
            'email' => 'Email Content',
            'product_description' => 'Product Description',
            'ad_copy' => 'Advertisement Copy'
        ];

        $tones = [
            'professional' => 'Professional',
            'casual' => 'Casual',
            'friendly' => 'Friendly',
            'formal' => 'Formal',
            'persuasive' => 'Persuasive',
            'informative' => 'Informative'
        ];

        $lengths = [
            'short' => 'Short (100-300 words)',
            'medium' => 'Medium (300-600 words)',
            'long' => 'Long (600+ words)'
        ];

        return view('AIContent::content.create', compact('templates', 'contentTypes', 'tones', 'lengths'));
    }

    /**
     * Store a newly created AI content
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('ai content create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|string',
            'prompt' => 'required|string',
            'template_id' => 'nullable|exists:ai_templates,id',
            'tone' => 'required|string',
            'length' => 'required|string',
            'keywords' => 'nullable|string',
            'language' => 'required|string|default:en',
            'provider' => 'nullable|string|in:openai,deepseek',
            'model' => 'nullable|string'
        ]);

        try {
            $workspaceId = getActiveWorkSpace();
            
            $provider = $request->provider ?? config('ai-content.default_provider');
            $aiService = new AIService($provider);
            $generationResult = $aiService->generateContent([
                'prompt' => $request->prompt,
                'content_type' => $request->content_type,
                'tone' => $request->tone,
                'length' => $request->length,
                'language' => $request->language,
                'keywords' => $request->keywords,
                'model' => $request->model
            ]);

            // Create AI content record
            $content = AIContent::create([
                'title' => $request->title,
                'content_type' => $request->content_type,
                'prompt' => $request->prompt,
                'generated_content' => $generationResult['content'],
                'template_id' => $request->template_id,
                'language' => $request->language,
                'tone' => $request->tone,
                'length' => $request->length,
                'keywords' => $request->keywords ? explode(',', $request->keywords) : null,
                'status' => 'draft',
                'ai_provider' => $provider,
                'ai_model' => $generationResult['model'],
                'tokens_used' => $generationResult['tokens_used'],
                'generation_time' => $generationResult['generation_time'],
                'quality_score' => $generationResult['quality_score'],
                'workspace_id' => $workspaceId,
                'created_by' => Auth::id()
            ]);

            // Log usage
            AIUsage::create([
                'content_id' => $content->id,
                'user_id' => Auth::id(),
                'action_type' => 'generate',
                'tokens_consumed' => $generationResult['tokens_used'],
                'cost' => $generationResult['cost'] ?? 0,
                'response_time' => $generationResult['generation_time'],
                'success' => true,
                'workspace_id' => $workspaceId
            ]);

            return redirect()->route('ai-content.show', $content)
                           ->with('success', __('AI content generated successfully!'));

        } catch (\Exception $e) {
            // Log failed usage
            AIUsage::create([
                'user_id' => Auth::id(),
                'action_type' => 'generate',
                'tokens_consumed' => 0,
                'cost' => 0,
                'response_time' => 0,
                'success' => false,
                'error_message' => $e->getMessage(),
                'workspace_id' => getActiveWorkSpace()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', __('Failed to generate content: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified AI content
     */
    public function show(AIContent $content)
    {
        if (!Auth::user()->isAbleTo('ai content view')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $content->load(['template', 'creator', 'usages', 'exports']);

        return view('AIContent::content.show', compact('content'));
    }

    /**
     * Show the form for editing AI content
     */
    public function edit(AIContent $content)
    {
        if (!Auth::user()->isAbleTo('ai content edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspaceId = getActiveWorkSpace();
        $templates = AITemplate::workspace($workspaceId)->active()->get();
        
        $contentTypes = [
            'article' => 'Article',
            'blog_post' => 'Blog Post',
            'social_media' => 'Social Media Post',
            'email' => 'Email Content',
            'product_description' => 'Product Description',
            'ad_copy' => 'Advertisement Copy'
        ];

        $tones = [
            'professional' => 'Professional',
            'casual' => 'Casual',
            'friendly' => 'Friendly',
            'formal' => 'Formal',
            'persuasive' => 'Persuasive',
            'informative' => 'Informative'
        ];

        return view('AIContent::content.edit', compact('content', 'templates', 'contentTypes', 'tones'));
    }

    /**
     * Update the specified AI content
     */
    public function update(Request $request, AIContent $content)
    {
        if (!Auth::user()->isAbleTo('ai content edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'generated_content' => 'required|string',
            'status' => 'required|in:draft,published,archived'
        ]);

        $content->update([
            'title' => $request->title,
            'generated_content' => $request->generated_content,
            'status' => $request->status
        ]);

        return redirect()->route('ai-content.show', $content)
                       ->with('success', __('Content updated successfully!'));
    }

    /**
     * Remove the specified AI content
     */
    public function destroy(AIContent $content)
    {
        if (!Auth::user()->isAbleTo('ai content delete')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $content->delete();

        return redirect()->route('ai-content.index')
                       ->with('success', __('Content deleted successfully!'));
    }

    /**
     * Regenerate content using AI
     */
    public function regenerate(Request $request, AIContent $content)
    {
        if (!Auth::user()->isAbleTo('ai content edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $generationResult = $this->aiService->generateContent([
                'prompt' => $content->prompt,
                'content_type' => $content->content_type,
                'tone' => $content->tone,
                'length' => $content->length,
                'keywords' => $content->keywords ?? [],
                'language' => $content->language
            ]);

            $content->update([
                'generated_content' => $generationResult['content'],
                'ai_model' => $generationResult['model'],
                'tokens_used' => $generationResult['tokens_used'],
                'generation_time' => $generationResult['generation_time'],
                'quality_score' => $generationResult['quality_score'] ?? null
            ]);

            // Log usage
            AIUsage::create([
                'content_id' => $content->id,
                'user_id' => Auth::id(),
                'action_type' => 'regenerate',
                'tokens_consumed' => $generationResult['tokens_used'],
                'cost' => $generationResult['cost'] ?? 0,
                'response_time' => $generationResult['generation_time'],
                'success' => true,
                'workspace_id' => getActiveWorkSpace()
            ]);

            return redirect()->back()->with('success', __('Content regenerated successfully!'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to regenerate content: ') . $e->getMessage());
        }
    }

    /**
     * Publish content
     */
    public function publish(AIContent $content)
    {
        if (!Auth::user()->isAbleTo('ai content edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $content->update(['status' => 'published']);

        return redirect()->back()->with('success', __('Content published successfully!'));
    }
}
