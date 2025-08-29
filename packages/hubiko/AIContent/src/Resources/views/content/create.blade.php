@extends('layouts.main')

@section('page-title')
    {{ __('Generate AI Content') }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}, {{ __('Generate') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('ai-content.content.store') }}" id="ai-content-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Generate New Content') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Content Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="{{ __('Enter content title') }}" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Content Type') }} <span class="text-danger">*</span></label>
                                <select name="content_type" class="form-select" required>
                                    <option value="">{{ __('Select Content Type') }}</option>
                                    @foreach($contentTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('content_type') == $key ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('content_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Tone') }} <span class="text-danger">*</span></label>
                                <select name="tone" class="form-select" required>
                                    <option value="">{{ __('Select Tone') }}</option>
                                    @foreach($tones as $key => $tone)
                                        <option value="{{ $key }}" {{ old('tone') == $key ? 'selected' : '' }}>{{ $tone }}</option>
                                    @endforeach
                                </select>
                                @error('tone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Length') }} <span class="text-danger">*</span></label>
                                <select name="length" class="form-select" required>
                                    <option value="">{{ __('Select Length') }}</option>
                                    @foreach($lengths as $key => $length)
                                        <option value="{{ $key }}" {{ old('length') == $key ? 'selected' : '' }}>{{ $length }}</option>
                                    @endforeach
                                </select>
                                @error('length')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Language') }}</label>
                                <select name="language" class="form-select">
                                    <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                    <option value="es" {{ old('language') == 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                    <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                    <option value="de" {{ old('language') == 'de' ? 'selected' : '' }}>{{ __('German') }}</option>
                                    <option value="it" {{ old('language') == 'it' ? 'selected' : '' }}>{{ __('Italian') }}</option>
                                    <option value="pt" {{ old('language') == 'pt' ? 'selected' : '' }}>{{ __('Portuguese') }}</option>
                                </select>
                                @error('language')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('AI Provider') }}</label>
                                <select name="provider" class="form-select" id="provider-select" onchange="updateModels()">
                                    <option value="openai" {{ old('provider') == 'openai' ? 'selected' : '' }}>{{ __('OpenAI') }}</option>
                                    <option value="deepseek" {{ old('provider') == 'deepseek' ? 'selected' : '' }}>{{ __('DeepSeek') }}</option>
                                </select>
                                @error('provider')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('AI Model') }}</label>
                                <select name="model" class="form-select" id="model-select">
                                    <option value="gpt-3.5-turbo" {{ old('model') == 'gpt-3.5-turbo' ? 'selected' : '' }}>{{ __('GPT-3.5 Turbo') }}</option>
                                    <option value="gpt-4" {{ old('model') == 'gpt-4' ? 'selected' : '' }}>{{ __('GPT-4') }}</option>
                                    <option value="gpt-4-turbo" {{ old('model') == 'gpt-4-turbo' ? 'selected' : '' }}>{{ __('GPT-4 Turbo') }}</option>
                                </select>
                                @error('model')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Template') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                <select name="template_id" class="form-select" id="template-select">
                                    <option value="">{{ __('Select Template (Optional)') }}</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" 
                                                data-prompt="{{ $template->prompt_template }}"
                                                data-tone="{{ $template->default_tone }}"
                                                data-length="{{ $template->default_length }}"
                                                {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Keywords') }} <small class="text-muted">({{ __('Comma separated') }})</small></label>
                                <input type="text" name="keywords" class="form-control" placeholder="{{ __('keyword1, keyword2, keyword3') }}" value="{{ old('keywords') }}">
                                <small class="text-muted">{{ __('Enter keywords separated by commas to include in the content') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Content Prompt') }} <span class="text-danger">*</span></label>
                        <textarea name="prompt" class="form-control" rows="6" placeholder="{{ __('Describe what content you want to generate...') }}" required>{{ old('prompt') }}</textarea>
                        <small class="text-muted">{{ __('Be specific about what you want. The more detailed your prompt, the better the AI can generate relevant content.') }}</small>
                        @error('prompt')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('ai-content.content.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary" id="generate-btn">
                            <i class="ti ti-wand"></i> {{ __('Generate Content') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">{{ __('Loading...') }}</span>
                </div>
                <h5>{{ __('Generating Content...') }}</h5>
                <p class="text-muted">{{ __('Please wait while AI generates your content. This may take a few moments.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Template selection handler
        $('#template-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const promptTemplate = selectedOption.data('prompt');
            const defaultTone = selectedOption.data('tone');
            const defaultLength = selectedOption.data('length');
            
            if (promptTemplate) {
                $('textarea[name="prompt"]').val(promptTemplate);
            }
            
            if (defaultTone) {
                $('select[name="tone"]').val(defaultTone);
            }
            
            if (defaultLength) {
                $('select[name="length"]').val(defaultLength);
            }
        });

        // Form submission handler
        $('#ai-content-form').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading modal
            $('#loadingModal').modal('show');
            
            // Disable submit button
            $('#generate-btn').prop('disabled', true).html('<i class="ti ti-loader"></i> {{ __("Generating...") }}');

    // Initialize models on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateModels();
    });

    // Template selection functionality
    document.getElementById('template-select').addEventListener('change', function() {
        const templateId = this.value;
        if (templateId) {
            // Here you would typically fetch template details via AJAX
            // For now, we'll just show a placeholder
            console.log('Template selected:', templateId);
        }
    });

    // Content type suggestions
    const contentTypeSuggestions = {
        'blog_post': 'Write an engaging blog post about [topic]. Include an introduction, main points, and conclusion.',
        'article': 'Create an informative article about [topic]. Include research, examples, and actionable insights.',
        'social_media': 'Create a social media post about [topic]. Make it engaging and shareable.',
        'email': 'Write a professional email about [topic]. Include a clear subject and call-to-action.',
        'product_description': 'Write a compelling product description for [product]. Highlight key features and benefits.',
        'ad_copy': 'Create persuasive ad copy for [product/service]. Focus on benefits and include a strong call-to-action.'
    };

    document.getElementById('content-type').addEventListener('change', function() {
        const contentType = this.value;
        const promptTextarea = document.querySelector('textarea[name="prompt"]');
        
        if (contentType && contentTypeSuggestions[contentType]) {
            promptTextarea.placeholder = contentTypeSuggestions[contentType];
        }
    });

    // Form submission with loading
    document.querySelector('form').addEventListener('submit', function() {
        const submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="ti ti-loader-2 animate-spin"></i> {{ __("Generating...") }}';
        submitBtn.disabled = true;
    });
</script>
@endpush
