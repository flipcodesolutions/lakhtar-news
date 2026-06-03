@extends('admin.layout.app')
@section('title', 'Lakhtar news - Add Category')

@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Add Category</h2>
                <a href="{{ route('admin.category.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <form action="{{ route('admin.category.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-row">

                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name In English</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter name">

                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="nameInHindi">Name In Hindi</label>
                            <input type="text" name="nameInHindi" id="nameInHindi" value="{{ old('nameInHindi') }}" class="form-control" placeholder="Enter Hindi name">

                            @error('nameInHindi')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="nameInGujarati">Name In Gujarati</label>
                            <input type="text" name="nameInGujarati" id="nameInGujarati" value="{{ old('nameInGujarati') }}" class="form-control" placeholder="Enter Gujarati name">

                            @error('nameInGujarati')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="image">Image</label>

                            <input type="file" accept="image/*" name="image" id="image" class="form-control">

                            @error('image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            <!-- Image Preview -->
                            <div style="margin-top: 15px;">
                                <img id="imagePreview" src="" alt="Image Preview" style="display:none;
                                           width:200px;
                                           height:200px;
                                           object-fit:cover;
                                           border:1px solid #ddd;
                                           border-radius:8px;
                                           padding:5px;">
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

            imageInput.addEventListener('change', function(e) {

                const file = e.target.files[0];

                if (!file) {
                    imagePreview.style.display = 'none';
                    imagePreview.src = '';
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
