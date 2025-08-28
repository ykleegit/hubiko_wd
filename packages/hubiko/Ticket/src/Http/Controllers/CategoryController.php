<?php

namespace Hubiko\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hubiko\Ticket\Entities\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->can('ticket category manage')) {
            $categories = Category::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $categoryTree = buildCategoryTree($categories);
            
            return view('ticket::categories.index', compact('categories', 'categoryTree'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->can('ticket category create')) {
            $categories = Category::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $categoryTree = buildCategoryTree($categories);
            
            return view('ticket::categories.create', compact('categoryTree'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('ticket category create')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
                'color' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $category = new Category();
            $category->name = $request->name;
            $category->color = $request->color;
            $category->parent = $request->parent ? $request->parent : 0;
            $category->created_by = creatorId();
            $category->workspace = getActiveWorkSpace();
            $category->save();

            return redirect()->route('ticket-category.index')->with('success', __('Category created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (Auth::user()->can('ticket category edit')) {
            $category = Category::find($id);
            
            if (!$category || $category->workspace != getActiveWorkSpace() || $category->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Category not found.'));
            }
            
            $categories = Category::where('workspace', getActiveWorkSpace())
                ->where('created_by', creatorId())
                ->get();
                
            $categoryTree = buildCategoryTree($categories);
            
            return view('ticket::categories.edit', compact('category', 'categoryTree'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::user()->can('ticket category edit')) {
            $category = Category::find($id);
            
            if (!$category || $category->workspace != getActiveWorkSpace() || $category->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Category not found.'));
            }
            
            $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
                'color' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $category->name = $request->name;
            $category->color = $request->color;
            $category->parent = $request->parent ? $request->parent : 0;
            $category->save();

            return redirect()->route('ticket-category.index')->with('success', __('Category updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::user()->can('ticket category delete')) {
            $category = Category::find($id);
            
            if (!$category || $category->workspace != getActiveWorkSpace() || $category->created_by != creatorId()) {
                return redirect()->back()->with('error', __('Category not found.'));
            }
            
            // Check if category has tickets
            if ($category->tickets()->count() > 0) {
                return redirect()->back()->with('error', __('Cannot delete category that has tickets.'));
            }
            
            // Check if category has subcategories
            if ($category->subCategories()->count() > 0) {
                return redirect()->back()->with('error', __('Cannot delete category that has subcategories.'));
            }
            
            $category->delete();

            return redirect()->route('ticket-category.index')->with('success', __('Category deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
} 