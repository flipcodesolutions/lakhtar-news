<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakhtar news - Login</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Admin Logo">
            </div>
            <h2 class="text-center mb-3">Admin Login</h2>
            <form action="{{ route('admin.login.post') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter your email">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="margin-top: -4px; text-align: right;">
                    <a href="{{ route('admin.password.request') }}" style="color: #b7131a; font-weight: 600; text-decoration: none;">
                        Forgot Password?
                    </a>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block">Login</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <script>
        window.flashMessages = {
            success: @json(session('success')),
            error: @json(session('error')),
        };
    </script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
