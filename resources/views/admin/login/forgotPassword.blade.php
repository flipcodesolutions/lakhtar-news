<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakhtar news - Forgot Password</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Admin Logo">
            </div>

            <h2 class="text-center mb-3">Forgot Password</h2>
            <p class="text-center" style="color: #64748b; margin-bottom: 20px;">
                Enter your admin email and we will send you a password reset link.
            </p>

            <form action="{{ route('admin.password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter your email">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                @if (session('success'))
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="form-group">
                    <button type="submit" class="btn btn-block">Send Reset Link</button>
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
