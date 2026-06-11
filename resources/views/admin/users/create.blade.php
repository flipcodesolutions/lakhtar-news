@extends('admin.layout.app')
@section('title', 'Lakhtar news - Add User')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>User Management</h2>
                <a href="{{ route('admin.user.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>


            <form action="{{ route('admin.user.store') }}" method="post">
                @csrf
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}" class="form-control" placeholder="Enter mobile" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" autocomplete="tel">
                            @error('mobile')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" class="form-control">
                                <option selected disabled>-- Select Role --</option>
                                <option @if (old('role') == 'admin') selected @endif value="admin">Admin</option>
                                {{-- <option value="editor">Editor</option> --}}
                                <option @if (old('role') == 'reporter') selected @endif value="reporter">Reporter</option>
                                <option @if (old('role') == 'user') selected @endif value="user">User</option>
                            </select>
                            @error('role')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language" class="form-control">
                                <option selected disabled>-- Select Language --</option>
                                <option @if (old('language') == 1) selected @endif value="eng">English</option>
                                <option @if (old('language') == 2) selected @endif value="guj">Gujarati</option>
                                <option @if (old('language') == 3) selected @endif value="hin">Hindi</option>
                            </select>
                            @error('language')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-row" id="password-fields" style="display:none;">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter the password" id="password" disabled>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm the password" id="password_confirmation" disabled>
                            @error('password_confirmation')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Submit</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    $(document).ready(function() {

        function togglePasswordFields() {
            const role = $('#role').val();
            const isAdmin = role === 'admin';

            if (isAdmin) {
                $('#password-fields').show();
                $('#password').prop('disabled', false).prop('required', true);
                $('#password_confirmation').prop('disabled', false).prop('required', true);
            } else {
                $('#password-fields').hide();
                $('#password').prop('disabled', true).prop('required', false).val('');
                $('#password_confirmation').prop('disabled', true).prop('required', false).val('');
                $('.js-error').remove();
            }
        }

        togglePasswordFields();
        $('#role').on('change', togglePasswordFields);

        $('form').on('submit', function(e) {

            let isValid = true;

            $('.js-error').remove();

            // Name
            let name = $('#name').val().trim();
            if (name === '') {
                $('#name').after('<span class="text-danger js-error">Name is required</span>');
                isValid = false;
            }

            // Email
            let email = $('#email').val().trim();
            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === '') {
                $('#email').after('<span class="text-danger js-error">Email is required</span>');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                $('#email').after('<span class="text-danger js-error">Enter a valid email address</span>');
                isValid = false;
            }

            // Mobile
            let mobile = $('#mobile').val().trim();

            if (mobile === '') {
                $('#mobile').after('<span class="text-danger js-error">Mobile number is required</span>');
                isValid = false;
            } else if (!/^[0-9]{10}$/.test(mobile)) {
                $('#mobile').after('<span class="text-danger js-error">Mobile number must be 10 digits</span>');
                isValid = false;
            }

            // Role
            let role = $('#role').val();
            if (!role) {
                $('#role').after('<span class="text-danger js-error">Please select a role</span>');
                isValid = false;
            }

            // Language
            let language = $('#language').val();
            if (!language) {
                $('#language').after('<span class="text-danger js-error">Please select a language</span>');
                isValid = false;
            }

            // Password
            const selectedRole = $('#role').val();
            const isAdmin = selectedRole === 'admin';
            if (isAdmin) {
                let password = $('#password').val();

                if (password === '') {
                    $('#password').after('<span class="text-danger js-error">Password is required</span>');
                    isValid = false;
                } else if (password.length < 6) {
                    $('#password').after('<span class="text-danger js-error">Password must be at least 6 characters</span>');
                    isValid = false;
                }

                let confirmPassword = $('#password_confirmation').val();

                if (confirmPassword === '') {
                    $('#password_confirmation').after('<span class="text-danger js-error">Confirm Password is required</span>');
                    isValid = false;
                } else if (password !== confirmPassword) {
                    $('#password_confirmation').after('<span class="text-danger js-error">Passwords do not match</span>');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }

        });

    });
</script>
