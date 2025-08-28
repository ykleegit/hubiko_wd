@extends('layouts.main')

@section('page-title')
    {{ __('Add-on Management') }}
@endsection

@section('page-breadcrumb')
    {{ __('Add-on Management') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top" style="top:30px">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        <a href="#addons-overview" class="list-group-item list-group-item-action border-0">
                            {{ __('Overview') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#installed-addons" class="list-group-item list-group-item-action border-0">
                            {{ __('Installed Add-ons') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#available-addons" class="list-group-item list-group-item-action border-0">
                            {{ __('Available Add-ons') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-9">
                <div id="addons-overview" class="card">
                    <div class="card-header">
                        <h5>{{ __('Self-Built Add-on System') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary">
                                    <div class="card-body">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-auto mb-3 mb-sm-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="theme-avtar bg-white">
                                                        <i class="ti ti-package text-primary"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <small class="text-white">{{ __('Total Add-ons') }}</small>
                                                        <h6 class="m-0 text-white">{{ count($addons) }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success">
                                    <div class="card-body">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-auto mb-3 mb-sm-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="theme-avtar bg-white">
                                                        <i class="ti ti-check text-success"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <small class="text-white">{{ __('Installed') }}</small>
                                                        <h6 class="m-0 text-white">{{ collect($addons)->where('installed', true)->count() }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info">
                                    <div class="card-body">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-auto mb-3 mb-sm-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="theme-avtar bg-white">
                                                        <i class="ti ti-power text-info"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <small class="text-white">{{ __('Enabled') }}</small>
                                                        <h6 class="m-0 text-white">{{ collect($addons)->where('enabled', true)->count() }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="available-addons" class="card">
                    <div class="card-header">
                        <h5>{{ __('Available Add-ons') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($addons as $name => $addon)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card addon-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1">{{ $addon['alias'] ?? $addon['name'] }}</h6>
                                                <small class="text-muted">v{{ $addon['version'] ?? '1.0' }}</small>
                                            </div>
                                            <div class="addon-status">
                                                @if($addon['installed'])
                                                    @if($addon['enabled'])
                                                        <span class="badge bg-success">{{ __('Enabled') }}</span>
                                                    @else
                                                        <span class="badge bg-warning">{{ __('Disabled') }}</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Not Installed') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <p class="text-muted small mb-3">
                                            {{ $addon['description'] ?? 'Self-built add-on module' }}
                                        </p>
                                        
                                        @if(isset($addon['features']) && is_array($addon['features']))
                                        <div class="mb-3">
                                            <small class="text-muted">{{ __('Features:') }}</small>
                                            <ul class="list-unstyled small">
                                                @foreach(array_slice($addon['features'], 0, 3) as $feature)
                                                <li><i class="ti ti-check text-success me-1"></i>{{ $feature }}</li>
                                                @endforeach
                                                @if(count($addon['features']) > 3)
                                                <li class="text-muted">{{ __('and') }} {{ count($addon['features']) - 3 }} {{ __('more...') }}</li>
                                                @endif
                                            </ul>
                                        </div>
                                        @endif
                                        
                                        <div class="addon-actions">
                                            @if($addon['installed'])
                                                @if($addon['enabled'])
                                                    <button class="btn btn-sm btn-warning addon-action" 
                                                            data-action="disable" 
                                                            data-addon="{{ $name }}">
                                                        {{ __('Disable') }}
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-success addon-action" 
                                                            data-action="enable" 
                                                            data-addon="{{ $name }}">
                                                        {{ __('Enable') }}
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-danger addon-action" 
                                                        data-action="uninstall" 
                                                        data-addon="{{ $name }}">
                                                    {{ __('Uninstall') }}
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-primary addon-action" 
                                                        data-action="install" 
                                                        data-addon="{{ $name }}">
                                                    {{ __('Install') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionButtons = document.querySelectorAll('.addon-action');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const addon = this.dataset.addon;
            
            if (confirm(`Are you sure you want to ${action} the ${addon} add-on?`)) {
                performAddonAction(action, addon, this);
            }
        });
    });
    
    function performAddonAction(action, addon, button) {
        const originalText = button.textContent;
        button.textContent = 'Processing...';
        button.disabled = true;
        
        fetch(`/addons/${addon}/${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to update the UI
            } else {
                alert('Error: ' + data.message);
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
            button.textContent = originalText;
            button.disabled = false;
        });
    }
});
</script>
@endsection
