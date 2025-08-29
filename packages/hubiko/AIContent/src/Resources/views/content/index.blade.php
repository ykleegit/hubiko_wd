@extends('layouts.main')

@section('page-title')
    {{ __('AI Content') }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a href="{{ route('ai-content.content.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Generate Content') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-lg-9 col-md-9 col-sm-12">
                        <h5>{{ __('AI Generated Content') }}</h5>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 justify-content-end d-flex">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ti ti-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card-body border-bottom">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-select filter-select" data-filter="status">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="content_type" class="form-select filter-select" data-filter="content_type">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach($contentTypes as $type)
                                <option value="{{ $type }}" {{ request('content_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="content-table">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Template') }}</th>
                                <th>{{ __('Words') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th>{{ __('Creator') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contents as $content)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ Str::limit($content->title, 40) }}</h6>
                                                <small class="text-muted">{{ Str::limit(strip_tags($content->generated_content), 60) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $content->content_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $content->status === 'published' ? 'success' : ($content->status === 'draft' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($content->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $content->template->name ?? __('Custom') }}</td>
                                    <td>{{ $content->word_count }}</td>
                                    <td>{{ $content->created_at->format('M d, Y') }}</td>
                                    <td>{{ $content->creator->name ?? 'N/A' }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('ai-content.content.show', $content) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @if(Auth::user()->isAbleTo('ai content edit'))
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route('ai-content.content.edit', $content) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                @if($content->status === 'draft')
                                                    <div class="action-btn bg-success ms-2">
                                                        <form method="POST" action="{{ route('ai-content.content.publish', $content) }}" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Publish') }}">
                                                                <i class="ti ti-check text-white"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endif
                                            @if(Auth::user()->isAbleTo('ai content delete'))
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{ route('ai-content.content.destroy', $content) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No content found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($contents->hasPages())
                <div class="card-footer">
                    {{ $contents->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.filter-select').on('change', function() {
            const filterName = $(this).data('filter');
            const filterValue = $(this).val();
            
            const url = new URL(window.location);
            if (filterValue) {
                url.searchParams.set(filterName, filterValue);
            } else {
                url.searchParams.delete(filterName);
            }
            
            window.location.href = url.toString();
        });
    });
</script>
@endpush
