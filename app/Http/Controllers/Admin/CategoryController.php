<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'superadmin') {
            $categories = Category::with(['parent', 'subcategories'])
                ->whereNull('parent_id')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();
        } else {
            $categories = Category::where('business_id', $user->business_id)
                ->with(['parent', 'subcategories'])
                ->whereNull('parent_id')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get parent categories for dropdown
        if ($user->role === 'superadmin') {
            $parentCategories = Category::whereNull('parent_id')
                ->active()
                ->orderBy('name')
                ->get();
        } else {
            $parentCategories = Category::where('business_id', $user->business_id)
                ->whereNull('parent_id')
                ->active()
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $data = $validator->validated();
        $data['business_id'] = $user->role === 'superadmin' ? null : $user->business_id;
        $data['is_active'] = $request->has('is_active') ? true : false;
        $data['display_order'] = $data['display_order'] ?? 999;

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $category = Category::findOrFail($id);
        
        // Check permission
        if ($user->role !== 'superadmin' && $category->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access');
        }
        
        // Get parent categories for dropdown
        if ($user->role === 'superadmin') {
            $parentCategories = Category::whereNull('parent_id')
                ->where('id', '!=', $id)
                ->active()
                ->orderBy('name')
                ->get();
        } else {
            $parentCategories = Category::where('business_id', $user->business_id)
                ->whereNull('parent_id')
                ->where('id', '!=', $id)
                ->active()
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $user = Auth::user();
        
        // Check permission
        if ($user->role !== 'superadmin' && $category->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;
        $data['display_order'] = $data['display_order'] ?? $category->display_order;

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $user = Auth::user();
        
        // Check permission
        if ($user->role !== 'superadmin' && $category->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access');
        }
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with existing products');
        }
        
        // Check if category has subcategories
        if ($category->subcategories()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with subcategories');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
