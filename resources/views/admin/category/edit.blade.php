@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Edit Category')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Edit Category</h2>
                <a href="{{ route('admin.category.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>


            <form action="{{ route('admin.category.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $category->id }}">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name In English</label>
                            <input type="text" name="name" id="name" value="{{ $category->name }}" class="form-control" placeholder="Enter name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name In Hindi</label>
                            <input type="text" name="nameInHindi" value="{{ $category->nameInHindi }}" id="name" class="form-control" placeholder="Enter name">
                            @error('nameInHindi')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name In Gujarati</label>
                            <input type="text" name="nameInGujarati" id="name" value="{{ $category->nameInGujarati }}" class="form-control" placeholder="Enter name">
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

                            <!-- Current Image -->
                            <div class="mt-2">
                                <img src="{{ asset($category->image) }}" id="imagePreview" alt="Category Image" style="max-width: 200px; max-height: 150px; border:1px solid #ddd; padding:5px;">
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
        $('#image').on('change', function() {

            $('.image-error').remove();

            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                $(this).after(
                    '<span class="text-danger image-error">Only JPG, JPEG, PNG and WEBP images are allowed.</span>'
                );
                $(this).val('');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                $(this).after(
                    '<span class="text-danger image-error">Image size must be less than 2MB.</span>'
                );
                $(this).val('');
                return;
            }

            let reader = new FileReader();

            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            }

            reader.readAsDataURL(file);
        });
    </script>
    <script>
        document.getElementById('image').addEventListener('change', function(e) {

            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(event) {
                    document.getElementById('imagePreview').src = event.target.result;
                }

                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
