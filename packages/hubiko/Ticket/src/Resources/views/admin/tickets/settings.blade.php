@extends('layouts.main')

@section('page-title')
    {{ __('Ticket Settings') }}
@endsection

@section('page-breadcrumb')
    - {{ __('Ticket Settings') }}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Ticket Settings') }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('ticket.settings.store') }}">
            @csrf
            <input type="hidden" name="workspace" value="{{ getActiveWorkSpace() }}">
            <input type="hidden" name="created_by" value="{{ creatorId() }}">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Allow Customer Tickets') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="allow_customer_tickets" id="allow_customer_tickets"
                                {{ isset($settings['allow_customer_tickets']) && $settings['allow_customer_tickets'] == 'on' ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_customer_tickets"></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Allow File Uploads') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="allow_file_uploads" id="allow_file_uploads"
                                {{ isset($settings['allow_file_uploads']) && $settings['allow_file_uploads'] == 'on' ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_file_uploads"></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Max File Size (MB)') }}</label>
                        <input type="number" class="form-control" name="max_file_size" value="{{ isset($settings['max_file_size']) ? $settings['max_file_size'] : '10' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Allowed File Types') }}</label>
                        <input type="text" class="form-control" name="allowed_file_types" value="{{ isset($settings['allowed_file_types']) ? $settings['allowed_file_types'] : 'jpg,jpeg,png,gif,pdf,doc,docx,zip' }}">
                        <small class="form-text text-muted">{{ __('Comma separated file extensions') }}</small>
                    </div>
                </div>
            </div>
            
            <h5 class="mt-4">{{ __('Notification Settings') }}</h5>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Notify Admin on New Ticket') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="notify_admin_on_new_ticket" id="notify_admin_on_new_ticket"
                                {{ isset($settings['notify_admin_on_new_ticket']) && $settings['notify_admin_on_new_ticket'] == 'on' ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_admin_on_new_ticket"></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Notify Customer on Ticket Status Change') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="notify_customer_on_ticket_status_change" id="notify_customer_on_ticket_status_change"
                                {{ isset($settings['notify_customer_on_ticket_status_change']) && $settings['notify_customer_on_ticket_status_change'] == 'on' ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_customer_on_ticket_status_change"></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Notify Agent on Ticket Assignment') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="notify_agent_on_ticket_assignment" id="notify_agent_on_ticket_assignment"
                                {{ isset($settings['notify_agent_on_ticket_assignment']) && $settings['notify_agent_on_ticket_assignment'] == 'on' ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_agent_on_ticket_assignment"></label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection 