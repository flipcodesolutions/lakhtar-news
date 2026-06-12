<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakhtar news - Reset Password</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Admin Logo">
            </div>

            <h2 class="text-center mb-3">Reset Password</h2>
            <p class="text-center" style="color: #64748b; margin-bottom: 20px;">
                Set a new password for your admin account.
            </p>

            <form action="{{ route('admin.password.store') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $email) }}" placeholder="Enter your email">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                    @error('password_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-block">Reset Password</button>
                </div>

                <div class="form-group" style="text-align: center; margin-bottom: 0;">
                    <a href="{{ route('admin.login') }}" style="color: #b7131a; font-weight: 600; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
