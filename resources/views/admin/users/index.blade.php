@extends('admin.layout.app')
@section('title', 'Lakhtar news - User List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>User List</h2>
                <a href="{{ route('admin.user.create') }}" class="btn">
                    <i class="fas fa-plus"></i> New User
                </a>
            </div>


            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Language</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->name ?? '-' }}</td>
                                <td>{{ $user->mobile ?? '-' }}</td>
                                <td>{{ $user->email ?? '-' }}</td>
                                <td>{{ $user->language ?? '-' }}</td>
                                <td class="text-capitalize">{{ $user->role ?? '-' }}</td>
                                <td> <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.user.edit', $user->id) }}" class="btn-sm"><i class="fas fa-edit"></i></a>
                                    <a href="{{ route('admin.user.destroy', $user->id) }}" class="btn-sm delete-record">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
