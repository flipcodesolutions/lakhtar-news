@extends('admin.layout.app')
@section('title', 'Lakhtar news - Add News')

@section('main')
    <style>
        :root {
            --primary-color: #b7131a;
            --primary-hover: #900f14;
            --bg-gray: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.01);
            --border-radius: 12px;
        }

        .main-content-inner {
            padding: 24px;
            background-color: #f1f5f9;
        }

        .news-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .news-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .news-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }

        .news-main {
            flex: 1 1 65%;
            min-width: 320px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .news-sidebar {
            flex: 1 1 30%;
            min-width: 280px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .editor-card, .sidebar-card, .actions-card {
            background: #ffffff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .editor-card:hover, .sidebar-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .editor-card {
            padding: 24px;
        }

        .sidebar-card {
            padding: 20px;
        }

        .sidebar-card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Tabs Styling */
        .tab-nav {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 24px;
            padding-bottom: 2px;
        }

        .tab-btn {
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -4px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--primary-color);
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-dark);
            background-color: var(--bg-gray);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(183, 19, 26, 0.1);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            line-height: 1.6;
        }

        /* Custom Toggle Switch for Featured Option */
        .radio-toggle-group {
            display: flex;
            background: var(--bg-gray);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 4px;
            gap: 4px;
        }

        .radio-toggle-label {
            flex: 1;
            position: relative;
            cursor: pointer;
        }

        .radio-toggle-label input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .radio-toggle-label span {
            display: block;
            text-align: center;
            padding: 8px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.2s ease;
        }

        .radio-toggle-label input:checked + span {
            background-color: var(--primary-color);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        /* Drag & Drop Upload Zone */
        .upload-zone {
            position: relative;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background-color: var(--bg-gray);
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .upload-zone:hover {
            border-color: var(--primary-color);
            background-color: rgba(183, 19, 26, 0.02);
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .upload-placeholder {
            text-align: center;
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .upload-placeholder i {
            font-size: 28px;
            color: #94a3b8;
            transition: color 0.2s ease;
        }

        .upload-zone:hover .upload-placeholder i {
            color: var(--primary-color);
        }

        .upload-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .upload-hint {
            font-size: 11px;
            color: var(--text-muted);
        }

        .upload-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3;
        }

        .upload-preview img, .upload-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-preview-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(15, 23, 42, 0.8);
            color: #ffffff;
            border: none;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 4;
        }

        .remove-preview-btn:hover {
            background-color: var(--primary-color);
            transform: scale(1.05);
        }

        /* Action Buttons */
        .actions-card {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-block {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: #ffffff;
            border: none;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-reset {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        .btn-reset:hover {
            background-color: var(--bg-gray);
            color: var(--text-dark);
        }

        .text-danger {
            font-size: 12px;
            font-weight: 500;
            margin-top: 4px;
            display: block;
            color: #ef4444;
        }

        .required-asterisk {
            color: #ef4444;
            margin-left: 2px;
        }

        .upload-optional {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: normal;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .news-grid {
                flex-direction: column;
            }
            .news-main, .news-sidebar {
                flex: 1 1 100%;
                max-width: 100%;
            }
        }
    </style>

    <div class="main-content-inner">
        <div class="news-header">
            <h2>Add News</h2>
            <a href="{{ route('admin.news.index') }}" class="btn" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="news-grid">
                <!-- Left: Content Inputs -->
                <div class="news-main">
                    <div class="editor-card">
                        <!-- Language Navigation Tabs -->
                        <div class="tab-nav">
                            <button type="button" class="tab-btn active" data-tab="en">
                                <i class="fas fa-globe"></i> English
                            </button>
                            <button type="button" class="tab-btn" data-tab="hi">
                                <i class="fas fa-language"></i> Hindi (हिन्दी)
                            </button>
                            <button type="button" class="tab-btn" data-tab="gu">
                                <i class="fas fa-language"></i> Gujarati (ગુજરાતી)
                            </button>
                        </div>

                        <!-- Tab Panes -->
                        <div class="tab-content">
                            <!-- English Tab -->
                            <div class="tab-pane active" id="tab-en">
                                <div class="form-group">
                                    <label for="title">Title In English <span class="required-asterisk">*</span></label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control" placeholder="Enter title in English">
                                    @error('title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="description">Description In English <span class="required-asterisk">*</span></label>
                                    <textarea name="description" id="description" rows="12" class="form-control" placeholder="Enter description in English">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Hindi Tab -->
                            <div class="tab-pane" id="tab-hi">
                                <div class="form-group">
                                    <label for="titleInHindi">Title In Hindi <span class="required-asterisk">*</span></label>
                                    <input type="text" name="titleInHindi" id="titleInHindi" value="{{ old('titleInHindi') }}" class="form-control" placeholder="Enter Hindi title">
                                    @error('titleInHindi')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="descriptionInHindi">Description In Hindi <span class="required-asterisk">*</span></label>
                                    <textarea name="descriptionInHindi" id="descriptionInHindi" rows="12" class="form-control" placeholder="Enter Hindi description">{{ old('descriptionInHindi') }}</textarea>
                                    @error('descriptionInHindi')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Gujarati Tab -->
                            <div class="tab-pane" id="tab-gu">
                                <div class="form-group">
                                    <label for="titleInGujarati">Title In Gujarati <span class="required-asterisk">*</span></label>
                                    <input type="text" name="titleInGujarati" id="titleInGujarati" value="{{ old('titleInGujarati') }}" class="form-control" placeholder="Enter Gujarati title">
                                    @error('titleInGujarati')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="descriptionInGujarati">Description In Gujarati <span class="required-asterisk">*</span></label>
                                    <textarea name="descriptionInGujarati" id="descriptionInGujarati" rows="12" class="form-control" placeholder="Enter Gujarati description">{{ old('descriptionInGujarati') }}</textarea>
                                    @error('descriptionInGujarati')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Metadata & Media Sidebar -->
                <div class="news-sidebar">
                    <!-- Publishing Options -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">
                            <i class="fas fa-cog"></i> Publishing Options
                        </h3>

                        <div class="form-group">
                            <label for="category_id">Category <span class="required-asterisk">*</span></label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="news_type">News Type <span class="required-asterisk">*</span></label>
                            <select name="news_type" id="news_type" class="form-control">
                                <option value="">Select News Type</option>
                                <option value="normal" {{ old('news_type') == 'normal' ? 'selected' : '' }}>Normal News</option>
                                <option value="breaking" {{ old('news_type') == 'breaking' ? 'selected' : '' }}>Breaking News</option>
                                <option value="trending" {{ old('news_type') == 'trending' ? 'selected' : '' }}>Trending News</option>
                                <option value="live" {{ old('news_type') == 'live' ? 'selected' : '' }}>Live News</option>
                            </select>
                            @error('news_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="publish_date">Publish Date <span class="required-asterisk">*</span></label>
                            <input type="date" name="publish_date" id="publish_date" value="{{ old('publish_date', date('Y-m-d')) }}" class="form-control">
                            @error('publish_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Is Featured? <span class="required-asterisk">*</span></label>
                            <div class="radio-toggle-group">
                                <label class="radio-toggle-label">
                                    <input type="radio" name="is_featured" value="1" {{ old('is_featured') == '1' ? 'checked' : '' }}>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-toggle-label">
                                    <input type="radio" name="is_featured" value="0" {{ old('is_featured', '0') == '0' ? 'checked' : '' }}>
                                    <span>No</span>
                                </label>
                            </div>
                            @error('is_featured')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Media Uploads -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">
                            <i class="fas fa-image"></i> News Media
                        </h3>

                        <!-- Image File Upload -->
                        <div class="form-group">
                            <label>Featured Image <span class="required-asterisk">*</span></label>
                            <div class="upload-zone" id="image-upload-zone">
                                <input type="file" accept="image/*" name="image" id="image" class="file-input">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="upload-text">Choose image or drag here</span>
                                    <span class="upload-hint">JPG, PNG, WEBP (Max 2MB)</span>
                                </div>
                                <div class="upload-preview" id="image-preview-container">
                                    <img id="imagePreview" src="" alt="Image Preview">
                                    <button type="button" class="remove-preview-btn" data-input-id="image" title="Remove image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @error('image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Video File Upload -->
                        <div class="form-group">
                            <label>News Video <span class="upload-optional">(Optional)</span></label>
                            <div class="upload-zone" id="video-upload-zone">
                                <input type="file" accept="video/*" name="video" id="video" class="file-input">
                                <div class="upload-placeholder">
                                    <i class="fas fa-video"></i>
                                    <span class="upload-text">Choose video or drag here</span>
                                    <span class="upload-hint">MP4, MOV, AVI (Max 20MB)</span>
                                </div>
                                <div class="upload-preview" id="video-preview-container">
                                    <video id="videoPreview" controls></video>
                                    <button type="button" class="remove-preview-btn" data-input-id="video" title="Remove video">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @error('video')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Video Thumbnail Upload (Required ONLY when video exists) -->
                        <div class="form-group" id="video-thumbnail-group" style="display: none;">
                            <label>Video Thumbnail <span class="required-asterisk">*</span></label>
                            <div class="upload-zone" id="thumbnail-upload-zone">
                                <input type="file" accept="image/*" name="video_thumbnail" id="video_thumbnail" class="file-input">
                                <div class="upload-placeholder">
                                    <i class="fas fa-image"></i>
                                    <span class="upload-text">Choose thumbnail</span>
                                    <span class="upload-hint">JPG, PNG, WEBP (Max 2MB)</span>
                                </div>
                                <div class="upload-preview" id="thumbnail-preview-container">
                                    <img id="videoThumbnailPreview" src="" alt="Thumbnail Preview">
                                    <button type="button" class="remove-preview-btn" data-input-id="video_thumbnail" title="Remove thumbnail">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @error('video_thumbnail')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="actions-card">
                        <button type="submit" class="btn-block btn-submit">
                            <i class="fas fa-save"></i> Publish News
                        </button>
                        <button type="reset" class="btn-block btn-reset" id="btn-reset-form">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Multilingual tab switching logic
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');

                    // Reset tabs
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    // Activate selected
                    this.classList.add('active');
                    document.getElementById(`tab-${targetTab}`).classList.add('active');
                });
            });

            // Media Preview handlers
            const mediaConfigs = {
                image: {
                    input: document.getElementById('image'),
                    preview: document.getElementById('imagePreview'),
                    container: document.getElementById('image-preview-container')
                },
                video: {
                    input: document.getElementById('video'),
                    preview: document.getElementById('videoPreview'),
                    container: document.getElementById('video-preview-container')
                },
                video_thumbnail: {
                    input: document.getElementById('video_thumbnail'),
                    preview: document.getElementById('videoThumbnailPreview'),
                    container: document.getElementById('thumbnail-preview-container')
                }
            };

            const videoThumbnailGroup = document.getElementById('video-thumbnail-group');

            // Setup file inputs listeners
            Object.keys(mediaConfigs).forEach(key => {
                const config = mediaConfigs[key];
                
                config.input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        config.preview.src = url;
                        config.container.style.display = 'flex';

                        // Specific logic when a video is selected
                        if (key === 'video') {
                            videoThumbnailGroup.style.display = 'block';
                        }
                    }
                });
            });

            // Setup remove preview buttons listeners
            document.querySelectorAll('.remove-preview-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const inputId = this.getAttribute('data-input-id');
                    const config = mediaConfigs[inputId];

                    if (config) {
                        config.input.value = ''; // clear input
                        config.preview.src = ''; // clear preview
                        config.container.style.display = 'none'; // hide preview container

                        // Specific logic when a video is removed
                        if (inputId === 'video') {
                            videoThumbnailGroup.style.display = 'none';
                            // Also clear the thumbnail
                            const thumbConfig = mediaConfigs['video_thumbnail'];
                            thumbConfig.input.value = '';
                            thumbConfig.preview.src = '';
                            thumbConfig.container.style.display = 'none';
                        }
                    }
                });
            });

            // Handle Form Reset
            document.getElementById('btn-reset-form').addEventListener('click', function(e) {
                // Wait for native reset to populate fields first
                setTimeout(() => {
                    Object.keys(mediaConfigs).forEach(key => {
                        const config = mediaConfigs[key];
                        config.preview.src = '';
                        config.container.style.display = 'none';
                    });
                    videoThumbnailGroup.style.display = 'none';
                    
                    // Switch back to English tab
                    tabBtns[0].click();
                }, 10);
            });

            // Prevent form submit if tab has validation errors and let the user see it
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                // A helper to auto-switch to a tab that has validation error displays, if any
                setTimeout(() => {
                    const firstError = document.querySelector('.tab-pane .text-danger');
                    if (firstError) {
                        const parentPane = firstError.closest('.tab-pane');
                        if (parentPane) {
                            const tabId = parentPane.id.replace('tab-', '');
                            const correspondingBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
                            if (correspondingBtn) {
                                correspondingBtn.click();
                            }
                        }
                    }
                }, 50);
            });
        });
    </script>
@endsection
