@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Alert List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Alert List</h2>
                <a href="{{ route('admin.alert.create') }}" class="btn">
                    <i class="fas fa-plus"></i> New Alert
                </a>
            </div>

            <form method="GET" action="{{ route('admin.alert.index') }}" class="list-filter-form">
                <div class="list-filter-grid">
                    <div class="list-filter-field list-filter-search">
                        <label for="alert-search">Search</label>
                        <input type="text" id="alert-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title or details">
                    </div>
                    <div class="list-filter-field">
                        <label for="alert-type">Type</label>
                        <select id="alert-type" name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="alert" {{ request('type') === 'alert' ? 'selected' : '' }}>Alert</option>
                            <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Info</option>
                        </select>
                    </div>
                    <div class="list-filter-field">
                        <label for="alert-status">Status</label>
                        <select id="alert-status" name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="list-filter-actions">
                        <button type="submit" class="btn">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.alert.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="list-results-meta">
                @if ($alerts->total() > 0)
                    Showing <strong>{{ $alerts->firstItem() }}</strong> - <strong>{{ $alerts->lastItem() }}</strong> of
                    <strong>{{ $alerts->total() }}</strong> alerts
                @else
                    Showing <strong>0</strong> alerts
                @endif
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Details</th>
                            <th>Type</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alerts as $alert)
                            <tr>
                                <td>{{ $alerts->firstItem() + $loop->index }}</td>
                                <td>{{ $alert->title ?? '-' }}</td>
                                <td>{{ $alert->details ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $alert->type === 'alert' ? 'danger' : 'success' }}">{{ ucfirst($alert->type) }}</span>
                                </td>
                                <td>{{ $alert->end_date ? \Illuminate\Support\Carbon::parse($alert->end_date)->format('Y-m-d') : '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $alert->status ? 'success' : 'danger' }}">{{ $alert->status ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.alert.edit', ['id' => $alert->id]) }}" class="btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.alert.destroy', ['id' => $alert->id]) }}" class="btn-sm delete-record" title="Delete" style="border-color: #ef4444; color: #ef4444;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($alerts->hasPages())
                <div class="list-pagination">
                    <div class="list-pagination-info">
                        Page <strong>{{ $alerts->currentPage() }}</strong> of <strong>{{ $alerts->lastPage() }}</strong>
                    </div>
                    {{ $alerts->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
