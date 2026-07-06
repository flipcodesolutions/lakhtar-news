@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Category List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Category list </h2>
                <a href="{{ route('admin.category.create') }}" class="btn">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>

            <form method="GET" action="{{ route('admin.category.index') }}" class="list-filter-form">
                <div class="list-filter-grid">
                    <div class="list-filter-field list-filter-search">
                        <label for="category-search">Search</label>
                        <input type="text" id="category-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by category name">
                    </div>
                    <div class="list-filter-field">
                        <label for="category-status">Status</label>
                        <select id="category-status" name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="list-filter-actions">
                        <a href="{{ route('admin.category.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="list-results-meta">
                Showing <strong>{{ $categories->count() }}</strong> categories
            </div>


            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Name In Gujarati</th>
                            <th>Name In Hindi</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>
                                    @if ($category->image)
                                        <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->nameInGujarati }}</td>
                                <td>{{ $category->nameInHindi }}</td>
                                <td> <span class="badge badge-{{ $category->status ? 'success' : 'danger' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.category.edit', ['id' => $category->id]) }}" class="btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.category.destroy', ['id' => $category->id]) }}" class="btn-sm delete-record"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
