@extends('admin.layout.app')
@section('title', 'Lakhtar news - Category List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Category List</h2>
                <a href="{{ route('admin.category.create') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Add Category
                </a>
            </div>


            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>Admin</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <a href="view.html" class="btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="form.html" class="btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td>jane@example.com</td>
                            <td>Editor</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <a href="view.html" class="btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="form.html" class="btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Robert Johnson</td>
                            <td>robert@example.com</td>
                            <td>User</td>
                            <td><span class="badge badge-danger">Inactive</span></td>
                            <td>
                                <a href="view.html" class="btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="form.html" class="btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Emily Wilson</td>
                            <td>emily@example.com</td>
                            <td>User</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <a href="view.html" class="btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="form.html" class="btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Michael Brown</td>
                            <td>michael@example.com</td>
                            <td>Editor</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td>
                                <a href="view.html" class="btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="form.html" class="btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
