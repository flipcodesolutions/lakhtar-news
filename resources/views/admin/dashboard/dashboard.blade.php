@extends('admin.layout.app')
@section('title', 'Lakhtar news - Dashboard')
@section('main')
    @php
        $approvalRate = $dashboardStats['totalNews'] > 0 ? round(($dashboardStats['approvedNews'] / $dashboardStats['totalNews']) * 100) : 0;

        $avgViewsPerStory = $dashboardStats['totalNews'] > 0 ? round($dashboardStats['totalViews'] / $dashboardStats['totalNews']) : 0;
    @endphp

    <div class="main-content-inner dashboard-page">
        <div class="content-card dashboard-hero">
            <div>
                <span class="dashboard-eyebrow">Newsroom overview</span>
                <h2>Welcome back, {{ Auth::user()?->name ?? 'Admin' }}</h2>
                <p>
                    Track publishing activity, review reporter submissions, and monitor the stories that are
                    performing best across Lakhtar News.
                </p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="{{ route('admin.news.create') }}" class="btn">
                    <i class="fas fa-plus"></i> Publish News
                </a>
                <a href="{{ route('admin.reporter-news.index') }}" class="btn">
                    <i class="fas fa-user-edit"></i> Review Reporter Queue
                </a>
            </div>
        </div>

        <div class="stats-cards dashboard-stats">
            <a class="stat-card dashboard-stat-card" href="{{ route('admin.news.index') }}">
                <div class="stat-card-icon bg-primary">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-number">{{ $dashboardStats['totalNews'] }}</div>
                    <div class="stat-card-title">Total News</div>
                </div>
            </a>

            <a class="stat-card dashboard-stat-card" href="{{ route('admin.reporter-news.index') }}">
                <div class="stat-card-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-number">{{ $dashboardStats['pendingNews'] }}</div>
                    <div class="stat-card-title">Pending Review</div>
                </div>
            </a>

            <div class="stat-card dashboard-stat-card">
                <div class="stat-card-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-number">{{ $dashboardStats['approvedNews'] }}</div>
                    <div class="stat-card-title">Approved Stories</div>
                </div>
            </div>

            <div class="stat-card dashboard-stat-card">
                <div class="stat-card-icon bg-info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-info">
                    <div class="stat-card-number">{{ $dashboardStats['totalUsers'] }}</div>
                    <div class="stat-card-title">Team Members</div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="content-card">
                <div class="dashboard-section-heading">
                    <div>
                        <span class="dashboard-section-label">Editorial pulse</span>
                        <h3>Publishing workflow</h3>
                    </div>
                    <span class="dashboard-chip">{{ $approvalRate }}% approval rate</span>
                </div>

                <div class="dashboard-kpi-grid">
                    <div class="dashboard-kpi-card">
                        <span>Total Views</span>
                        <strong>{{ number_format($dashboardStats['totalViews']) }}</strong>
                        <small>Audience reach across all news posts</small>
                    </div>
                    <div class="dashboard-kpi-card">
                        <span>Avg. Views / Story</span>
                        <strong>{{ number_format($avgViewsPerStory) }}</strong>
                        <small>Average engagement on published content</small>
                    </div>
                    <div class="dashboard-kpi-card">
                        <span>Featured Stories</span>
                        <strong>{{ $dashboardStats['featuredNews'] }}</strong>
                        <small>Highlighted stories on the platform</small>
                    </div>
                    <div class="dashboard-kpi-card">
                        <span>Scheduled Stories</span>
                        <strong>{{ $dashboardStats['scheduledNews'] }}</strong>
                        <small>Stories queued for future publishing</small>
                    </div>
                </div>

                <div class="dashboard-status-list">
                    <div class="dashboard-status-item">
                        <div>
                            <strong>Pending reporter submissions</strong>
                            <p>Stories that still need editorial review.</p>
                        </div>
                        <span class="badge badge-warning">{{ $dashboardStats['pendingNews'] }}</span>
                    </div>
                    <div class="dashboard-status-item">
                        <div>
                            <strong>Approved and ready live</strong>
                            <p>Content already cleared by the admin desk.</p>
                        </div>
                        <span class="badge badge-success">{{ $dashboardStats['approvedNews'] }}</span>
                    </div>
                    <div class="dashboard-status-item">
                        <div>
                            <strong>Rejected submissions</strong>
                            <p>Items that need correction or replacement.</p>
                        </div>
                        <span class="badge badge-danger">{{ $dashboardStats['rejectedNews'] }}</span>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="dashboard-section-heading">
                    <div>
                        <span class="dashboard-section-label">Management</span>
                        <h3>Quick actions</h3>
                    </div>
                </div>

                <div class="dashboard-actions-grid">
                    <a href="{{ route('admin.news.create') }}" class="dashboard-action-card">
                        <i class="fas fa-edit"></i>
                        <div>
                            <strong>Create a story</strong>
                            <p>Open the editor and publish breaking or scheduled content.</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.news.index') }}" class="dashboard-action-card">
                        <i class="fas fa-folder-open"></i>
                        <div>
                            <strong>Manage newsroom</strong>
                            <p>Update stories, media, featured posts, and publish dates.</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.category.index') }}" class="dashboard-action-card">
                        <i class="fas fa-bookmark"></i>
                        <div>
                            <strong>Organize categories</strong>
                            <p>Keep the news sections clean and easy to browse.</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.user.index') }}" class="dashboard-action-card">
                        <i class="fas fa-user-cog"></i>
                        <div>
                            <strong>Manage users</strong>
                            <p>Track active team members and control access.</p>
                        </div>
                    </a>
                </div>

                <div class="dashboard-team-summary">
                    <div class="dashboard-summary-row">
                        <span>Active team members</span>
                        <strong>{{ $dashboardStats['activeUsers'] }} / {{ $dashboardStats['totalUsers'] }}</strong>
                    </div>
                    <div class="dashboard-summary-row">
                        <span>Active categories</span>
                        <strong>{{ $dashboardStats['categories'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid dashboard-grid-bottom">
            <div class="content-card">
                <div class="dashboard-section-heading">
                    <div>
                        <span class="dashboard-section-label">Latest updates</span>
                        <h3>Recent news activity</h3>
                    </div>
                    <a href="{{ route('admin.news.index') }}" class="dashboard-text-link">View all news</a>
                </div>

                <div class="dashboard-list">
                    @forelse ($recentNews as $item)
                        <div class="dashboard-list-item">
                            <div class="dashboard-list-main">
                                <strong>{{ $item->title }}</strong>
                                <p>
                                    {{ $item->category?->name ?? 'Uncategorized' }} •
                                    {{ $item->user?->name ?? 'Unknown author' }}
                                </p>
                            </div>
                            <div class="dashboard-list-meta">
                                <span class="badge
                                    @if ($item->status === 'approved') badge-success
                                    @elseif ($item->status === 'rejected') badge-danger
                                    @else badge-warning @endif">
                                    {{ ucfirst($item->status ?? 'pending') }}
                                </span>
                                <small>{{ $item->publish_date?->format('M d, Y h:i A') ?? 'Not scheduled' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">
                            <i class="fas fa-newspaper"></i>
                            <p>No news has been created yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="content-card">
                <div class="dashboard-section-heading">
                    <div>
                        <span class="dashboard-section-label">Performance</span>
                        <h3>Popular stories</h3>
                    </div>
                </div>

                <div class="dashboard-list">
                    @forelse ($popularNews as $item)
                        <div class="dashboard-list-item">
                            <div class="dashboard-list-main">
                                <strong>{{ \Illuminate\Support\Str::limit($item->title, 60) }}</strong>
                                <p>{{ $item->category?->name ?? 'Uncategorized' }}</p>
                            </div>
                            <div class="dashboard-list-meta">
                                <span class="dashboard-chip">{{ number_format($item->total_views) }} views</span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">
                            <i class="fas fa-chart-line"></i>
                            <p>No view data is available yet.</p>
                        </div>
                    @endforelse
                </div>

                <div class="dashboard-divider"></div>

                <div class="dashboard-section-heading dashboard-subheading">
                    <div>
                        <span class="dashboard-section-label">Coverage map</span>
                        <h3>Top categories</h3>
                    </div>
                </div>

                <div class="dashboard-category-list">
                    @forelse ($topCategories as $category)
                        <div class="dashboard-category-item">
                            <div>
                                <strong>{{ $category->name }}</strong>
                                <p>{{ $category->news_count }} stories published</p>
                            </div>
                            <span class="dashboard-chip">{{ $loop->iteration }}</span>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">
                            <i class="fas fa-bookmark"></i>
                            <p>No categories are available yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
