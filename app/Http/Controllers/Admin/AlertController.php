<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = Alert::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('details', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->input('status');
                if ($status === 'active') {
                    $query->where('status', true);
                } elseif ($status === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.alerts.index', compact('alerts'));
    }

    public function create()
    {
        return view('admin.alerts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'required|string|max:255',
            'titleInHindi' => 'nullable|string|max:255',
            'titleInGujarati' => 'nullable|string|max:255',
            'detailsInHindi' => 'nullable|string|max:255',
            'detailsInGujarati' => 'nullable|string|max:255',
            'type' => 'required|in:alert,info',
            'status' => 'required|boolean',
            'end_date' => 'nullable|date',
        ]);

        $alert = new Alert();
        $alert->title = $request->title;
        $alert->details = $request->details;
        $alert->titleInHindi = $request->titleInHindi;
        $alert->titleInGujarati = $request->titleInGujarati;
        $alert->detailsInHindi = $request->detailsInHindi;
        $alert->detailsInGujarati = $request->detailsInGujarati;
        $alert->type = $request->type;
        $alert->status = $request->boolean('status');
        $alert->end_date = $request->end_date;
        $alert->save();

        return redirect()->route('admin.alert.index')->with('success', 'Alert created successfully.');
    }

    public function edit($id)
    {
        $alert = Alert::find($id);

        if (! $alert) {
            return redirect()->route('admin.alert.index')->with('error', 'Alert not found.');
        }

        return view('admin.alerts.edit', compact('alert'));
    }

    public function update(Request $request, $id)
    {
        $alert = Alert::find($id);

        if (! $alert) {
            return redirect()->route('admin.alert.index')->with('error', 'Alert not found.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'required|string|max:255',
            'titleInHindi' => 'nullable|string|max:255',
            'titleInGujarati' => 'nullable|string|max:255',
            'detailsInHindi' => 'nullable|string|max:255',
            'detailsInGujarati' => 'nullable|string|max:255',
            'type' => 'required|in:alert,info',
            'status' => 'required|boolean',
            'end_date' => 'nullable|date',
        ]);

        $alert->title = $request->title;
        $alert->details = $request->details;
        $alert->titleInHindi = $request->titleInHindi;
        $alert->titleInGujarati = $request->titleInGujarati;
        $alert->detailsInHindi = $request->detailsInHindi;
        $alert->detailsInGujarati = $request->detailsInGujarati;
        $alert->type = $request->type;
        $alert->status = $request->boolean('status');
        $alert->end_date = $request->end_date;
        $alert->save();

        return redirect()->route('admin.alert.index')->with('success', 'Alert updated successfully.');
    }

    public function destroy($id)
    {
        $alert = Alert::find($id);

        if (! $alert) {
            return redirect()->route('admin.alert.index')->with('error', 'Alert not found.');
        }

        $alert->delete();

        return redirect()->route('admin.alert.index')->with('success', 'Alert deleted successfully.');
    }
}
