<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::orderBy('id', 'desc')->get();
        return view('admin.languages.index', compact('languages'));
    }
    public function create()
    {
        return view('admin.languages.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255'
        ]);
        Language::create([
            'name' => $request->name,
            'code' => $request->code
        ]);
        return redirect()->route('admin.language.index')->with('success', 'Language created successfully');
    }
}
