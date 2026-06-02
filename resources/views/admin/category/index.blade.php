@extends('admin.layout.app')
@section('title', 'Lakhtar news - Category List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Category list </h2>
                <a href="{{ route('admin.category.create') }}" class="btn">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>


            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td><img src="" alt=""></td>
                                <td>{{ $category->name }}</td>
                                <td> <span class="badge badge-{{ $category->status ? 'success' : 'danger' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span></td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="view.html" class="btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="form.html" class="btn-sm"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="btn-sm"><i class="fas fa-trash"></i></a>
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
