@extends('layouts.admin')

@section('page-title')
    {{ __('Ticket Details') }} #{{ $ticket->ticket_id }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('packages/workdo/Ticket/Resources/assets/css/ticket.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ticket-card ticket-priority-{{ strtolower($ticket->getPriority ? $ticket->getPriority->name : 'low') }}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{ __('Ticket Information') }}</h5>
                    <div>
                        <a href="{{ route('ticket.index') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                        </a>
                        @can('ticket edit')
                            <a href="{{ route('ticket.edit', $ticket->id) }}" class="btn btn-sm btn-info">
                                <i class="ti ti-pencil"></i> {{ __('Edit') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>{{ __('Ticket ID') }}</th>
                                        <td>{{ $ticket->ticket_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Subject') }}</th>
                                        <td>{{ $ticket->subject }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <td>{{ $ticket->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Email') }}</th>
                                        <td>{{ $ticket->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Mobile Number') }}</th>
                                        <td>{{ $ticket->mobile_no ?: '--' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>
                                            <span class="badge p-2 ticket-status-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                                {{ $ticket->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Priority') }}</th>
                                        <td>
                                            <span class="badge p-2" style="background-color: {{ $ticket->getPriority ? $ticket->getPriority->color : '#6c757d' }}">
                                                {{ $ticket->getPriority ? $ticket->getPriority->name : '--' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Category') }}</th>
                                        <td>
                                            <span class="badge p-2" style="background-color: {{ $ticket->getCategory ? $ticket->getCategory->color : '#6c757d' }}">
                                                {{ $ticket->getCategory ? $ticket->getCategory->name : '--' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Assigned To') }}</th>
                                        <td>
                                            @if($ticket->getAgentDetails)
                                                <span class="badge bg-primary p-2">{{ $ticket->getAgentDetails->name }}</span>
                                            @else
                                                <span class="badge bg-danger p-2">{{ __('Not Assigned') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Created At') }}</th>
                                        <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>{{ __('Description') }}</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! $ticket->description !!}
                            </div>
                        </div>
                    </div>
                </div>

                @if($ticket->attachments && json_decode($ticket->attachments))
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>{{ __('Attachments') }}</h5>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @foreach(json_decode($ticket->attachments) as $attachment)
                                        <div class="col-md-2 mb-3">
                                            <a href="{{ asset($attachment) }}" target="_blank" class="attachment-item">
                                                <i class="ti ti-download"></i> {{ basename($attachment) }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(count($ticket->conversions) > 0)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>{{ __('Conversations') }}</h5>
                        <div class="ticket-conversation">
                            @foreach($ticket->conversions as $conversion)
                                <div class="conversation-item {{ $conversion->sender && $conversion->sender->id == $ticket->created_by ? 'customer' : 'agent' }}">
                                    <div class="conversation-header">
                                        <div>
                                            <strong>{{ $conversion->sender ? $conversion->sender->name : 'System' }}</strong>
                                        </div>
                                        <div>
                                            {{ \Carbon\Carbon::parse($conversion->created_at)->format('Y-m-d H:i:s') }}
                                        </div>
                                    </div>
                                    <div class="conversation-content">
                                        {!! $conversion->description !!}
                                    </div>
                                    @if($conversion->attachments && json_decode($conversion->attachments))
                                        <div class="conversation-attachments">
                                            @foreach(json_decode($conversion->attachments) as $attachment)
                                                <a href="{{ asset($attachment) }}" target="_blank" class="attachment-item">
                                                    <i class="ti ti-download"></i> {{ basename($attachment) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @can('ticket reply')
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Reply to Ticket') }}</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('ticket.direct.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="reply-form">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">{{ __('Status') }}</label>
                                        <select name="status" id="status" class="form-control">
                                            @foreach(\Hubiko\Ticket\Entities\Ticket::$statues as $status)
                                                <option value="{{ $status }}" {{ $ticket->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="description" class="form-label">{{ __('Message') }}</label>
                                        <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="attachments" class="form-label">{{ __('Attachments') }}</label>
                                        <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                                        <div class="ticket-attachments-wrapper mt-2" id="attachmentPreview"></div>
                                    </div>
                                    <div class="form-group text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-send"></i> {{ __('Send Reply') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview attachments
        $('#attachments').on('change', function() {
            var files = $(this)[0].files;
            $('#attachmentPreview').html('');
            
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var preview = 
                        '<div class="ticket-attachment-preview">' +
                            '<img src="' + e.target.result + '" alt="Attachment">' +
                            '<span class="remove-attachment" data-index="' + i + '">×</span>' +
                        '</div>';
                    
                    $('#attachmentPreview').append(preview);
                }
                
                reader.readAsDataURL(file);
            }
        });
        
        // Remove attachment
        $(document).on('click', '.remove-attachment', function() {
            $(this).parent().remove();
        });
    });
</script>
@endpush 