@extends('layouts.main')

@section('page-title')
    {{ __('SEO Audits') }}
@endsection

@section('page-breadcrumb')
    {{ __('SEO Audits') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="assets">
                        <thead>
                            <tr>
                                <th>{{ __('Website') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Score') }}</th>
                                <th>{{ __('Issues') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audits ?? [] as $audit)
                                <tr>
                                    <td>{{ $audit->website->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($audit->audit_type) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $audit->status === 'completed' ? 'success' : ($audit->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $audit->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($audit->score)
                                            <span class="badge bg-{{ $audit->score_color }}">{{ $audit->score }}/100</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $audit->total_issues_found }}</td>
                                    <td>{{ $audit->duration ? $audit->duration . ' min' : '-' }}</td>
                                    <td>{{ $audit->created_at->format('M d, Y') }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('seo.audits.show', $audit) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{ route('seo.audits.destroy', $audit) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white text-white"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No audits found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
