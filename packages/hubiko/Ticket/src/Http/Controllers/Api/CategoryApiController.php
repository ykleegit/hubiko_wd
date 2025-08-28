<?php

namespace Hubiko\Ticket\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hubiko\Ticket\Entities\Category;

class CategoryApiController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket manage')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $query = Category::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId());
        
        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Get paginated results or all results
        if ($request->has('per_page')) {
            $categories = $query->paginate($request->per_page);
        } else {
            $categories = $query->get();
        }
        
        return response()->json($categories);
    }

    /**
     * Store a newly created category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket create')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Create category
        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->created_by = creatorId();
        $category->workspace = getActiveWorkSpace();
        $category->save();
        
        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket show')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $category = Category::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        
        return response()->json($category);
    }

    /**
     * Update the specified category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket edit')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $category = Category::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Update category
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();
        
        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->isAbleTo('ticket delete')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        
        $category = Category::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->find($id);
            
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        
        // Check if category is being used in tickets
        $ticketsCount = \Hubiko\Ticket\Entities\Ticket::where('category_id', $id)
            ->where('workspace', getActiveWorkSpace())
            ->count();
            
        if ($ticketsCount > 0) {
            return response()->json([
                'error' => 'Category cannot be deleted because it is being used in tickets'
            ], 422);
        }
        
        $category->delete();
        
        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
} 