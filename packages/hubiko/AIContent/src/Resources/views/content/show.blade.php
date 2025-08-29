@extends('layouts.main')

@section('page-title')
    {{ $content->title }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}, {{ __('View') }}
@endsection

@section('page-action')
    <div class="d-flex">
        @if(Auth::user()->isAbleTo('ai content edit'))
            <a href="{{ route('ai-content.content.edit', $content) }}" class="btn btn-sm btn-primary me-2">
                <i class="ti ti-pencil"></i> {{ __('Edit') }}
            </a>
            <form method="POST" action="{{ route('ai-content.content.regenerate', $content) }}" class="me-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-refresh"></i> {{ __('Regenerate') }}
                </button>
            </form>
            @if($content->status === 'draft')
                <form method="POST" action="{{ route('ai-content.content.publish', $content) }}" class="me-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="ti ti-check"></i> {{ __('Publish') }}
                    </button>
                </form>
            @endif
        @endif
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="ti ti-download"></i> {{ __('Export') }}
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportContent('pdf')"><i class="ti ti-file-type-pdf"></i> {{ __('PDF') }}</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportContent('docx')"><i class="ti ti-file-type-docx"></i> {{ __('Word') }}</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportContent('txt')"><i class="ti ti-file-type-txt"></i> {{ __('Text') }}</a></li>
                <li><a class="dropdown-item" href="#" onclick="copyToClipboard()"><i class="ti ti-copy"></i> {{ __('Copy') }}</a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $content->title }}</h5>
                    <span class="badge bg-{{ $content->status === 'published' ? 'success' : ($content->status === 'draft' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($content->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="content-display" id="content-display">
                    {!! $content->formatted_content !!}
                </div>
            </div>
        </div>

        <!-- Usage History -->
        @if($content->usages->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5>{{ __('Generation History') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Tokens') }}</th>
                                    <th>{{ __('Cost') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($content->usages as $usage)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $usage->success ? 'success' : 'danger' }}">
                                                {{ ucfirst($usage->action_type) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($usage->tokens_consumed) }}</td>
                                        <td>{{ $usage->formatted_cost }}</td>
                                        <td>{{ $usage->formatted_response_time }}</td>
                                        <td>{{ $usage->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Content Details -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Content Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">{{ __('Type') }}</small>
                        <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $content->content_type)) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ __('Language') }}</small>
                        <div class="fw-bold">{{ strtoupper($content->language) }}</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">{{ __('Tone') }}</small>
                        <div class="fw-bold">{{ ucfirst($content->tone) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ __('Length') }}</small>
                        <div class="fw-bold">{{ ucfirst($content->length) }}</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">{{ __('Word Count') }}</small>
                        <div class="fw-bold">{{ number_format($content->word_count) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ __('Reading Time') }}</small>
                        <div class="fw-bold">{{ $content->reading_time }} min</div>
                    </div>
                </div>

                @if($content->keywords)
                    <div class="mb-3">
                        <small class="text-muted">{{ __('Keywords') }}</small>
                        <div class="mt-1">
                            @foreach($content->keywords as $keyword)
                                <span class="badge bg-light text-dark me-1">{{ $keyword }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($content->template)
                    <div class="mb-3">
                        <small class="text-muted">{{ __('Template') }}</small>
                        <div class="fw-bold">{{ $content->template->name }}</div>
                    </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted">{{ __('Created By') }}</small>
                    <div class="fw-bold">{{ $content->creator->name ?? 'N/A' }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted">{{ __('Created At') }}</small>
                    <div class="fw-bold">{{ $content->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- AI Generation Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>{{ __('Generation Stats') }}</h5>
            </div>
            <div class="card-body">
                @if($content->ai_model)
                    <div class="mb-3">
                        <small class="text-muted">{{ __('AI Model') }}</small>
                        <div class="fw-bold">{{ $content->ai_model }}</div>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">{{ __('Tokens Used') }}</small>
                        <div class="fw-bold">{{ number_format($content->tokens_used) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ __('Generation Time') }}</small>
                        <div class="fw-bold">{{ $content->generation_time }}s</div>
                    </div>
                </div>

                @if($content->quality_score)
                    <div class="mb-3">
                        <small class="text-muted">{{ __('Quality Score') }}</small>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-{{ $content->quality_score >= 80 ? 'success' : ($content->quality_score >= 60 ? 'warning' : 'danger') }}" 
                                 style="width: {{ $content->quality_score }}%">
                                {{ $content->quality_score }}%
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Original Prompt -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>{{ __('Original Prompt') }}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $content->prompt }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard() {
        const content = document.getElementById('content-display').innerText;
        navigator.clipboard.writeText(content).then(function() {
            toastrs('Success', '{{ __("Content copied to clipboard!") }}', 'success');
        }, function(err) {
            toastrs('Error', '{{ __("Failed to copy content") }}', 'error');
        });
    }

    function exportContent(format) {
        // This would typically make an AJAX request to export the content
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("ai-content.content.show", $content) }}/export';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const formatInput = document.createElement('input');
        formatInput.type = 'hidden';
        formatInput.name = 'format';
        formatInput.value = format;
        
        form.appendChild(csrfToken);
        form.appendChild(formatInput);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endpush
