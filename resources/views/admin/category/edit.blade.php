@extends('admin.layout.app')
@section('title', 'Lakhtar news - Edit Category')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Edit Category</h2>
                <a href="{{ route('admin.category.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>


            <form id="adminForm">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" class="form-control" placeholder="Enter name">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" class="form-control" placeholder="Enter email">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" class="form-control">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" class="form-control" rows="4" placeholder="Enter user bio"></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Submit</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
@endsection
