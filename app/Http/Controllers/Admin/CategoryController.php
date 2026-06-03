<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('id', 'desc')->get();
        return view('admin.category.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'nameInHindi' => 'required|string|max:255',
            'nameInGujarati' => 'required|string|max:255',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->nameInHindi = $request->nameInHindi;
        $category->nameInGujarati = $request->nameInGujarati;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('category'), $fileName);
            $category->image = 'category/' . $fileName;
        }
        $category->save();


        return redirect()->route('admin.category.index')->with('success', 'Category created successfully');
    }
    public function edit($id)
    {
        $category = Category::find($id);
        return view('admin.category.edit', compact('category'));
    }
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'nameInHindi' => 'required|string|max:255',
            'nameInGujarati' => 'required|string|max:255',
        ]);

        $id = $request->id;
        $category = Category::find($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->nameInHindi = $request->nameInHindi;
        $category->nameInGujarati = $request->nameInGujarati;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('category'), $fileName);
            $category->image = 'category/' . $fileName;
        }
        $category->save();


        return redirect()->route('admin.category.index')->with('success', 'Category created successfully');
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();
        return redirect()->route('admin.category.index')->with('success', 'Category deleted successfully');
    }
}
