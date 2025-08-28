@extends('layouts.main')

@section('page-title')
    {{ __('SEO Websites') }}
@endsection

@section('page-breadcrumb')
    {{ __('SEO Websites') }}
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
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('URL') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Keywords') }}</th>
                                <th>{{ __('Issues') }}</th>
                                <th>{{ __('Last Crawled') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($websites as $website)
                                <tr>
                                    <td>{{ $website->name }}</td>
                                    <td>{{ $website->domain }}</td>
                                    <td>
                                        <span class="badge bg-{{ $website->is_active ? 'success' : 'danger' }}">
                                            {{ $website->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $website->keywords_count ?? 0 }}</td>
                                    <td>{{ $website->total_issues ?? 0 }}</td>
                                    <td>{{ $website->last_crawled_at ? $website->last_crawled_at->format('M d, Y') : 'Never' }}</td>
                                    <td>{{ $website->created_at->format('M d, Y') }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('seo.websites.show', $website) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="{{ route('seo.websites.edit', $website) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="{{ route('seo.websites.crawl', $website) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Crawl') }}">
                                                    <i class="ti ti-refresh text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{ route('seo.websites.destroy', $website) }}">
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
                                    <td colspan="8" class="text-center">{{ __('No websites found') }}</td>
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
