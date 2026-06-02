@extends('admin.layout.app')
@section('title', 'Lakhtar news - Add Language')
@section('main')
    <div class="main-content-inner">
        <div class="content-card">
            <div class="view-header">
                <h2>Add Language</h2>
                <a href="{{ route('admin.language.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>


            <form action="{{ route('admin.language.store') }}" method="post">
                @csrf
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" class="form-control" placeholder="Enter name" name="name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="code">Code</label>
                            <input type="text" id="code" class="form-control" placeholder="Enter code" name="code">
                            @error('code')
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
