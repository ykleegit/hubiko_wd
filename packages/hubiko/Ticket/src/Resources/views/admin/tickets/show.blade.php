@extends('layouts.main')

@section('page-title')
    {{ __('Ticket Details') }}
@endsection

@section('page-breadcrumb')
    - <a href="{{ route('ticket.index') }}">{{ __('Tickets') }}</a>
    - {{ __('View') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{ __('Ticket Details') }}</h5>
                    <div>
                        @can('ticket edit')
                            <a href="{{ route('ticket.edit', $ticket->id) }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-pencil"></i> {{ __('Edit') }}
                            </a>
                        @endcan
                        <a href="{{ route('ticket.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th>{{ __('Ticket ID') }}</th>
                                        <td>{{ $ticket->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <td>{{ $ticket->title }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>
                                            <span class="badge bg-{{ $ticket->status_color }}">
                                                {{ $ticket->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th>{{ __('Category') }}</th>
                                        <td>{{ $ticket->category->name ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Priority') }}</th>
                                        <td>{{ $ticket->priority->name ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Created At') }}</th>
                                        <td>{{ \App\Models\Utility::getDateFormated($ticket->created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>{{ __('Description') }}</h5>
                        <div class="border p-3 rounded">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>
                </div>
                
                @if($ticket->attachments && count($ticket->attachments) > 0)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>{{ __('Attachments') }}</h5>
                        <div class="d-flex flex-wrap">
                            @foreach($ticket->attachments as $attachment)
                            <div class="attachment-item me-3 mb-2">
                                <div class="card p-2">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <i class="ti ti-file"></i> {{ $attachment->file_name }}
                                        </div>
                                        <div class="ms-2">
                                            <a href="{{ asset($attachment->file_path) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            @can('ticket edit')
                                            <a href="{{ route('ticket.attachment.delete', $attachment->id) }}" class="btn btn-sm btn-danger delete-attachment">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Ticket Conversations -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Conversations') }}</h5>
            </div>
            <div class="card-body">
                @if(count($ticket->conversations ?? []) > 0)
                    <div class="ticket-conversations">
                        @foreach($ticket->conversations as $conversation)
                            <div class="conversation-item mb-4 {{ $conversation->is_client ? 'client-message' : 'staff-message' }}">
                                <div class="card p-3 {{ $conversation->is_client ? 'bg-light' : 'bg-light-primary' }}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $conversation->user ? $conversation->user->name : __('System') }}</strong>
                                            <span class="text-muted ms-2">{{ \App\Models\Utility::getDateFormated($conversation->created_at, true) }}</span>
                                        </div>
                                        @can('ticket delete')
                                            <form action="{{ route('ticket.conversation.destroy', $conversation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger delete-conversation">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                    <div class="mt-2">
                                        {!! nl2br(e($conversation->message)) !!}
                                    </div>
                                    @if($conversation->attachments && count($conversation->attachments) > 0)
                                        <div class="mt-2">
                                            <strong>{{ __('Attachments') }}:</strong>
                                            <div class="d-flex flex-wrap mt-2">
                                                @foreach($conversation->attachments as $attachment)
                                                    <div class="attachment-item me-3 mb-2">
                                                        <div class="card p-2">
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <i class="ti ti-file"></i> {{ $attachment->file_name }}
                                                                </div>
                                                                <div class="ms-2">
                                                                    <a href="{{ asset($attachment->file_path) }}" class="btn btn-sm btn-info" target="_blank">
                                                                        <i class="ti ti-eye"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <p>{{ __('No conversations found') }}</p>
                    </div>
                @endif
                
                @if($ticket->status != 'closed')
                    <div class="add-conversation mt-4">
                        <h5>{{ __('Reply') }}</h5>
                        <form action="{{ route('ticket.conversation.store', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="workspace" value="{{ getActiveWorkSpace() }}">
                            <input type="hidden" name="created_by" value="{{ creatorId() }}">
                            
                            <div class="form-group mb-3">
                                <textarea name="message" class="form-control" rows="4" required></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Attachments') }}</label>
                                <input type="file" name="attachments[]" class="form-control" multiple>
                                <small class="text-muted">{{ __('Max file size 10MB. Allowed file types: pdf, doc, docx, xls, xlsx, jpg, jpeg, png') }}</small>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Send Reply') }}</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.delete-attachment, .delete-conversation').click(function(e) {
            if(!confirm('{{ __("Are you sure you want to delete this?") }}')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush 