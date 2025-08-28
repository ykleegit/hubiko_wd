@extends('layouts.main')

@section('page-title')
    {{ __('Company Boards') }}
@endsection

@section('page-breadcrumb')
    {{ __('Company Boards') }}
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
                                <th>{{ __('Company Name') }}</th>
                                <th>{{ __('Registration Number') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Directors') }}</th>
                                <th>{{ __('Meetings') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companies as $company)
                                <tr>
                                    <td>{{ $company->name }}</td>
                                    <td>{{ $company->company_registration_number }}</td>
                                    <td>{{ ucfirst($company->company_type) }}</td>
                                    <td>{{ $company->active_directors ?? 0 }}</td>
                                    <td>{{ $company->upcoming_meetings ?? 0 }}</td>
                                    <td>
                                        <span class="badge bg-{{ $company->is_active ? 'success' : 'danger' }}">
                                            {{ $company->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $company->created_at->format('M d, Y') }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('company-secretary.companies.show', $company) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="{{ route('company-secretary.companies.edit', $company) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{ route('company-secretary.companies.destroy', $company) }}">
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
                                    <td colspan="8" class="text-center">{{ __('No companies found') }}</td>
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
