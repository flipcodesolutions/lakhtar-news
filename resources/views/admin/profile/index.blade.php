@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - My Profile')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>My Profile</h2>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="profile-grid">
                    <div class="profile-card">
                        <div class="profile-avatar-wrap">
                            <img id="profileImagePreview" src="{{ $user?->profile_image ? asset($user->profile_image) : asset('images/logo.png') }}" alt="{{ $user?->name ?? 'User' }}" class="profile-avatar-preview">
                        </div>

                        <div class="profile-summary">
                            <h3>{{ $user?->name ?? 'User' }}</h3>
                            <p>{{ $user?->email ?? 'No email found' }}</p>
                        </div>

                        <div class="profile-badges">
                            <span class="badge profile-badge">{{ ucfirst($user?->role ?? 'user') }}</span>
                            <span class="badge {{ $user?->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $user?->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="profile_image">Profile Image</label>
                            <input type="file" id="profile_image" name="profile_image" class="form-control" accept=".jpeg,.jpg,.png,.gif,.webp">
                            @error('profile_image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="profile-form-card">
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user?->name) }}" placeholder="Enter full name">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-col">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" placeholder="Enter email address">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="mobile">Mobile</label>
                                    <input type="text" id="mobile" name="mobile" class="form-control" value="{{ old('mobile', $user?->mobile) }}" placeholder="Enter mobile number" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                                    @error('mobile')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-col">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <select id="language" name="language" class="form-control">
                                        <option value="eng" {{ old('language', $user?->language) === 'eng' ? 'selected' : '' }}>English</option>
                                        <option value="guj" {{ old('language', $user?->language) === 'guj' ? 'selected' : '' }}>Gujarati</option>
                                        <option value="hin" {{ old('language', $user?->language) === 'hin' ? 'selected' : '' }}>Hindi</option>
                                    </select>
                                    @error('language')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <input type="text" id="role" class="form-control" value="{{ ucfirst($user?->role ?? 'user') }}" readonly>
                                </div>
                            </div>

                            <div class="form-col">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <input type="text" id="status" class="form-control" value="{{ $user?->is_active ? 'Active' : 'Inactive' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="{{ route('admin.password.change') }}" class="btn btn-secondary">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
        }

        .profile-card,
        .profile-form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 24px;
        }

        .profile-card {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-self: start;
        }

        .profile-avatar-wrap {
            display: flex;
            justify-content: center;
        }

        .profile-avatar-preview {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #e2e8f0;
            background: #f8fafc;
        }

        .profile-summary {
            text-align: center;
        }

        .profile-summary h3 {
            margin: 0 0 8px;
            color: #1e293b;
        }

        .profile-summary p {
            margin: 0;
            color: #64748b;
            word-break: break-word;
        }

        .profile-badges {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .profile-badge {
            background: #e0f2fe;
            color: #0369a1;
        }

        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        document.getElementById('profile_image')?.addEventListener('change', function(event) {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            const preview = document.getElementById('profileImagePreview');
            if (!preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
        });
    </script>
@endsection
