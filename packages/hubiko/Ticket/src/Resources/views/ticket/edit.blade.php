@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Ticket') }} #{{ $ticket->ticket_id }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('packages/workdo/Ticket/Resources/assets/css/ticket.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{ __('Edit Ticket') }}</h5>
                    <div>
                        <a href="{{ route('ticket.index') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <a href="{{ route('ticket.show', $ticket->id) }}" class="btn btn-sm btn-info">
                            <i class="ti ti-eye"></i> {{ __('View') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('ticket.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $ticket->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $ticket->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Mobile Number') }}</label>
                                <input type="text" name="mobile_no" class="form-control @error('mobile_no') is-invalid @enderror" value="{{ old('mobile_no', $ticket->mobile_no) }}">
                                @error('mobile_no')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', $ticket->subject) }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select name="category" class="form-control select @error('category') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category', $ticket->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control select @error('priority') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Priority') }}</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ old('priority', $ticket->priority) == $priority->id ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select @error('status') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Status') }}</option>
                                    @foreach(Hubiko\Ticket\Entities\Ticket::$statues as $status)
                                        <option value="{{ $status }}" {{ old('status', $ticket->status) == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Assign To') }} <span class="text-danger">*</span></label>
                                <select name="agent" class="form-control select @error('agent') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Agent') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('agent', $ticket->is_assign) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description', $ticket->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        @if($ticket->attachments && json_decode($ticket->attachments))
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Current Attachments') }}</label>
                                <div class="row">
                                    @foreach(json_decode($ticket->attachments) as $key => $attachment)
                                        <div class="col-md-2 mb-3">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <a href="{{ asset($attachment) }}" target="_blank" class="attachment-item">
                                                            <i class="ti ti-download"></i> {{ basename($attachment) }}
                                                        </a>
                                                        <a href="{{ route('ticket.attachment.destroy', [$ticket->id, $key]) }}" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this attachment?') }}')">
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                    </div>
                                                    @if(in_array(pathinfo($attachment, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                                        <img src="{{ asset($attachment) }}" class="img-fluid rounded" alt="Attachment">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Add New Attachments') }}</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control @error('attachments.*') is-invalid @enderror" multiple>
                                <div class="ticket-attachments-wrapper mt-2" id="attachmentPreview"></div>
                                <small class="text-muted">{{ __('Max file size 10MB') }}</small>
                                @error('attachments.*')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        @if(!empty($customFields))
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Custom Fields') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($customFields as $field)
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">{{ $field->name }} @if($field->is_required) <span class="text-danger">*</span> @endif</label>
                                                <?php $customFieldValue = \Hubiko\Ticket\Entities\CustomField::getData($ticket, $field->id); ?>
                                                @if($field->type == 'text')
                                                    <input type="text" name="customField[{{ $field->id }}]" class="form-control" value="{{ $customFieldValue }}" @if($field->is_required) required @endif>
                                                @elseif($field->type == 'textarea')
                                                    <textarea name="customField[{{ $field->id }}]" class="form-control" rows="3" @if($field->is_required) required @endif>{{ $customFieldValue }}</textarea>
                                                @elseif($field->type == 'select')
                                                    <select name="customField[{{ $field->id }}]" class="form-control select" @if($field->is_required) required @endif>
                                                        <option value="">{{ __('Select Option') }}</option>
                                                        @foreach(explode(',', $field->options) as $option)
                                                            <option value="{{ $option }}" {{ $customFieldValue == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($field->type == 'checkbox')
                                                    @foreach(explode(',', $field->options) as $option)
                                                        <div class="form-check">
                                                            <input type="checkbox" name="customField[{{ $field->id }}][]" value="{{ $option }}" class="form-check-input" {{ in_array($option, is_array($customFieldValue) ? $customFieldValue : [$customFieldValue]) ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                @elseif($field->type == 'radio')
                                                    @foreach(explode(',', $field->options) as $option)
                                                        <div class="form-check">
                                                            <input type="radio" name="customField[{{ $field->id }}]" value="{{ $option }}" class="form-check-input" {{ $customFieldValue == $option ? 'checked' : '' }} @if($loop->first && $field->is_required) required @endif>
                                                            <label class="form-check-label">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                @elseif($field->type == 'date')
                                                    <input type="date" name="customField[{{ $field->id }}]" class="form-control" value="{{ $customFieldValue }}" @if($field->is_required) required @endif>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-save"></i> {{ __('Update Ticket') }}
                            </button>
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
        // Initialize select2
        $('.select').select2();
        
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