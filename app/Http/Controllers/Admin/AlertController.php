<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\AppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AlertController extends Controller
{
    public function __construct(
        protected AppNotificationService $appNotification
    ) {}

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
            'status' => 'required|in:0,1',
            'end_date' => 'required|date',
        ]);

        $alert = new Alert();
        $alert->title = $request->title;
        $alert->details = $request->details;
        $alert->titleInHindi = $request->titleInHindi;
        $alert->titleInGujarati = $request->titleInGujarati;
        $alert->detailsInHindi = $request->detailsInHindi;
        $alert->detailsInGujarati = $request->detailsInGujarati;
        $alert->type = $request->type;
        $alert->status = $request->input('status') === '1';
        $alert->end_date = $request->end_date;
        $alert->save();

        $this->dispatchAlertNotification($alert);

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
            'status' => 'required|in:0,1',
            'end_date' => 'required|date',
        ]);

        $wasActive = (bool) $alert->status;

        $alert->title = $request->title;
        $alert->details = $request->details;
        $alert->titleInHindi = $request->titleInHindi;
        $alert->titleInGujarati = $request->titleInGujarati;
        $alert->detailsInHindi = $request->detailsInHindi;
        $alert->detailsInGujarati = $request->detailsInGujarati;
        $alert->type = $request->type;
        $alert->status = $request->input('status') === '1';
        $alert->end_date = $request->end_date;
        $alert->save();

        if ($alert->status && ! $wasActive) {
            $this->dispatchAlertNotification($alert);
        }

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

    protected function dispatchAlertNotification(Alert $alert): void
    {
        try {
            $this->appNotification->notifyNewAlert($alert);
        } catch (Throwable $e) {
            Log::error('Failed to dispatch alert notification', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
