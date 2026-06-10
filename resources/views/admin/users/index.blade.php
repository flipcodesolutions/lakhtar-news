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

            <form method="GET" action="{{ route('admin.user.index') }}" class="list-filter-form">
                <div class="list-filter-grid">
                    <div class="list-filter-field list-filter-search">
                        <label for="user-search">Search</label>
                        <input type="text" id="user-search" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="Search by name, email, or mobile">
                    </div>
                    <div class="list-filter-field">
                        <label for="user-role">Role</label>
                        <select id="user-role" name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="reporter" {{ request('role') === 'reporter' ? 'selected' : '' }}>Reporter</option>
                            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="list-filter-field">
                        <label for="user-status">Status</label>
                        <select id="user-status" name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="list-filter-actions">
                        <button type="submit" class="btn">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="list-results-meta">
                Showing <strong>{{ $users->count() }}</strong> users
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
