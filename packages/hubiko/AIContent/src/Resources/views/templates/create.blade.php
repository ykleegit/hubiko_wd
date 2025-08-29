@extends('layouts.main')

@section('page-title')
    {{ __('Create AI Template') }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}, {{ __('Templates') }}, {{ __('Create') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('ai-content.templates.store') }}">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Create New Template') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    <option value="content" {{ old('category') == 'content' ? 'selected' : '' }}>{{ __('Content') }}</option>
                                    <option value="marketing" {{ old('category') == 'marketing' ? 'selected' : '' }}>{{ __('Marketing') }}</option>
                                    <option value="social_media" {{ old('category') == 'social_media' ? 'selected' : '' }}>{{ __('Social Media') }}</option>
                                    <option value="email" {{ old('category') == 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                                </select>
                                @error('category')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Content Type') }} <span class="text-danger">*</span></label>
                                <select name="content_type" class="form-select" required>
                                    <option value="">{{ __('Select Content Type') }}</option>
                                    <option value="blog_post" {{ old('content_type') == 'blog_post' ? 'selected' : '' }}>{{ __('Blog Post') }}</option>
                                    <option value="article" {{ old('content_type') == 'article' ? 'selected' : '' }}>{{ __('Article') }}</option>
                                    <option value="product_description" {{ old('content_type') == 'product_description' ? 'selected' : '' }}>{{ __('Product Description') }}</option>
                                    <option value="social_media" {{ old('content_type') == 'social_media' ? 'selected' : '' }}>{{ __('Social Media Post') }}</option>
                                    <option value="email" {{ old('content_type') == 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                                    <option value="ad_copy" {{ old('content_type') == 'ad_copy' ? 'selected' : '' }}>{{ __('Ad Copy') }}</option>
                                </select>
                                @error('content_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Default Tone') }}</label>
                                <select name="default_tone" class="form-select">
                                    <option value="professional" {{ old('default_tone') == 'professional' ? 'selected' : '' }}>{{ __('Professional') }}</option>
                                    <option value="casual" {{ old('default_tone') == 'casual' ? 'selected' : '' }}>{{ __('Casual') }}</option>
                                    <option value="friendly" {{ old('default_tone') == 'friendly' ? 'selected' : '' }}>{{ __('Friendly') }}</option>
                                    <option value="formal" {{ old('default_tone') == 'formal' ? 'selected' : '' }}>{{ __('Formal') }}</option>
                                    <option value="persuasive" {{ old('default_tone') == 'persuasive' ? 'selected' : '' }}>{{ __('Persuasive') }}</option>
                                    <option value="informative" {{ old('default_tone') == 'informative' ? 'selected' : '' }}>{{ __('Informative') }}</option>
                                </select>
                                @error('default_tone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Default Length') }}</label>
                                <select name="default_length" class="form-select">
                                    <option value="short" {{ old('default_length') == 'short' ? 'selected' : '' }}>{{ __('Short') }}</option>
                                    <option value="medium" {{ old('default_length') == 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                    <option value="long" {{ old('default_length') == 'long' ? 'selected' : '' }}>{{ __('Long') }}</option>
                                </select>
                                @error('default_length')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Prompt Template') }} <span class="text-danger">*</span></label>
                        <textarea name="prompt_template" class="form-control" rows="6" required placeholder="{{ __('Enter your prompt template. Use {{variable_name}} for dynamic variables.') }}">{{ old('prompt_template') }}</textarea>
                        <small class="form-text text-muted">
                            {{ __('Use {{variable_name}} syntax for dynamic variables. Example: Write about {{topic}} for {{audience}}.') }}
                        </small>
                        @error('prompt_template')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Variables') }}</label>
                        <div id="variables-container">
                            <div class="variable-input mb-2">
                                <div class="input-group">
                                    <input type="text" name="variables[]" class="form-control" placeholder="{{ __('Variable name (e.g., topic, audience)') }}">
                                    <button type="button" class="btn btn-outline-danger remove-variable" onclick="removeVariable(this)">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addVariable()">
                            <i class="ti ti-plus"></i> {{ __('Add Variable') }}
                        </button>
                        <small class="form-text text-muted d-block mt-1">
                            {{ __('Define variables that users can fill when using this template.') }}
                        </small>
                        @error('variables')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            {{ __('Active Template') }}
                        </label>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('ai-content.templates.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> {{ __('Create Template') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function addVariable() {
        const container = document.getElementById('variables-container');
        const newVariable = document.createElement('div');
        newVariable.className = 'variable-input mb-2';
        newVariable.innerHTML = `
            <div class="input-group">
                <input type="text" name="variables[]" class="form-control" placeholder="{{ __('Variable name (e.g., topic, audience)') }}">
                <button type="button" class="btn btn-outline-danger remove-variable" onclick="removeVariable(this)">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        `;
        container.appendChild(newVariable);
    }

    function removeVariable(button) {
        const container = document.getElementById('variables-container');
        if (container.children.length > 1) {
            button.closest('.variable-input').remove();
        }
    }
</script>
@endpush
