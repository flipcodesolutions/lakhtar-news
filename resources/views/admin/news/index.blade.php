@extends('admin.layout.app')
@section('title', 'Lakhtar news - News List')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>News List</h2>
                <a href="{{ route('admin.news.create') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> Add News
                </a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>News Type</th>
                            <th>Featured</th>
                            <th>Publish Date</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($news as $item)
                            <tr>
                                <td>
                                    @if ($item->image)
                                        <img src="{{ asset($item->image) }}" alt="{{ $item->title }}" width="50" height="50" style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd; padding: 2px;">
                                    @else
                                        <span style="color: #cbd5e1;"><i class="fas fa-image fa-2x"></i></span>
                                    @endif
                                </td>
                                <td style="font-weight: 600; color: #1e293b;">{{ $item->title }}</td>
                                <td>
                                    <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 13px;">
                                        {{ $item->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="text-transform: capitalize; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                        @if($item->news_type == 'breaking') background: #ffebeb; color: #b7131a;
                                        @elseif($item->news_type == 'trending') background: #e0f2fe; color: #0369a1;
                                        @elseif($item->news_type == 'live') background: #fef3c7; color: #d97706;
                                        @else background: #f1f5f9; color: #475569; @endif">
                                        {{ $item->news_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                        @if($item->is_featured) background: #dcfce7; color: #15803d; @else background: #f1f5f9; color: #64748b; @endif">
                                        {{ $item->is_featured ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>{{ $item->publish_date ? $item->publish_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.news.edit', ['id' => $item->id]) }}" class="btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.news.destroy', ['id' => $item->id]) }}" class="btn-sm delete-record" title="Delete" style="border-color: #ef4444; color: #ef4444;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 40px 0; color: #64748b;">
                                    <i class="fas fa-newspaper fa-3x" style="margin-bottom: 12px; color: #cbd5e1; display: block;"></i>
                                    No news articles found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
