@extends('layouts.main')

@section('page-title')
    {{ __('Tickets') }}
@endsection

@section('page-breadcrumb')
    - {{ __('Tickets') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{ __('Tickets List') }}</h5>
                    @can('ticket create')
                        <div class="text-end">
                            <a href="{{ route('ticket.create') }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-plus"></i> {{ __('Create Ticket') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets ?? [] as $ticket)
                                <tr>
                                    <td>{{ $ticket->id }}</td>
                                    <td>{{ $ticket->title }}</td>
                                    <td>{{ $ticket->priority->name ?? '--' }}</td>
                                    <td>{{ $ticket->category->name ?? '--' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->status_color }}">
                                            {{ $ticket->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ \App\Models\Utility::getDateFormated($ticket->created_at) }}</td>
                                    <td class="action">
                                        @can('ticket show')
                                            <a href="{{ route('ticket.show', $ticket->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        @endcan
                                        @can('ticket edit')
                                            <a href="{{ route('ticket.edit', $ticket->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('ticket delete')
                                            <form action="{{ route('ticket.destroy', $ticket->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.show_confirm').click(function(e) {
            if(!confirm('{{ __("Are you sure you want to delete this ticket?") }}')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush 