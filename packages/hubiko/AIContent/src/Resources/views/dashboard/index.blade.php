@extends('layouts.main')

@section('page-title')
    {{ __('AI Content Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('AI Content') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a href="{{ route('ai-content.content.create') }}" class="btn btn-sm btn-primary me-2">
            <i class="ti ti-plus"></i> {{ __('Generate Content') }}
        </a>
        <a href="{{ route('ai-content.ai-templates.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="ti ti-template"></i> {{ __('Templates') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="theme-avtar bg-primary">
                        <i class="ti ti-file-text"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted">{{ __('Total Content') }}</small>
                        <h6 class="m-0">{{ $stats['total_content'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="theme-avtar bg-success">
                        <i class="ti ti-check"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted">{{ __('Published') }}</small>
                        <h6 class="m-0">{{ $stats['published_content'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="theme-avtar bg-warning">
                        <i class="ti ti-edit"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted">{{ __('Drafts') }}</small>
                        <h6 class="m-0">{{ $stats['draft_content'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="theme-avtar bg-info">
                        <i class="ti ti-template"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted">{{ __('Templates') }}</small>
                        <h6 class="m-0">{{ $stats['total_templates'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Usage Analytics Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Usage Analytics') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Content Type Distribution -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Content Types') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="contentTypeChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Content -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Recent Content') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentContent as $content)
                                <tr>
                                    <td>{{ $content->title }}</td>
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
                                    <td>{{ $content->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('ai-content.content.show', $content) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No content found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Templates -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Popular Templates') }}</h5>
            </div>
            <div class="card-body">
                @forelse($popularTemplates as $template)
                    <div class="d-flex align-items-center mb-3">
                        <div class="theme-avtar bg-light-primary">
                            <i class="ti ti-template"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-0">{{ $template->name }}</h6>
                            <small class="text-muted">{{ $template->contents_count }} {{ __('uses') }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">{{ __('No templates found') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-primary">{{ number_format($stats['total_tokens_used']) }}</h4>
                <small class="text-muted">{{ __('Tokens Used') }}</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-success">${{ number_format($stats['total_cost'], 4) }}</h4>
                <small class="text-muted">{{ __('Total Cost') }}</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-info">{{ $stats['content_this_month'] }}</h4>
                <small class="text-muted">{{ __('This Month') }}</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-warning">{{ $stats['avg_generation_time'] }}s</h4>
                <small class="text-muted">{{ __('Avg Generation Time') }}</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Usage Analytics Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    fetch('{{ route("ai-content.usage.chart") }}?days=30')
        .then(response => response.json())
        .then(data => {
            new Chart(usageCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Content Generated',
                        data: data.usage_count,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

    // Content Type Distribution Chart
    const contentTypeCtx = document.getElementById('contentTypeChart').getContext('2d');
    fetch('{{ route("ai-content.content-type.data") }}')
        .then(response => response.json())
        .then(data => {
            new Chart(contentTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: data.labels.map(label => label.replace('_', ' ').toUpperCase()),
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
</script>
@endpush
