<?php

namespace App\Http\Controllers;

use App\Models\Cms;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function show($slug)
    {
        $cms = Cms::where('slug', $slug)->first();
        if (!$cms) {
            return redirect()->route('admin.cms.show', ['slug' => $slug])->with('error', 'Page not found');
        }
        return view('admin.cms.show', compact('cms'));
    }

    public function update(Request $request, $slug)
    {
        try {
            $cms = Cms::where('slug', $slug)->first();
            $cms->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'detail' => $request->detail,
            ]);
            return redirect()->route('admin.cms.show', ['slug' => $slug])->with('success', 'CMS updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.cms.show', ['slug' => $slug])->with('error', 'Failed to update CMS');
        }
    }
}
