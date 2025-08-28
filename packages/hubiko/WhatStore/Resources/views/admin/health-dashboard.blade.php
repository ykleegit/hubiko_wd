@extends('layouts.admin')

@section('page-title')
    {{ __('WhatsApp Store Health Dashboard') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('System Health Overview') }}</h5>
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-primary refresh-data">
                            <i class="ti ti-refresh"></i> {{ __('Refresh') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="system-status me-3" id="overall-status-icon">
                                            <i class="ti ti-circle-check text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div>
                                            <span class="d-block h6 mb-0">{{ __('Overall Status') }}</span>
                                            <span class="text-sm text-muted" id="overall-status">{{ __('Checking...') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="system-status me-3" id="database-status-icon">
                                            <i class="ti ti-circle-check text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div>
                                            <span class="d-block h6 mb-0">{{ __('Database') }}</span>
                                            <span class="text-sm text-muted" id="database-status">{{ __('Checking...') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="system-status me-3" id="whatsapp-status-icon">
                                            <i class="ti ti-circle-check text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div>
                                            <span class="d-block h6 mb-0">{{ __('WhatsApp API') }}</span>
                                            <span class="text-sm text-muted" id="whatsapp-status">{{ __('Checking...') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="system-status me-3" id="payment-status-icon">
                                            <i class="ti ti-circle-check text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div>
                                            <span class="d-block h6 mb-0">{{ __('Payment Gateways') }}</span>
                                            <span class="text-sm text-muted" id="payment-status">{{ __('Checking...') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('System Information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">{{ __('Module Version') }}</td>
                                                <td id="module-version">1.0.0</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">{{ __('Last Checked') }}</td>
                                                <td id="last-checked">{{ __('Checking...') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">{{ __('Cache Driver') }}</td>
                                                <td id="cache-driver">{{ __('Checking...') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">{{ __('Database Response Time') }}</td>
                                                <td id="db-response-time">{{ __('Checking...') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Payment Gateways Status') }}</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Gateway') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Configured') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payment-gateways-table">
                                            <tr>
                                                <td colspan="3" class="text-center">{{ __('Loading gateway status...') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Recent System Logs') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Timestamp') }}</th>
                                                    <th>{{ __('Level') }}</th>
                                                    <th>{{ __('Message') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="system-logs">
                                                <tr>
                                                    <td colspan="3" class="text-center">{{ __('Loading logs...') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        // Load health data when page loads
        loadHealthData();
        
        // Refresh data when refresh button is clicked
        $('.refresh-data').on('click', function() {
            loadHealthData();
        });
        
        // Set up refresh interval (every 5 minutes)
        setInterval(loadHealthData, 300000);
        
        // Function to load health data from API
        function loadHealthData() {
            // Update last checked time
            $('#last-checked').text(new Date().toLocaleString());
            
            // Load overall health data
            $.ajax({
                url: '{{ route("api.whatstore.health") }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    updateOverallStatus(data);
                    updateSystemInfo(data);
                },
                error: function() {
                    setStatusError('overall');
                    $('#overall-status').text('{{ __("Could not connect to health service") }}');
                }
            });
            
            // Load database health data
            $.ajax({
                url: '{{ route("api.whatstore.health.database") }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    updateDatabaseStatus(data);
                },
                error: function() {
                    setStatusError('database');
                    $('#database-status').text('{{ __("Could not check database") }}');
                }
            });
            
            // Load WhatsApp API health data
            $.ajax({
                url: '{{ route("api.whatstore.health.whatsapp") }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    updateWhatsAppStatus(data);
                },
                error: function() {
                    setStatusError('whatsapp');
                    $('#whatsapp-status').text('{{ __("Could not check WhatsApp API") }}');
                }
            });
            
            // Load payment gateways health data
            $.ajax({
                url: '{{ route("api.whatstore.health.payment-gateways") }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    updatePaymentStatus(data);
                },
                error: function() {
                    setStatusError('payment');
                    $('#payment-status').text('{{ __("Could not check payment gateways") }}');
                }
            });
            
            // Load system logs (would be implemented in a real system)
            loadSystemLogs();
        }
        
        // Function to update overall status
        function updateOverallStatus(data) {
            if (data.status === 'ok') {
                setStatusOk('overall');
                $('#overall-status').text('{{ __("All systems operational") }}');
            } else {
                setStatusError('overall');
                $('#overall-status').text('{{ __("System issues detected") }}');
            }
        }
        
        // Function to update database status
        function updateDatabaseStatus(data) {
            if (data.status === 'ok') {
                setStatusOk('database');
                $('#database-status').text('{{ __("Connected") }}');
                $('#db-response-time').text(data.response_time_ms + ' ms');
            } else {
                setStatusError('database');
                $('#database-status').text('{{ __("Connection error") }}');
                $('#db-response-time').text('{{ __("N/A") }}');
            }
        }
        
        // Function to update WhatsApp API status
        function updateWhatsAppStatus(data) {
            if (data.status === 'ok') {
                setStatusOk('whatsapp');
                $('#whatsapp-status').text('{{ __("Connected") }}');
            } else if (data.status === 'warning') {
                setStatusWarning('whatsapp');
                $('#whatsapp-status').text('{{ __("Not configured") }}');
            } else {
                setStatusError('whatsapp');
                $('#whatsapp-status').text('{{ __("Connection error") }}');
            }
        }
        
        // Function to update payment gateways status
        function updatePaymentStatus(data) {
            if (data.status === 'ok') {
                setStatusOk('payment');
                $('#payment-status').text('{{ __("All gateways available") }}');
            } else {
                setStatusError('payment');
                $('#payment-status').text('{{ __("Gateway issues detected") }}');
            }
            
            // Update payment gateways table
            if (data.gateways) {
                updatePaymentGatewaysTable(data.gateways);
            }
        }
        
        // Function to update system information
        function updateSystemInfo(data) {
            $('#module-version').text(data.version);
            
            if (data.components && data.components.cache) {
                $('#cache-driver').text(data.components.cache.driver);
            }
        }
        
        // Function to update payment gateways table
        function updatePaymentGatewaysTable(gateways) {
            var html = '';
            
            $.each(gateways, function(key, gateway) {
                var statusIcon = '';
                
                if (gateway.status === 'ok') {
                    statusIcon = '<i class="ti ti-circle-check text-success"></i>';
                } else {
                    statusIcon = '<i class="ti ti-circle-x text-danger"></i>';
                }
                
                html += '<tr>' +
                    '<td>' + capitalizeFirstLetter(key.replace('_', ' ')) + '</td>' +
                    '<td>' + statusIcon + ' ' + (gateway.status === 'ok' ? '{{ __("Available") }}' : '{{ __("Unavailable") }}') + '</td>' +
                    '<td>' + (gateway.configured ? '<span class="badge bg-success">{{ __("Yes") }}</span>' : '<span class="badge bg-warning">{{ __("No") }}</span>') + '</td>' +
                    '</tr>';
            });
            
            $('#payment-gateways-table').html(html);
        }
        
        // Function to load system logs
        function loadSystemLogs() {
            // In a real implementation, this would fetch actual logs from the server
            // For now, we'll just display sample data
            var sampleLogs = [
                { timestamp: '2023-11-15 09:45:23', level: 'info', message: 'System health check completed' },
                { timestamp: '2023-11-15 08:30:12', level: 'info', message: 'WhatsApp API connection verified' },
                { timestamp: '2023-11-15 07:15:03', level: 'warning', message: 'Payment gateway response time slow (3.2s)' },
                { timestamp: '2023-11-14 23:05:47', level: 'info', message: 'Scheduled maintenance completed' },
                { timestamp: '2023-11-14 22:30:00', level: 'info', message: 'Scheduled maintenance started' }
            ];
            
            var html = '';
            
            $.each(sampleLogs, function(index, log) {
                var levelClass = '';
                
                if (log.level === 'info') {
                    levelClass = 'badge bg-info';
                } else if (log.level === 'warning') {
                    levelClass = 'badge bg-warning';
                } else if (log.level === 'error') {
                    levelClass = 'badge bg-danger';
                }
                
                html += '<tr>' +
                    '<td>' + log.timestamp + '</td>' +
                    '<td><span class="' + levelClass + '">' + log.level + '</span></td>' +
                    '<td>' + log.message + '</td>' +
                    '</tr>';
            });
            
            $('#system-logs').html(html);
        }
        
        // Helper functions for status updates
        function setStatusOk(element) {
            $('#' + element + '-status-icon').html('<i class="ti ti-circle-check text-success" style="font-size: 24px;"></i>');
        }
        
        function setStatusWarning(element) {
            $('#' + element + '-status-icon').html('<i class="ti ti-alert-triangle text-warning" style="font-size: 24px;"></i>');
        }
        
        function setStatusError(element) {
            $('#' + element + '-status-icon').html('<i class="ti ti-circle-x text-danger" style="font-size: 24px;"></i>');
        }
        
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
</script>
@endpush 