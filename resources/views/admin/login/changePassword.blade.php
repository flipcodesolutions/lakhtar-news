@extends('admin.layout.app')
@section('title', 'Lakhtar news - Change Password')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Change Password</h2>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.password.update') }}" method="POST" class="form-container">
                @csrf
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-field">
                                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" autocomplete="current-password">
                                <button type="button" class="password-toggle" data-target="current_password" aria-label="Toggle current password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <div class="password-field">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="password" aria-label="Toggle new password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <div class="password-field">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm new password" autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Toggle confirm password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
        }

        .password-toggle:focus {
            outline: none;
        }
    </style>

    <script>
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;

                const nextType = input.type === 'password' ? 'text' : 'password';
                input.type = nextType;

                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
            });
        });
    </script>
@endsection
