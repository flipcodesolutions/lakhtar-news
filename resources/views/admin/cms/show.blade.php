@extends('admin.layout.app')
@section('title', 'Lakhtar news - ' . ($cms->title ?? 'CMS Page'))
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>{{ $cms->title ?? 'CMS Page' }}</h2>
            </div>

            <form action="{{ route('admin.cms.update', ['slug' => $cms->slug]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="slug" value="{{ old('slug', $cms->slug) }}">

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Title<span class="text-sm text-danger">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title', $cms->title) }}" class="form-control" placeholder="Enter title">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="detail">Detail<span class="text-sm text-danger">*</span></label>
                            <textarea id="detail" name="detail" rows="12" class="form-control" placeholder="Enter page content">{{ old('detail', $cms->detail) }}</textarea>
                            @error('detail')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Update</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
@endsection
