@extends('layouts.main')

@section('page-title')
    {{ __('E-commerce Stores') }}
@endsection

@section('page-breadcrumb')
    {{ __('E-commerce Stores') }}
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
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Products') }}</th>
                                <th>{{ __('Orders') }}</th>
                                <th>{{ __('Customers') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stores as $store)
                                <tr>
                                    <td>{{ $store->name }}</td>
                                    <td>{{ $store->email ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $store->is_active ? 'success' : 'danger' }}">
                                            {{ $store->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $store->products_count ?? 0 }}</td>
                                    <td>{{ $store->orders_count ?? 0 }}</td>
                                    <td>{{ $store->customers_count ?? 0 }}</td>
                                    <td>{{ $store->created_at->format('M d, Y') }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('ecommerce.stores.show', $store) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="{{ route('ecommerce.stores.edit', $store) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{ route('ecommerce.stores.destroy', $store) }}">
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
                                    <td colspan="8" class="text-center">{{ __('No stores found') }}</td>
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
