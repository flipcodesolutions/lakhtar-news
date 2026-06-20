@extends('admin.layout.app')
@section('title', 'Lakhtar news - Media Library')

@section('main')
    <style>
        .media-library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
            margin-top: 18px;
        }

        .media-library-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .media-preview {
            height: 190px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .media-preview-placeholder {
            color: #64748b;
            text-align: center;
            padding: 24px;
        }

        .media-preview-placeholder i {
            font-size: 34px;
            margin-bottom: 10px;
            display: block;
            color: #b7131a;
        }

        .media-card-body {
            padding: 16px;
        }

        .media-card-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .media-card-id {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .media-card-path {
            font-size: 13px;
            color: #1e293b;
            word-break: break-word;
        }

        .media-type-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .media-type-image {
            background: #dcfce7;
            color: #15803d;
        }

        .media-type-video {
            background: #fee2e2;
            color: #b91c1c;
        }

        .media-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .media-meta-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            padding: 10px;
        }

        .media-meta-item label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .media-meta-item span {
            font-size: 13px;
            color: #1e293b;
            word-break: break-word;
        }

        .media-news-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .media-news-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            text-decoration: none;
        }

        .media-news-pill:hover {
            background: #e2e8f0;
        }

        .media-card-actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-disabled {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #94a3b8;
            cursor: not-allowed;
            font-size: 13px;
            font-weight: 600;
        }
    </style>

    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Media Library</h2>
                <a href="{{ route('admin.news.create') }}" class="btn">
                    <i class="fas fa-plus"></i> Use In News
                </a>
            </div>

            <form method="GET" action="{{ route('admin.media.index') }}" class="list-filter-form">
                <div class="list-filter-grid">
                    <div class="list-filter-field list-filter-search">
                        <label for="media-search">Search</label>
                        <input type="text" id="media-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by file path, caption, news title, uploader">
                    </div>
                    <div class="list-filter-field">
                        <label for="media-type">Media Type</label>
                        <select id="media-type" name="type" class="form-control">
                            <option value="">All Media</option>
                            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Images</option>
                            <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
                        </select>
                    </div>
                    <div class="list-filter-actions">
                        <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="list-results-meta">
                @if ($mediaItems->total() > 0)
                    Showing <strong>{{ $mediaItems->firstItem() }}</strong> - <strong>{{ $mediaItems->lastItem() }}</strong> of
                    <strong>{{ $mediaItems->total() }}</strong> media items
                @else
                    Showing <strong>0</strong> media items
                @endif
            </div>

            <div class="media-library-grid">
                @forelse ($mediaItems as $media)
                    <div class="media-library-card">
                        <div class="media-preview">
                            @if ($media->media_type === 'image')
                                <img src="{{ asset($media->file_path) }}" alt="Media image">
                            @else
                                <div class="media-preview-placeholder">
                                    <i class="fab fa-youtube"></i>
                                    <a href="{{ $media->file_path }}" target="_blank" rel="noopener noreferrer">Open YouTube URL</a>
                                </div>
                            @endif
                        </div>

                        <div class="media-card-body">
                            <div class="media-card-top">
                                <div>
                                    <div class="media-card-id">Media #{{ $media->id }}</div>
                                    <div class="media-card-path">{{ $media->caption ?: $media->file_path }}</div>
                                </div>
                                <span class="media-type-badge media-type-{{ $media->media_type }}">{{ $media->media_type }}</span>
                            </div>

                            <div class="media-meta">
                                <div class="media-meta-item">
                                    <label>Used In</label>
                                    <span>{{ $media->news_count }} news</span>
                                </div>
                                <div class="media-meta-item">
                                    <label>Uploaded By</label>
                                    <span>{{ $media->uploader?->name ?? 'Unknown' }}</span>
                                </div>
                            </div>

                            <div class="media-meta-item">
                                <label>Linked News</label>
                                @if ($media->news->isNotEmpty())
                                    <div class="media-news-links">
                                        @foreach ($media->news as $news)
                                            <a href="{{ route('admin.news.edit', ['id' => $news->id]) }}" class="media-news-pill">{{ $news->title }}</a>
                                        @endforeach
                                    </div>
                                @else
                                    <span>Not used in any news yet.</span>
                                @endif
                            </div>

                            <div class="media-card-actions">
                                <a href="{{ route('admin.news.create', ['library_media' => $media->id]) }}" class="btn-sm">
                                    <i class="fas fa-share"></i> Reuse In News
                                </a>
                                @if ($media->news_count === 0)
                                    <a href="{{ route('admin.media.destroy', ['id' => $media->id]) }}" class="btn-sm delete-record" style="border-color: #ef4444; color: #ef4444;">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                @else
                                    <span class="btn-disabled">In use by news</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="media-library-card" style="grid-column: 1 / -1;">
                        <div class="media-card-body" style="text-align:center; padding:40px 20px;">
                            <i class="fas fa-images" style="font-size:42px; color:#cbd5e1; margin-bottom:12px;"></i>
                            <p style="margin:0; color:#64748b;">No media found.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($mediaItems->hasPages())
                <div class="list-pagination">
                    <div class="list-pagination-info">
                        Page <strong>{{ $mediaItems->currentPage() }}</strong> of <strong>{{ $mediaItems->lastPage() }}</strong>
                    </div>
                    {{ $mediaItems->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
