@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Edit Banner')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Edit Banner</h2>
                <a href="{{ route('admin.banner.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to Banner
                </a>
            </div>

            <form action="{{ route('admin.banner.update', ['id' => $banner->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title', $banner->title) }}" class="form-control" placeholder="Enter title">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="link">Link (Optional)</label>
                            <input type="url" id="link" name="link" value="{{ old('link', $banner->link) }}" class="form-control" placeholder="https://example.com">
                            @error('link')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="start_date">Start Date<span class="text-sm text-danger">*</span> </label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $banner->start_date ? \Illuminate\Support\Carbon::parse($banner->start_date)->format('Y-m-d') : '') }}" class="form-control">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="end_date">End Date<span class="text-sm text-danger">*</span> </label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $banner->end_date ? \Illuminate\Support\Carbon::parse($banner->end_date)->format('Y-m-d') : '') }}" class="form-control">
                            @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="1" {{ old('status', $banner->status ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $banner->status ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="image">Image (Optional)</label>
                            <input type="file" accept="image/*" name="image" id="image" class="form-control">
                            @error('image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            <div style="margin-top: 15px;">
                                @if ($banner->image)
                                    <img id="imagePreview" src="{{ asset($banner->image) }}" alt="Banner Image" style="width:220px; height:120px; object-fit:cover; border:1px solid #ddd; border-radius:8px; padding:5px;">
                                @else
                                    <img id="imagePreview" src="" alt="Image Preview" style="display:none; width:220px; height:120px; object-fit:cover; border:1px solid #ddd; border-radius:8px; padding:5px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Submit</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('imagePreview');

            if (!imageInput || !imagePreview) {
                return;
            }

            imageInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;

                if (!file) {
                    imagePreview.style.display = '{{ $banner->image ? 'block' : 'none' }}';
                    imagePreview.src = '{{ $banner->image ? asset($banner->image) : '' }}';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection
