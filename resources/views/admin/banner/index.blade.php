@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Banner List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Banner List</h2>
                <a href="{{ route('admin.banner.create') }}" class="btn">
                    <i class="fas fa-plus"></i> New Banner
                </a>
            </div>

            <form method="GET" action="{{ route('admin.banner.index') }}" class="list-filter-form">
                <div class="list-filter-grid">
                    <div class="list-filter-field list-filter-search">
                        <label for="banner-search">Search</label>
                        <input type="text" id="banner-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title or link">
                    </div>
                    <div class="list-filter-field">
                        <label for="banner-status">Status</label>
                        <select id="banner-status" name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="list-filter-actions">
                        <a href="{{ route('admin.banner.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="list-results-meta">
                @if ($banners->total() > 0)
                    Showing <strong>{{ $banners->firstItem() }}</strong> - <strong>{{ $banners->lastItem() }}</strong> of
                    <strong>{{ $banners->total() }}</strong> banners
                @else
                    Showing <strong>0</strong> banners
                @endif
            </div>


            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($banners as $banner)
                            <tr>
                                <td>{{ $banners->firstItem() + $loop->index }}</td>
                                <td>
                                    @if ($banner->image)
                                        <img src="{{ asset($banner->image) }}" alt="{{ $banner->title }}" width="70" height="40" style="object-fit: cover; border-radius: 4px;">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $banner->title ?? '-' }}</td>
                                <td>
                                    @if ($banner->link)
                                        <a href="{{ $banner->link }}" target="_blank" rel="noopener">{{ $banner->link }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $banner->start_date ?? '-' }}</td>
                                <td>{{ $banner->end_date ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $banner->status ? 'success' : 'danger' }}">{{ $banner->status ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.banner.edit', ['id' => $banner->id]) }}" class="btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.banner.destroy', ['id' => $banner->id]) }}" class="btn-sm delete-record" title="Delete" style="border-color: #ef4444; color: #ef4444;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No data found</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            @if ($banners->hasPages())
                <div class="list-pagination">
                    <div class="list-pagination-info">
                        Page <strong>{{ $banners->currentPage() }}</strong> of <strong>{{ $banners->lastPage() }}</strong>
                    </div>
                    {{ $banners->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>


@endsection
