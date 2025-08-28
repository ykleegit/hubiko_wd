<?php

namespace Modules\WhatStore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WhatStore\Entities\Template;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $templates = Template::forWorkspace()
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->category, function($query, $category) {
                return $query->where('category', $category);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('whatstore::templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('whatstore::templates.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'category' => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'body_text' => 'required|string',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'buttons' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $components = $this->buildComponents($request);

        Template::create([
            'name' => $request->name,
            'language' => $request->language,
            'category' => $request->category,
            'components' => json_encode($components),
            'header_text' => $request->header_text,
            'header_format' => $request->header_format,
            'body_text' => $request->body_text,
            'footer_text' => $request->footer_text,
            'buttons' => $request->buttons,
        ]);

        return redirect()->route('whatstore.templates.index')
            ->with('success', __('Template created successfully.'));
    }

    /**
     * Display the specified template
     */
    public function show(Template $template)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Template not found.'));
        }

        return view('whatstore::templates.show', compact('template'));
    }

    /**
     * Show the form for editing the template
     */
    public function edit(Template $template)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Template not found.'));
        }

        return view('whatstore::templates.edit', compact('template'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, Template $template)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Template not found.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'category' => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'body_text' => 'required|string',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'buttons' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $components = $this->buildComponents($request);

        $template->update([
            'name' => $request->name,
            'language' => $request->language,
            'category' => $request->category,
            'components' => json_encode($components),
            'header_text' => $request->header_text,
            'header_format' => $request->header_format,
            'body_text' => $request->body_text,
            'footer_text' => $request->footer_text,
            'buttons' => $request->buttons,
        ]);

        return redirect()->route('whatstore.templates.index')
            ->with('success', __('Template updated successfully.'));
    }

    /**
     * Remove the specified template
     */
    public function destroy(Template $template)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Template not found.'));
        }

        if ($template->isReferenced()) {
            return redirect()->back()->with('error', __('Cannot delete template that is being used by campaigns.'));
        }

        $template->delete();

        return redirect()->route('whatstore.templates.index')
            ->with('success', __('Template deleted successfully.'));
    }

    /**
     * Duplicate a template
     */
    public function duplicate(Template $template)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Template not found.'));
        }

        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->status = 'PENDING';
        $newTemplate->template_id = null;
        $newTemplate->save();

        return redirect()->route('whatstore.templates.edit', $newTemplate)
            ->with('success', __('Template duplicated successfully.'));
    }

    /**
     * Submit template for approval
     */
    public function submitForApproval(Template $template)
    {
        if (!auth()->user()->isAbleTo('whatstore manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($template->workspace != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Template not found.'));
        }

        $template->update(['status' => 'PENDING']);

        return redirect()->back()
            ->with('success', __('Template submitted for approval.'));
    }

    /**
     * Build WhatsApp components array from request
     */
    private function buildComponents(Request $request): array
    {
        $components = [];

        // Header component
        if ($request->header_text || $request->header_format) {
            $headerComponent = [
                'type' => 'HEADER',
                'format' => $request->header_format ?? 'TEXT',
            ];

            if ($request->header_format === 'TEXT') {
                $headerComponent['text'] = $request->header_text;
            }

            $components[] = $headerComponent;
        }

        // Body component (required)
        $components[] = [
            'type' => 'BODY',
            'text' => $request->body_text,
        ];

        // Footer component
        if ($request->footer_text) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $request->footer_text,
            ];
        }

        // Buttons component
        if ($request->buttons && count($request->buttons) > 0) {
            $buttonsComponent = [
                'type' => 'BUTTONS',
                'buttons' => [],
            ];

            foreach ($request->buttons as $button) {
                if (!empty($button['text'])) {
                    $buttonsComponent['buttons'][] = [
                        'type' => $button['type'] ?? 'QUICK_REPLY',
                        'text' => $button['text'],
                        'url' => $button['url'] ?? null,
                        'phone_number' => $button['phone_number'] ?? null,
                    ];
                }
            }

            if (!empty($buttonsComponent['buttons'])) {
                $components[] = $buttonsComponent;
            }
        }

        return $components;
    }
}
