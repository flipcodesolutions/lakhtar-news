@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Edit User')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>User Management</h2>
                <a href="{{ route('admin.user.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>


            <form action="{{ route('admin.user.update', $user->id) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $user->id }}">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="{{ $user->name }}" class="form-control" placeholder="Enter name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ $user->email }}" class="form-control" placeholder="Enter email">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" id="mobile" name="mobile" value="{{ $user->mobile }}" class="form-control" placeholder="Enter mobile" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" autocomplete="tel">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" class="form-control">
                                <option disabled {{ old('role', $user->role) ? '' : 'selected' }}>-- Select Role --</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="reporter" {{ old('role', $user->role) == 'reporter' ? 'selected' : '' }}>Reporter</option>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
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
                                <option @if (old('language') == 'eng' || $user->language == 'eng') selected @endif value="eng">English</option>
                                <option @if (old('language') == 'guj' || $user->language == 'guj') selected @endif value="guj">Gujarati</option>
                                <option @if (old('language') == 'hin' || $user->language == 'hin') selected @endif value="hin">Hindi</option>
                            </select>
                            @error('language')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-row" id="password-fields" style="display: none;">
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

<script>
    $(document).ready(function() {
        function togglePasswordFields() {
            const role = $('#role').val();
            const isAdmin = role === 'admin';

            if (isAdmin) {
                $('#password-fields').show();
                $('#password').prop('disabled', false);
                $('#password_confirmation').prop('disabled', false);
            } else {
                $('#password-fields').hide();
                $('#password').prop('disabled', true).val('');
                $('#password_confirmation').prop('disabled', true).val('');
            }
        }

        togglePasswordFields();
        $('#role').on('change', togglePasswordFields);
    });
</script>
