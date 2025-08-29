<?php

namespace Hubiko\AIContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\AIContent\Entities\AITemplate;

class AITemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of AI templates
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('ai template manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspaceId = getActiveWorkSpace();
        
        $templates = AITemplate::workspace($workspaceId)
            ->with('creator')
            ->when($request->category, function($query, $category) {
                return $query->where('category', $category);
            })
            ->when($request->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->paginate(15);

        $categories = AITemplate::workspace($workspaceId)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        return view('AIContent::templates.index', compact('templates', 'categories'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        if (!Auth::user()->isAbleTo('ai template create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categories = [
            'marketing' => 'Marketing',
            'content' => 'Content Writing',
            'social_media' => 'Social Media',
            'email' => 'Email Marketing',
            'seo' => 'SEO Content',
            'business' => 'Business Writing',
            'creative' => 'Creative Writing'
        ];

        $contentTypes = [
            'article' => 'Article',
            'blog_post' => 'Blog Post',
            'social_media' => 'Social Media Post',
            'email' => 'Email Content',
            'product_description' => 'Product Description',
            'ad_copy' => 'Advertisement Copy'
        ];

        return view('AIContent::templates.create', compact('categories', 'contentTypes'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('ai template create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'prompt_template' => 'required|string',
            'content_type' => 'required|string',
            'default_tone' => 'nullable|string',
            'default_length' => 'nullable|string',
            'variables' => 'nullable|array'
        ]);

        AITemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'prompt_template' => $request->prompt_template,
            'variables' => $request->variables ?? [],
            'content_type' => $request->content_type,
            'default_tone' => $request->default_tone,
            'default_length' => $request->default_length,
            'is_active' => true,
            'is_system' => false,
            'workspace_id' => getActiveWorkSpace(),
            'created_by' => Auth::id()
        ]);

        return redirect()->route('ai-templates.index')
                       ->with('success', __('Template created successfully!'));
    }

    /**
     * Display the specified template
     */
    public function show(AITemplate $template)
    {
        if (!Auth::user()->isAbleTo('ai template view')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $template->load(['creator', 'contents']);

        return view('AIContent::templates.show', compact('template'));
    }

    /**
     * Show the form for editing the template
     */
    public function edit(AITemplate $template)
    {
        if (!Auth::user()->isAbleTo('ai template edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->is_system) {
            return redirect()->back()->with('error', __('System templates cannot be edited.'));
        }

        $categories = [
            'marketing' => 'Marketing',
            'content' => 'Content Writing',
            'social_media' => 'Social Media',
            'email' => 'Email Marketing',
            'seo' => 'SEO Content',
            'business' => 'Business Writing',
            'creative' => 'Creative Writing'
        ];

        $contentTypes = [
            'article' => 'Article',
            'blog_post' => 'Blog Post',
            'social_media' => 'Social Media Post',
            'email' => 'Email Content',
            'product_description' => 'Product Description',
            'ad_copy' => 'Advertisement Copy'
        ];

        return view('AIContent::templates.edit', compact('template', 'categories', 'contentTypes'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, AITemplate $template)
    {
        if (!Auth::user()->isAbleTo('ai template edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->is_system) {
            return redirect()->back()->with('error', __('System templates cannot be edited.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'prompt_template' => 'required|string',
            'content_type' => 'required|string',
            'default_tone' => 'nullable|string',
            'default_length' => 'nullable|string',
            'variables' => 'nullable|array'
        ]);

        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'prompt_template' => $request->prompt_template,
            'variables' => $request->variables ?? [],
            'content_type' => $request->content_type,
            'default_tone' => $request->default_tone,
            'default_length' => $request->default_length
        ]);

        return redirect()->route('ai-templates.show', $template)
                       ->with('success', __('Template updated successfully!'));
    }

    /**
     * Remove the specified template
     */
    public function destroy(AITemplate $template)
    {
        if (!Auth::user()->isAbleTo('ai template delete')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->is_system) {
            return redirect()->back()->with('error', __('System templates cannot be deleted.'));
        }

        $template->delete();

        return redirect()->route('ai-templates.index')
                       ->with('success', __('Template deleted successfully!'));
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(AITemplate $template)
    {
        if (!Auth::user()->isAbleTo('ai template edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()->with('success', __("Template {$status} successfully!"));
    }
}
