@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Reporter News List')
@section('main')
    <style>
        .review-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 2000;
            padding: 24px;
            overflow-y: auto;
        }

        .review-modal-overlay.active {
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .review-modal {
            width: 100%;
            max-width: 920px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            margin: auto;
            overflow: hidden;
        }

        .review-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .review-modal-header h3 {
            margin: 0;
            font-size: 20px;
            color: #1e293b;
        }

        .review-modal-close {
            border: none;
            background: transparent;
            font-size: 28px;
            line-height: 1;
            color: #64748b;
            cursor: pointer;
        }

        .review-modal-body {
            padding: 24px;
            max-height: calc(100vh - 220px);
            overflow-y: auto;
        }

        .review-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .review-meta-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }

        .review-meta-item label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .review-meta-item span {
            color: #1e293b;
            font-weight: 600;
            text-transform: capitalize;
        }

        .review-section {
            margin-bottom: 24px;
        }

        .review-section h4 {
            margin: 0 0 12px;
            font-size: 16px;
            color: #b7131a;
            border-bottom: 2px solid #fde8e9;
            padding-bottom: 8px;
        }

        .review-lang-block {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .review-lang-block h5 {
            margin: 0 0 8px;
            font-size: 14px;
            color: #475569;
        }

        .review-lang-block p.title {
            margin: 0 0 10px;
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .review-description {
            color: #334155;
            line-height: 1.6;
        }

        .review-media {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .review-media-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            background: #f8fafc;
        }

        .review-media-box img,
        .review-media-box video,
        .review-media-box iframe {
            width: 100%;
            max-height: 280px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
        }

        .review-media-box iframe {
            min-height: 220px;
            border: none;
        }

        .review-media-box a {
            color: #0f5ad1;
            font-weight: 600;
            word-break: break-all;
        }

        .reporter-thumb-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reporter-thumb-meta {
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }

        .list-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .list-pagination-info {
            font-size: 14px;
            color: #64748b;
        }

        .list-pagination .pagination {
            margin: 0;
        }

        .list-pagination .pagination svg {
            width: 16px;
            height: 16px;
        }

        .swal2-container.reporter-review-swal {
            z-index: 3000 !important;
        }

        .review-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .review-empty {
            color: #94a3b8;
            font-style: italic;
        }
    </style>

    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Reporter News List</h2>
            </div>

            <form method="GET" action="{{ route('admin.reporter-news.index') }}" class="list-filter-form">
                <div class="list-filter-grid reporter-filter-grid">
                    <div class="list-filter-field list-filter-search">
                        <label for="reporter-news-search">Search</label>
                        <input type="text" id="reporter-news-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title, reporter, email, or category">
                    </div>
                    <div class="list-filter-field">
                        <label for="reporter-news-status">Status</label>
                        <select id="reporter-news-status" name="status" class="form-control">
                            <option value="pending" {{ ($selectedStatus ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($selectedStatus ?? 'pending') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ ($selectedStatus ?? 'pending') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="list-filter-actions">
                        <a href="{{ route('admin.reporter-news.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="list-results-meta">
                Showing <strong>{{ $news->firstItem() ?? 0 }}</strong> to <strong>{{ $news->lastItem() ?? 0 }}</strong> of
                <strong>{{ $news->total() }}</strong> reporter submissions
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Title</th>
                            <th>Reporter</th>
                            <th>Category</th>
                            <th>News Type</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Publish Date</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($news as $item)
                            @php
                                $previewImage = $item->media->firstWhere('media_type', 'image');
                                $imageCount = $item->media->where('media_type', 'image')->count();
                                $videoCount = $item->media->where('media_type', 'video')->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="reporter-thumb-wrap">
                                        @if ($previewImage)
                                            <img src="{{ asset($previewImage->file_path) }}" alt="{{ $item->title }}" width="50" height="50" style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd; padding: 2px;">
                                        @else
                                            <span style="color: #cbd5e1;"><i class="fas fa-image fa-2x"></i></span>
                                        @endif
                                        <div class="reporter-thumb-meta">
                                            <div>{{ $imageCount }} image{{ $imageCount === 1 ? '' : 's' }}</div>
                                            <div>{{ $videoCount }} video{{ $videoCount === 1 ? '' : 's' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-weight: 600; color: #1e293b;">{{ $item->title }}</td>
                                <td>{{ $item->user?->name ?? '-' }}</td>
                                <td>
                                    <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 13px;">
                                        {{ $item->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="text-transform: capitalize; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                        @if ($item->news_type == 'breaking') background: #ffebeb; color: #b7131a;
                                        @elseif($item->news_type == 'trending') background: #e0f2fe; color: #0369a1;
                                        @elseif($item->news_type == 'live') background: #fef3c7; color: #d97706;
                                        @else background: #f1f5f9; color: #475569; @endif">
                                        {{ $item->news_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                        @if ($item->is_featured) background: #dcfce7; color: #15803d; @else background: #f1f5f9; color: #64748b; @endif">
                                        {{ $item->is_featured ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge status-badge" data-status="{{ $item->status }}" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                        @if ($item->status === 'approved') background: #dcfce7; color: #15803d;
                                        @elseif ($item->status === 'rejected') background: #ffebeb; color: #b7131a;
                                        @else background: #fef3c7; color: #d97706; @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>{{ $item->publish_date ? $item->publish_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    <button type="button" class="btn-sm review-news-btn" data-id="{{ $item->id }}">
                                        <i class="fas fa-eye"></i> Review
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center" style="padding: 40px 0; color: #64748b;">
                                    <i class="fas fa-newspaper fa-3x" style="margin-bottom: 12px; color: #cbd5e1; display: block;"></i>
                                    No reporter news found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($news->hasPages())
                <div class="list-pagination">
                    <div class="list-pagination-info">
                        Page <strong>{{ $news->currentPage() }}</strong> of <strong>{{ $news->lastPage() }}</strong>
                    </div>
                    {{ $news->withQueryString()->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="reviewModalOverlay" class="review-modal-overlay">
        <div class="review-modal" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
            <div class="review-modal-header">
                <h3 id="reviewModalTitle">Review News</h3>
                <button type="button" class="review-modal-close" id="closeReviewModal">&times;</button>
            </div>
            <div class="review-modal-body" id="reviewModalBody"></div>
            <div class="review-modal-footer">
                <button type="button" class="btn-sm btn-secondary" id="cancelReviewModal">Close</button>
                <a href="#" id="rejectNewsBtn" class="btn-sm btn-danger">Reject</a>
                <a href="#" id="approveNewsBtn" class="btn-sm btn-success">Approve</a>
            </div>
        </div>
    </div>

    @php
        $newsItems = $news->map(function ($item) {
            $media = $item->media
                ->map(function ($mediaItem) {
                    $url = $mediaItem->file_path;

                    if ($mediaItem->media_type === 'image' && $url !== null) {
                        $url = asset($url);
                    } elseif ($url !== null && !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                        $url = asset($url);
                    }

                    return [
                        'id' => $mediaItem->id,
                        'type' => $mediaItem->media_type,
                        'url' => $url,
                    ];
                })
                ->values();

            return [
                'id' => $item->id,
                'status' => $item->status,
                'reject_reason' => $item->reject_reason,
                'title' => $item->title,
                'titleInHindi' => $item->titleInHindi,
                'titleInGujarati' => $item->titleInGujarati,
                'description' => $item->description,
                'descriptionInHindi' => $item->descriptionInHindi,
                'descriptionInGujarati' => $item->descriptionInGujarati,
                'reporter_name' => $item->user?->name,
                'reporter_email' => $item->user?->email,
                'reporter_mobile' => $item->user?->mobile,
                'category' => $item->category?->name,
                'media' => $media,
                'publish_date' => $item->publish_date?->format('M d, Y'),
                'news_type' => $item->news_type,
                'is_featured' => (bool) $item->is_featured,
                'approve_url' => route('admin.reporter-news.change-status', ['id' => $item->id, 'status' => 'approved']),
                'reject_url' => route('admin.reporter-news.change-status', ['id' => $item->id, 'status' => 'rejected']),
            ];
        });
    @endphp

    <script>
        const reporterNewsItems = @json($newsItems);

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function isYoutubeUrl(url) {
            return /(?:youtube\.com|youtu\.be)/i.test(String(url || ''));
        }

        function getYoutubeEmbedUrl(url) {
            try {
                const parsedUrl = new URL(url);

                if (parsedUrl.hostname.includes('youtu.be')) {
                    const videoId = parsedUrl.pathname.replace('/', '');
                    return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
                }

                if (parsedUrl.pathname.includes('/shorts/')) {
                    const segments = parsedUrl.pathname.split('/').filter(Boolean);
                    const videoId = segments[segments.length - 1];
                    return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
                }

                const videoId = parsedUrl.searchParams.get('v');
                return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
            } catch (error) {
                return null;
            }
        }

        function renderMediaItem(mediaItem, index) {
            if (!mediaItem || !mediaItem.url) {
                return '';
            }

            const label = `${escapeHtml(mediaItem.type === 'image' ? 'Image' : 'Video')} ${index + 1}`;

            if (mediaItem.type === 'image') {
                return `<div class="review-media-box"><label>${label}</label><img src="${mediaItem.url}" alt="${label}"></div>`;
            }

            if (mediaItem.type === 'video') {
                if (isYoutubeUrl(mediaItem.url)) {
                    const embedUrl = getYoutubeEmbedUrl(mediaItem.url);

                    if (embedUrl) {
                        return `<div class="review-media-box"><label>${label}</label><iframe src="${embedUrl}" allowfullscreen loading="lazy"></iframe><div style="margin-top: 8px;"><a href="${mediaItem.url}" target="_blank" rel="noopener">Open YouTube URL</a></div></div>`;
                    }
                }

                return `<div class="review-media-box"><label>${label}</label><video src="${mediaItem.url}" controls></video></div>`;
            }

            return '';
        }

        function renderMediaGallery(mediaItems) {
            if (!Array.isArray(mediaItems) || mediaItems.length === 0) {
                return `<div class="review-media-box"><label>Media</label><p class="review-empty">Not provided</p></div>`;
            }

            return mediaItems.map((mediaItem, index) => renderMediaItem(mediaItem, index)).join('');
        }

        function renderLanguageBlock(language, title, description) {
            return `
                <div class="review-lang-block">
                    <h5>${escapeHtml(language)}</h5>
                    <p class="title">${escapeHtml(title || '-')}</p>
                    <div class="review-description">${description || '<p class="review-empty">No description</p>'}</div>
                </div>
            `;
        }

        function openReviewModal(newsId) {
            const item = reporterNewsItems.find(entry => entry.id === newsId);

            if (!item) {
                return;
            }

            document.getElementById('reviewModalTitle').textContent = 'Review: ' + (item.title || 'News');
            document.getElementById('reviewModalBody').innerHTML = `
                <div class="review-meta-grid">
                    <div class="review-meta-item">
                        <label>Reporter</label>
                        <span>${escapeHtml(item.reporter_name || '-')}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>Email</label>
                        <span>${escapeHtml(item.reporter_email || '-')}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>Mobile</label>
                        <span>${escapeHtml(item.reporter_mobile || '-')}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>Category</label>
                        <span>${escapeHtml(item.category || '-')}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>News Type</label>
                        <span>${escapeHtml(item.news_type || '-')}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>Featured</label>
                        <span>${item.is_featured ? 'Yes' : 'No'}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>Publish Date</label>
                        <span>${escapeHtml(item.publish_date || '-')}</span>
                    </div>
                    <div class="review-meta-item">
                        <label>Current Status</label>
                        <span>${escapeHtml(item.status || '-')}</span>
                    </div>
                </div>

                <div class="review-section">
                    <h4>Content</h4>
                    ${renderLanguageBlock('English', item.title, item.description)}
                    ${renderLanguageBlock('Hindi', item.titleInHindi, item.descriptionInHindi)}
                    ${renderLanguageBlock('Gujarati', item.titleInGujarati, item.descriptionInGujarati)}
                </div>

                <div class="review-section">
                    <h4>Media</h4>
                    <div class="review-media">
                        ${renderMediaGallery(item.media)}
                    </div>
                </div>

                <div class="review-section">
                    <h4>Reject Reason</h4>
                    <div class="review-lang-block">
                        <div class="review-description">
                            ${item.reject_reason ? `<p>${escapeHtml(item.reject_reason)}</p>` : '<p class="review-empty">No reject reason added</p>'}
                        </div>
                    </div>
                </div>
            `;

            const approveBtn = document.getElementById('approveNewsBtn');
            const rejectBtn = document.getElementById('rejectNewsBtn');

            approveBtn.href = item.approve_url;
            rejectBtn.href = item.reject_url;

            approveBtn.style.display = item.status === 'approved' ? 'none' : 'inline-block';
            rejectBtn.style.display = item.status === 'rejected' ? 'none' : 'inline-block';

            document.getElementById('reviewModalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeReviewModal() {
            document.getElementById('reviewModalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.review-news-btn').forEach(button => {
            button.addEventListener('click', function() {
                openReviewModal(Number(this.dataset.id));
            });
        });

        document.getElementById('closeReviewModal').addEventListener('click', closeReviewModal);
        document.getElementById('cancelReviewModal').addEventListener('click', closeReviewModal);

        document.getElementById('reviewModalOverlay').addEventListener('click', function(event) {
            if (event.target === this) {
                closeReviewModal();
            }
        });

        function confirmStatusChange(event, options) {
            event.preventDefault();

            const targetUrl = event.currentTarget.getAttribute('href');
            if (!targetUrl) {
                return;
            }

            if (typeof Swal === 'undefined') {
                window.location.href = targetUrl;
                return;
            }

            if (options.requiresReason) {
                Swal.fire({
                    title: options.title,
                    input: 'textarea',
                    inputLabel: 'Reject reason',
                    inputPlaceholder: 'Enter reject reason',
                    inputValue: options.currentReason || '',
                    inputAttributes: {
                        'aria-label': 'Enter reject reason'
                    },
                    customClass: {
                        container: 'reporter-review-swal',
                    },
                    showCancelButton: true,
                    confirmButtonText: options.confirmButtonText,
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: options.confirmButtonColor,
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value || !value.trim()) {
                            return 'Reject reason is required.';
                        }

                        if (value.trim().length > 1000) {
                            return 'Reject reason must not exceed 1000 characters.';
                        }

                        return null;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const redirectUrl = new URL(targetUrl, window.location.origin);
                        redirectUrl.searchParams.set('reject_reason', result.value.trim());
                        window.location.href = redirectUrl.toString();
                    }
                });

                return;
            }

            Swal.fire({
                title: options.title,
                text: options.text,
                icon: options.icon,
                customClass: {
                    container: 'reporter-review-swal',
                },
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText,
                cancelButtonText: 'Cancel',
                confirmButtonColor: options.confirmButtonColor,
                cancelButtonColor: '#64748b',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = targetUrl;
                }
            });
        }

        document.getElementById('approveNewsBtn').addEventListener('click', function(event) {
            confirmStatusChange(event, {
                title: 'Approve News?',
                text: 'This reporter news will be approved.',
                icon: 'success',
                confirmButtonText: 'Yes, approve it',
                confirmButtonColor: '#15803d',
            });
        });

        document.getElementById('rejectNewsBtn').addEventListener('click', function(event) {
            const item = reporterNewsItems.find(entry => entry.reject_url === this.getAttribute('href'));

            confirmStatusChange(event, {
                title: 'Reject News?',
                text: 'This reporter news will be rejected.',
                icon: 'warning',
                confirmButtonText: 'Save reject reason',
                confirmButtonColor: '#b7131a',
                requiresReason: true,
                currentReason: item?.reject_reason || '',
            });
        });
    </script>
@endsection
