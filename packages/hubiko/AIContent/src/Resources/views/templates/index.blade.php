@extends('layouts.main')

@section('page-title')
    {{ __('AI Templates') }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}, {{ __('Templates') }}
@endsection

@section('page-action')
    @if(Auth::user()->isAbleTo('ai template create'))
        <a href="{{ route('ai-content.templates.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Create Template') }}
        </a>
    @endif
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5>{{ __('AI Templates') }}</h5>
                    </div>
                    <div class="col-auto">
                        <form method="GET" action="{{ route('ai-content.templates.index') }}" class="d-flex gap-2">
                            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">{{ __('All Categories') }}</option>
                                <option value="content" {{ request('category') == 'content' ? 'selected' : '' }}>{{ __('Content') }}</option>
                                <option value="marketing" {{ request('category') == 'marketing' ? 'selected' : '' }}>{{ __('Marketing') }}</option>
                                <option value="social_media" {{ request('category') == 'social_media' ? 'selected' : '' }}>{{ __('Social Media') }}</option>
                                <option value="email" {{ request('category') == 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                            </select>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Template') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Content Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Usage Count') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-{{ $template->is_system ? 'primary' : 'secondary' }}">
                                                    <i class="ti ti-{{ $template->is_system ? 'star' : 'template' }}"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $template->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $template->category)) }}</span>
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $template->content_type)) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                        @if($template->is_system)
                                            <span class="badge bg-primary ms-1">{{ __('System') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->usage_count }}</td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if(Auth::user()->isAbleTo('ai template view'))
                                                    <li><a class="dropdown-item" href="{{ route('ai-content.templates.show', $template) }}"><i class="ti ti-eye"></i> {{ __('View') }}</a></li>
                                                @endif
                                                @if(Auth::user()->isAbleTo('ai template edit') && (!$template->is_system || Auth::user()->type == 'super admin'))
                                                    <li><a class="dropdown-item" href="{{ route('ai-content.templates.edit', $template) }}"><i class="ti ti-pencil"></i> {{ __('Edit') }}</a></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('ai-content.templates.toggle', $template) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="ti ti-{{ $template->is_active ? 'eye-off' : 'eye' }}"></i>
                                                                {{ $template->is_active ? __('Deactivate') : __('Activate') }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if(Auth::user()->isAbleTo('ai template delete') && !$template->is_system)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('ai-content.templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="ti ti-trash"></i> {{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="ti ti-template display-4 text-muted"></i>
                                            <h5 class="mt-3">{{ __('No templates found') }}</h5>
                                            <p class="text-muted">{{ __('Create your first AI template to get started') }}</p>
                                            @if(Auth::user()->isAbleTo('ai template create'))
                                                <a href="{{ route('ai-content.templates.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus"></i> {{ __('Create Template') }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($templates->hasPages())
                <div class="card-footer">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
