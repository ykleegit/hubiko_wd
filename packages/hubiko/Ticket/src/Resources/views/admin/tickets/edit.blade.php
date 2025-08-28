@extends('layouts.main')

@section('page-title')
    {{ __('Edit Ticket') }}
@endsection

@section('page-breadcrumb')
    - <a href="{{ route('ticket.index') }}">{{ __('Tickets') }}</a>
    - {{ __('Edit') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Edit Ticket') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ticket.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="workspace" value="{{ getActiveWorkSpace() }}">
                    <input type="hidden" name="created_by" value="{{ creatorId() }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $ticket->title }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ $ticket->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select name="priority_id" class="form-control" required>
                                    <option value="">{{ __('Select Priority') }}</option>
                                    @foreach($priorities ?? [] as $priority)
                                        <option value="{{ $priority->id }}" {{ $ticket->priority_id == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                    <option value="on_hold" {{ $ticket->status == 'on_hold' ? 'selected' : '' }}>{{ __('On Hold') }}</option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ $ticket->description }}</textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Attachments') }}</label>
                                <input type="file" name="attachments[]" class="form-control" multiple>
                                <small class="text-muted">{{ __('Max file size 10MB. Allowed file types: pdf, doc, docx, xls, xlsx, jpg, jpeg, png') }}</small>
                            </div>
                        </div>
                        
                        @if($ticket->attachments && count($ticket->attachments) > 0)
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Current Attachments') }}</label>
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
                                                <a href="{{ route('ticket.attachment.delete', $attachment->id) }}" class="btn btn-sm btn-danger delete-attachment">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                            <a href="{{ route('ticket.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.delete-attachment').click(function(e) {
            if(!confirm('{{ __("Are you sure you want to delete this attachment?") }}')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush 