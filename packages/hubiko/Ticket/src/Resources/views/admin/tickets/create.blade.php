@extends('layouts.main')

@section('page-title')
    {{ __('Create Ticket') }}
@endsection

@section('page-breadcrumb')
    - <a href="{{ route('ticket.index') }}">{{ __('Tickets') }}</a>
    - {{ __('Create') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Create New Ticket') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ticket.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="workspace" value="{{ getActiveWorkSpace() }}">
                    <input type="hidden" name="created_by" value="{{ creatorId() }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="open">{{ __('Open') }}</option>
                                    <option value="in_progress">{{ __('In Progress') }}</option>
                                    <option value="on_hold">{{ __('On Hold') }}</option>
                                    <option value="closed">{{ __('Closed') }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Attachments') }}</label>
                                <input type="file" name="attachments[]" class="form-control" multiple>
                                <small class="text-muted">{{ __('Max file size 10MB. Allowed file types: pdf, doc, docx, xls, xlsx, jpg, jpeg, png') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                            <a href="{{ route('ticket.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 