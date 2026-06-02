@extends('admin.layout.app')
@section('title', 'Lakhtar news - Language List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Language List</h2>
                <a href="{{ route('admin.language.create') }}" class="btn">
                    <i class="fas fa-plus"></i> Add Language
                </a>
            </div>


            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($languages as $language)
                            <tr>
                                <td>{{ $language->id }}</td>
                                <td>{{ $language->name }}</td>
                                <td>{{ $language->code }}</td>
                                <td><span class="badge badge-{{ $language->status ? 'success' : 'danger' }}">{{ $language->status ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.language.edit', $language->id) }}" class="btn-sm"><i class="fas fa-edit"></i></a>
                                    <a href="{{ route('admin.language.destroy', $language->id) }}" class="btn-sm"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No data found</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
