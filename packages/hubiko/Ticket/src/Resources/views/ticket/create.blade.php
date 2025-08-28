@extends('layouts.admin')

@section('page-title')
    {{ __('Create Ticket') }}
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
                    <h5>{{ __('Create New Ticket') }}</h5>
                    <a href="{{ route('ticket.index') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('ticket.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
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
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
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
                                <input type="text" name="mobile_no" class="form-control @error('mobile_no') is-invalid @enderror" value="{{ old('mobile_no') }}">
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
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
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
                                        <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>
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
                                        <option value="{{ $priority->id }}" {{ old('priority') == $priority->id ? 'selected' : '' }}>
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
                                        <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
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
                                        <option value="{{ $user->id }}" {{ old('agent') == $user->id ? 'selected' : '' }}>
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
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Attachments') }}</label>
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
                                                @if($field->type == 'text')
                                                    <input type="text" name="customField[{{ $field->id }}]" class="form-control" @if($field->is_required) required @endif>
                                                @elseif($field->type == 'textarea')
                                                    <textarea name="customField[{{ $field->id }}]" class="form-control" rows="3" @if($field->is_required) required @endif></textarea>
                                                @elseif($field->type == 'select')
                                                    <select name="customField[{{ $field->id }}]" class="form-control select" @if($field->is_required) required @endif>
                                                        <option value="">{{ __('Select Option') }}</option>
                                                        @foreach(explode(',', $field->options) as $option)
                                                            <option value="{{ $option }}">{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($field->type == 'checkbox')
                                                    @foreach(explode(',', $field->options) as $option)
                                                        <div class="form-check">
                                                            <input type="checkbox" name="customField[{{ $field->id }}][]" value="{{ $option }}" class="form-check-input">
                                                            <label class="form-check-label">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                @elseif($field->type == 'radio')
                                                    @foreach(explode(',', $field->options) as $option)
                                                        <div class="form-check">
                                                            <input type="radio" name="customField[{{ $field->id }}]" value="{{ $option }}" class="form-check-input" @if($loop->first && $field->is_required) required @endif>
                                                            <label class="form-check-label">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                @elseif($field->type == 'date')
                                                    <input type="date" name="customField[{{ $field->id }}]" class="form-control" @if($field->is_required) required @endif>
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
                                <i class="ti ti-plus"></i> {{ __('Create Ticket') }}
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