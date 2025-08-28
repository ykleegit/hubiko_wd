@extends('layouts.admin')

@section('page-title')
    {{ __('Tickets') }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('packages/workdo/Ticket/Resources/assets/css/ticket.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{ __('Filters') }}</h5>
                    <a data-bs-toggle="collapse" href="#collapseFilters" role="button" aria-expanded="false" aria-controls="collapseFilters" class="btn btn-sm btn-primary">
                        <i class="ti ti-filter"></i> {{ __('Filter') }}
                    </a>
                </div>
            </div>
            <div class="collapse" id="collapseFilters">
                <div class="card-body">
                    <form action="{{ route('ticket.index') }}" method="GET" class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="category" class="form-label">{{ __('Category') }}</label>
                                        <select name="category" id="category" class="form-control select">
                                            <option value="">{{ __('All Categories') }}</option>
                                            @foreach (\Hubiko\Ticket\Entities\Category::where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->get() as $category)
                                                <option value="{{ $category->id }}" {{ isset($_GET['category']) && $_GET['category'] == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">{{ __('Status') }}</label>
                                        <select name="status" id="status" class="form-control select">
                                            <option value="">{{ __('All Statuses') }}</option>
                                            @foreach (\Hubiko\Ticket\Entities\Ticket::$statues as $status)
                                                <option value="{{ $status }}" {{ isset($_GET['status']) && $_GET['status'] == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                        <select name="priority" id="priority" class="form-control select">
                                            <option value="">{{ __('All Priorities') }}</option>
                                            @foreach (\Hubiko\Ticket\Entities\Priority::where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->get() as $priority)
                                                <option value="{{ $priority->id }}" {{ isset($_GET['priority']) && $_GET['priority'] == $priority->id ? 'selected' : '' }}>
                                                    {{ $priority->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="agent" class="form-label">{{ __('Assigned To') }}</label>
                                        <select name="agent" id="agent" class="form-control select">
                                            <option value="">{{ __('All Agents') }}</option>
                                            @foreach (\App\Models\User::where('type', 'agent')->where('created_by', creatorId())->get() as $agent)
                                                <option value="{{ $agent->id }}" {{ isset($_GET['agent']) && $_GET['agent'] == $agent->id ? 'selected' : '' }}>
                                                    {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 mt-4">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search"></i> {{ __('Search') }}
                                </button>
                                <a href="{{ route('ticket.index') }}" class="btn btn-danger">
                                    <i class="ti ti-refresh"></i> {{ __('Reset') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{ __('Tickets List') }}</h5>
                    <div class="d-flex">
                        @can('ticket export')
                            <a href="{{ route('ticket.export') }}" class="btn btn-sm btn-primary me-2">
                                <i class="ti ti-file-export"></i> {{ __('Export') }}
                            </a>
                        @endcan
                        @can('ticket create')
                            <a href="{{ route('ticket.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus"></i> {{ __('Create Ticket') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Ticket ID') }}</th>
                                <th>{{ __('Subject') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Assigned To') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th class="text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr class="ticket-priority-{{ strtolower($ticket->getPriority ? $ticket->getPriority->name : 'low') }}">
                                    <td>
                                        <a href="{{ route('ticket.show', $ticket->id) }}">
                                            {{ $ticket->ticket_id }}
                                        </a>
                                    </td>
                                    <td>{{ $ticket->subject }}</td>
                                    <td>{{ $ticket->name }}</td>
                                    <td>{{ $ticket->email }}</td>
                                    <td>
                                        <span class="badge p-2" style="background-color: {{ $ticket->getCategory ? $ticket->getCategory->color : '#6c757d' }}">
                                            {{ $ticket->getCategory ? $ticket->getCategory->name : '--' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->getAgentDetails)
                                            <span class="badge bg-primary p-2">{{ $ticket->getAgentDetails->name }}</span>
                                        @else
                                            <span class="badge bg-danger p-2">{{ __('Not Assigned') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge p-2 ticket-status-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge p-2" style="background-color: {{ $ticket->getPriority ? $ticket->getPriority->color : '#6c757d' }}">
                                            {{ $ticket->getPriority ? $ticket->getPriority->name : '--' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        @can('ticket edit')
                                            <a href="{{ route('ticket.edit', $ticket->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('ticket show')
                                            <a href="{{ route('ticket.show', $ticket->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        @endcan
                                        @can('ticket delete')
                                            <form method="POST" action="{{ route('ticket.destroy', $ticket->id) }}" style="display:inline;">
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
                            
                            @if(count($tickets) == 0)
                                <tr>
                                    <td colspan="10" class="text-center">{{ __('No tickets found') }}</td>
                                </tr>
                            @endif
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