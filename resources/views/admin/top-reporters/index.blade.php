@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Top Reporters')
@section('main')
    <div class="main-content-inner">
        <!-- Custom CSS for page premium styles -->
        <style>
            .top-reporter-container {
                display: flex;
                flex-direction: column;
                gap: 24px;
                margin-bottom: 30px;
            }

            /* Premium Top Reporter Card */
            .top-reporter-hero {
                background: linear-gradient(135deg, #b7131a 0%, #d82b32 50%, #e74c3c 100%);
                color: #ffffff;
                border-radius: 12px;
                padding: 30px;
                box-shadow: 0 10px 20px rgba(183, 19, 26, 0.25);
                position: relative;
                overflow: hidden;
                display: flex;
                align-items: center;
                gap: 30px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .top-reporter-hero::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -30%;
                width: 350px;
                height: 350px;
                background: rgba(255, 255, 255, 0.08);
                border-radius: 50%;
                pointer-events: none;
            }

            .top-reporter-hero:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 24px rgba(183, 19, 26, 0.3);
            }

            .hero-avatar-wrapper {
                position: relative;
                flex-shrink: 0;
            }

            .hero-avatar {
                width: 110px;
                height: 110px;
                border-radius: 50%;
                object-fit: cover;
                border: 4px solid rgba(255, 255, 255, 0.8);
                background-color: #ffffff;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            }

            .hero-avatar-placeholder {
                width: 110px;
                height: 110px;
                border-radius: 50%;
                border: 4px solid rgba(255, 255, 255, 0.8);
                background: rgba(255, 255, 255, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 40px;
                color: #ffffff;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .hero-badge {
                position: absolute;
                bottom: -5px;
                right: -5px;
                background-color: #ffd700;
                color: #333333;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                font-size: 14px;
                border: 2px solid #ffffff;
                animation: pulse-crown 2s infinite;
            }

            @keyframes pulse-crown {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.1);
                }

                100% {
                    transform: scale(1);
                }
            }

            .hero-details {
                flex-grow: 1;
            }

            .hero-label {
                text-transform: uppercase;
                letter-spacing: 2px;
                font-size: 11px;
                font-weight: 700;
                color: rgba(255, 255, 255, 0.9);
                margin-bottom: 6px;
                display: inline-block;
                background: rgba(255, 255, 255, 0.15);
                padding: 4px 10px;
                border-radius: 20px;
            }

            .hero-name {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 8px;
                line-height: 1.2;
            }

            .hero-meta-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 12px;
                margin-bottom: 15px;
            }

            .hero-meta-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                color: rgba(255, 255, 255, 0.9);
            }

            .hero-meta-item i {
                width: 16px;
                text-align: center;
                opacity: 0.8;
            }

            .hero-dates {
                font-size: 13px;
                background: rgba(0, 0, 0, 0.15);
                padding: 8px 12px;
                border-radius: 6px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .hero-actions {
                flex-shrink: 0;
            }

            .btn-hero-remove {
                background: #ffffff;
                color: #b7131a;
                border: none;
                border-radius: 6px;
                padding: 10px 18px;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }

            .btn-hero-remove:hover {
                background: #f8f9fa;
                color: #e74c3c;
                transform: translateY(-1px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            }

            /* Placeholder State */
            .top-reporter-placeholder {
                background: #ffffff;
                border: 2px dashed #ddd;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: #7f8c8d;
                box-shadow: var(--box-shadow);
            }

            .placeholder-icon {
                font-size: 45px;
                color: #bdc3c7;
                margin-bottom: 15px;
            }

            .placeholder-title {
                font-size: 18px;
                font-weight: 600;
                color: #34495e;
                margin-bottom: 6px;
            }

            .placeholder-subtitle {
                font-size: 14px;
                max-width: 400px;
                line-height: 1.4;
            }

            /* Custom Modal Styles */
            .custom-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1050;
                display: none;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .custom-modal.show {
                display: flex;
                opacity: 1;
            }

            .custom-modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
            }

            .custom-modal-dialog {
                position: relative;
                background: #ffffff;
                border-radius: 12px;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
                z-index: 1051;
                transform: scale(0.9);
                transition: transform 0.3s ease;
                overflow: hidden;
            }

            .custom-modal.show .custom-modal-dialog {
                transform: scale(1);
            }

            .custom-modal-header {
                background: #f8f9fa;
                padding: 16px 20px;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .custom-modal-header h4 {
                margin: 0;
                font-size: 18px;
                color: #2c3e50;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .custom-modal-header h4 i {
                color: #ffd700;
            }

            .close-modal {
                background: none;
                border: none;
                font-size: 20px;
                color: #95a5a6;
                cursor: pointer;
                transition: color 0.2s;
            }

            .close-modal:hover {
                color: #34495e;
            }

            .custom-modal-body {
                padding: 20px;
            }

            .custom-modal-footer {
                background: #f8f9fa;
                padding: 16px 20px;
                border-top: 1px solid #eee;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
            }

            /* Responsive tweaks */
            @media (max-width: 768px) {
                .top-reporter-hero {
                    flex-direction: column;
                    text-align: center;
                    align-items: center;
                    padding: 24px;
                }

                .hero-meta-grid {
                    grid-template-columns: 1fr;
                    text-align: left;
                    margin: 0 auto 15px;
                    max-width: 250px;
                }

                .hero-dates {
                    justify-content: center;
                }
            }
        </style>

        <div class="top-reporter-container">
            <!-- Top Hero Card showing the Active Top Reporter -->
            @if ($activeTopReporter && $activeTopReporter->user)
                <div class="top-reporter-hero">
                    <div class="hero-avatar-wrapper">
                        @if ($activeTopReporter->user->profile_image)
                            <img src="{{ asset($activeTopReporter->user->profile_image) }}" alt="{{ $activeTopReporter->user->name }}" class="hero-avatar">
                        @else
                            <div class="hero-avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="hero-badge">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                    <div class="hero-details">
                        <span class="hero-label">Active Top Reporter</span>
                        <h3 class="hero-name">{{ $activeTopReporter->user->name }}</h3>

                        <div class="hero-meta-grid">
                            <div class="hero-meta-item">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $activeTopReporter->user->email ?? '-' }}</span>
                            </div>
                            <div class="hero-meta-item">
                                <i class="fas fa-phone"></i>
                                <span>{{ $activeTopReporter->user->mobile ?? '-' }}</span>
                            </div>
                            <div class="hero-meta-item">
                                <i class="fas fa-file-alt"></i>
                                <span>{{ $activeTopReporter->user->news_count ?? $activeTopReporter->user->news()->count() }} Articles Published</span>
                            </div>
                        </div>

                        <div class="hero-dates">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Active Period: <strong>{{ \Carbon\Carbon::parse($activeTopReporter->start_date)->format('M d, Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($activeTopReporter->end_date)->format('M d, Y') }}</strong></span>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ route('admin.top-reporters.destroy', $activeTopReporter->id) }}" class="btn-hero-remove delete-record">
                            <i class="fas fa-user-minus"></i> Remove Top status
                        </a>
                    </div>
                </div>
            @else
                <div class="top-reporter-placeholder">
                    <div class="placeholder-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="placeholder-title">No Active Top Reporter Selected</h3>
                    <p class="placeholder-subtitle">Select one of the registered reporters below, click the <strong>Make Top Reporter</strong> button, and specify active dates to promote them.</p>
                </div>
            @endif

            <!-- Reporters Table Card -->
            <div class="content-card">
                <div class="view-header">
                    <h2>Reporter Directory</h2>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Articles Published</th>
                                <th>Top Reporter Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reporters as $reporter)
                                @php
                                    $isActive = $activeTopReporter && $activeTopReporter->user_id == $reporter->id;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($reporter->profile_image)
                                            <img src="{{ asset($reporter->profile_image) }}" alt="{{ $reporter->name }}" width="45" height="45" style="object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
                                        @else
                                            <div style="width: 45px; height: 45px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #475569; border: 2px solid #ddd;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color: #2c3e50;">{{ $reporter->name }}</strong>
                                    </td>
                                    <td>{{ $reporter->email ?? '-' }}</td>
                                    <td>{{ $reporter->mobile ?? '-' }}</td>
                                    <td>
                                        <span style="font-weight: 600; padding: 4px 8px; border-radius: 12px; background-color: #f1f5f9; color: #475569; font-size: 12px;">
                                            {{ $reporter->news_count ?? 0 }} articles
                                        </span>
                                    </td>
                                    <td>
                                        @if ($isActive)
                                            <span class="badge badge-success" style="background-color: #2ecc71; color: white; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 4px;">
                                                <i class="fas fa-crown" style="font-size: 10px;"></i> Active Top
                                            </span>
                                        @else
                                            <span class="badge badge-secondary" style="background-color: #95a5a6; color: white; padding: 4px 8px; border-radius: 4px;">
                                                Standard
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        @if ($isActive)
                                            <a href="{{ route('admin.top-reporters.destroy', $activeTopReporter->id) }}" class="btn-sm btn-danger delete-record" title="Remove Top Status">
                                                <i class="fas fa-user-minus"></i> Remove
                                            </a>
                                        @else
                                            <button class="btn-sm make-top-reporter-btn" style="border-color: #ffd700; color: #d4af37;" data-id="{{ $reporter->id }}" data-name="{{ $reporter->name }}" title="Make Top Reporter">
                                                <i class="fas fa-crown"></i> Make Top
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center" style="padding: 30px; color: #7f8c8d;">No reporters found in the directory.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Modal Popup for Promoting a Reporter -->
    <div id="promote-modal" class="custom-modal">
        <div class="custom-modal-backdrop"></div>
        <div class="custom-modal-dialog">
            <form action="{{ route('admin.top-reporters.store') }}" method="POST">
                @csrf
                <input type="hidden" id="promote-user-id" name="user_id">

                <div class="custom-modal-header">
                    <h4><i class="fas fa-crown"></i> Promote to Top Reporter</h4>
                    <button type="button" class="close-modal">&times;</button>
                </div>

                <div class="custom-modal-body">
                    <p style="margin-bottom: 20px; color: #64748b; font-size: 14px;">
                        Set the active period for the selected reporter. Promoting this reporter will automatically deactivate any other active Top Reporter.
                    </p>

                    <div class="form-group">
                        <label for="promote-user-name">Selected Reporter</label>
                        <input type="text" id="promote-user-name" class="form-control" readonly style="background-color: #f8f9fa;">
                    </div>

                    <div class="form-row" style="margin-top: 15px;">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="start-date">Start Date</label>
                                <input type="date" id="start-date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="end-date">End Date</label>
                                <input type="date" id="end-date" name="end_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+1 month')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary close-modal" style="padding: 8px 16px; font-size: 14px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px; border-color: #ffd700; color: #b7131a; background: #fff;">
                        Confirm Promotion
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal script logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('promote-modal');
            const openButtons = document.querySelectorAll('.make-top-reporter-btn');
            const closeElements = document.querySelectorAll('.close-modal, .custom-modal-backdrop');
            const promoteUserIdInput = document.getElementById('promote-user-id');
            const promoteUserNameInput = document.getElementById('promote-user-name');

            // Open Modal handler
            openButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');

                    promoteUserIdInput.value = userId;
                    promoteUserNameInput.value = userName;

                    // Show modal
                    modal.style.display = 'flex';
                    // Trigger reflow for transition
                    modal.offsetHeight;
                    modal.classList.add('show');
                });
            });

            // Close Modal handler
            closeElements.forEach(element => {
                element.addEventListener('click', function() {
                    modal.classList.remove('show');
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 300);
                });
            });
        });
    </script>
@endsection
