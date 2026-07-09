@extends('admin.layout.app')
@section('title', 'Lakhtar News Update - Add Alert')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Add Alert</h2>
                <a href="{{ route('admin.alert.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to Alert
                </a>
            </div>

            <form action="{{ route('admin.alert.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Title (English)<span class="text-sm text-danger">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control" placeholder="Enter title">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="titleInHindi">Title (Hindi)</label>
                            <input type="text" id="titleInHindi" name="titleInHindi" value="{{ old('titleInHindi') }}" class="form-control" placeholder="शीर्षक दर्ज करें">
                            @error('titleInHindi')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="titleInGujarati">Title (Gujarati)</label>
                            <input type="text" id="titleInGujarati" name="titleInGujarati" value="{{ old('titleInGujarati') }}" class="form-control" placeholder="શીર્ષક દાખલ કરો">
                            @error('titleInGujarati')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="type">Type<span class="text-sm text-danger">*</span></label>
                            <select id="type" name="type" class="form-control">
                                <option value="alert" {{ old('type', 'alert') === 'alert' ? 'selected' : '' }}>Alert</option>
                                <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Info</option>
                            </select>
                            @error('type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="end_date">End Date <span class="text-sm text-danger">*</span></label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                            @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
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
                            <label for="details">Details (English)<span class="text-sm text-danger">*</span></label>
                            <textarea id="details" name="details" rows="4" class="form-control" placeholder="Enter details">{{ old('details') }}</textarea>
                            @error('details')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="detailsInHindi">Details (Hindi)</label>
                            <textarea id="detailsInHindi" name="detailsInHindi" rows="4" class="form-control" placeholder="विवरण दर्ज करें">{{ old('detailsInHindi') }}</textarea>
                            @error('detailsInHindi')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="detailsInGujarati">Details (Gujarati)</label>
                            <textarea id="detailsInGujarati" name="detailsInGujarati" rows="4" class="form-control" placeholder="વિગતો દાખલ કરો">{{ old('detailsInGujarati') }}</textarea>
                            @error('detailsInGujarati')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
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
@endsection
