@extends('layouts.main')

@section('page-title')
    {{ __('Edit AI Content') }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}, {{ __('Edit') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('ai-content.content.update', $content) }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Edit Content') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Content Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $content->title) }}" required>
                                @error('title')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" {{ old('status', $content->status) == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                    <option value="published" {{ old('status', $content->status) == 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                    <option value="archived" {{ old('status', $content->status) == 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Generated Content') }} <span class="text-danger">*</span></label>
                        <textarea name="generated_content" class="form-control" rows="20" required>{{ old('generated_content', $content->generated_content) }}</textarea>
                        @error('generated_content')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('ai-content.content.show', $content) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> {{ __('Update Content') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
