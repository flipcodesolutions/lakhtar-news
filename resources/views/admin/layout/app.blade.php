<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('images/logo.png') }}" style="width: 100px" alt="Lakhtar news Logo">
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.user.index') }}" class="{{ request()->routeIs('admin.user.index') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.category.index') }}" class="{{ request()->routeIs('admin.category.index') ? 'active' : '' }}">
                            <i class="fas fa-bookmark"></i> Category
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.index') ? 'active' : '' }}">
                            <i class="fas fa-newspaper"></i> News
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reporter-news.index') }}" class="{{ request()->routeIs('admin.reporter-news.index') ? 'active' : '' }}">
                            <i class="fas fa-user"></i> Reporter News
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.banner.index') }}" class="{{ request()->routeIs('admin.banner.index') ? 'active' : '' }}">
                            <i class="fas fa-image"></i> Banner
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.alert.index') }}" class="{{ request()->routeIs('admin.alert.index') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i> Alerts
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.index') ? 'active' : '' }}">
                            <i class="fas fa-images"></i> Media
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.top-reporters.index') }}" class="{{ request()->routeIs('admin.top-reporters.index') ? 'active' : '' }}">
                            <i class="fas fa-crown"></i> Top Reporters
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-left">
                    <button class="sidebar-toggle">
                        <div class="toggle-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </button>
                    <h1 class="page-title">@yield('title')</h1>
                </div>
                <div class="top-bar-right">
                    {{-- <div class="dropdown">
                        <div class="notifications dropdown-toggle">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </div>
                        <div class="dropdown-menu notification-dropdown">
                            <div class="dropdown-header">
                                <h6>Notifications</h6>
                                <a href="#" class="dropdown-link">Mark all as read</a>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <div class="notification-item">
                                    <div class="notification-icon bg-primary">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p class="notification-text">New contact message received</p>
                                        <p class="notification-time">2 minutes ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item">
                                <div class="notification-item">
                                    <div class="notification-icon bg-success">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p class="notification-text">New user registered</p>
                                        <p class="notification-time">1 hour ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item">
                                <div class="notification-item">
                                    <div class="notification-icon bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p class="notification-text">System alert: Low disk space</p>
                                        <p class="notification-time">5 hours ago</p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-footer">
                                <a href="#" class="dropdown-link">View all notifications</a>
                            </div>
                        </div>
                    </div> --}}

                    <div class="dropdown">
                        <div class="user-info dropdown-toggle">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name">{{ Auth::user()?->name ?? 'User' }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu user-dropdown">
                            <a href="{{ route('admin.profile') }}" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="{{ route('admin.password.change') }}" class="dropdown-item">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @yield('main')

            <!-- End of main-content-inner -->
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            $('.delete-record').on('click', function(e) {

                e.preventDefault();

                let url = $(this).attr('href');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this user!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {

                    if (result.isConfirmed) {
                        window.location.href = url;
                    }

                });

            });

        });
    </script>
</body>

</html>
