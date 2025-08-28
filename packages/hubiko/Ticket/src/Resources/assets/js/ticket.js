/**
 * Ticket Module - JavaScript
 */

(function() {
    'use strict';
    
    // Initialize ticket functionality when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initTicketModule();
    });
    
    /**
     * Initialize the ticket module functionality
     */
    function initTicketModule() {
        setupAttachmentPreview();
        setupTicketFilters();
        setupTicketStatusChange();
        setupTicketAssignChange();
        setupReplyForm();
    }
    
    /**
     * Setup attachment preview functionality
     */
    function setupAttachmentPreview() {
        const fileInput = document.getElementById('ticket-attachments');
        const previewContainer = document.getElementById('attachment-preview-container');
        
        if (!fileInput || !previewContainer) return;
        
        fileInput.addEventListener('change', function(e) {
            previewContainer.innerHTML = '';
            
            Array.from(this.files).forEach(function(file, index) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'ticket-attachment-preview';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        
                        const removeBtn = document.createElement('span');
                        removeBtn.className = 'remove-attachment';
                        removeBtn.innerHTML = '×';
                        removeBtn.setAttribute('data-index', index);
                        removeBtn.addEventListener('click', removeAttachment);
                        
                        preview.appendChild(img);
                        preview.appendChild(removeBtn);
                        previewContainer.appendChild(preview);
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    // For non-image files, show a generic icon
                    const preview = document.createElement('div');
                    preview.className = 'ticket-attachment-preview file';
                    
                    const fileIcon = document.createElement('div');
                    fileIcon.className = 'file-icon';
                    fileIcon.innerHTML = '<i class="ti ti-file"></i>';
                    
                    const fileName = document.createElement('span');
                    fileName.className = 'file-name';
                    fileName.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                    
                    const removeBtn = document.createElement('span');
                    removeBtn.className = 'remove-attachment';
                    removeBtn.innerHTML = '×';
                    removeBtn.setAttribute('data-index', index);
                    removeBtn.addEventListener('click', removeAttachment);
                    
                    preview.appendChild(fileIcon);
                    preview.appendChild(fileName);
                    preview.appendChild(removeBtn);
                    previewContainer.appendChild(preview);
                }
            });
        });
    }
    
    /**
     * Remove attachment when the remove button is clicked
     */
    function removeAttachment(e) {
        const index = parseInt(e.target.getAttribute('data-index'));
        const fileInput = document.getElementById('ticket-attachments');
        
        // Cannot directly modify a FileList, need to create a new input
        // This is a placeholder for the functionality
        console.log('Remove attachment at index: ' + index);
        
        // Remove the preview
        e.target.parentElement.remove();
    }
    
    /**
     * Setup ticket filter functionality
     */
    function setupTicketFilters() {
        const filterForm = document.getElementById('ticket-filter-form');
        
        if (!filterForm) return;
        
        // Automatically submit form when select fields change
        const filterSelects = filterForm.querySelectorAll('select');
        filterSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
    
    /**
     * Setup ticket status change functionality
     */
    function setupTicketStatusChange() {
        const statusSelect = document.getElementById('ticket-status-change');
        
        if (!statusSelect) return;
        
        statusSelect.addEventListener('change', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            const newStatus = this.value;
            
            // Make AJAX request to update status
            fetch(`/ticket/${ticketId}/status/change?status=${newStatus}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message
                    showNotification('success', data.message);
                    
                    // Update status badge
                    updateStatusBadge(newStatus);
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating ticket status:', error);
                showNotification('error', 'An error occurred while updating the status');
            });
        });
    }
    
    /**
     * Update the status badge in the UI
     */
    function updateStatusBadge(status) {
        const statusBadge = document.querySelector('.ticket-status-badge');
        
        if (!statusBadge) return;
        
        // Remove all status classes
        statusBadge.classList.remove(
            'ticket-status-new',
            'ticket-status-in-progress',
            'ticket-status-on-hold',
            'ticket-status-closed',
            'ticket-status-resolved'
        );
        
        // Add the new status class
        let statusClass = '';
        let statusText = '';
        
        switch (status) {
            case 'New Ticket':
                statusClass = 'ticket-status-new';
                statusText = 'New';
                break;
            case 'In Progress':
                statusClass = 'ticket-status-in-progress';
                statusText = 'In Progress';
                break;
            case 'On Hold':
                statusClass = 'ticket-status-on-hold';
                statusText = 'On Hold';
                break;
            case 'Closed':
                statusClass = 'ticket-status-closed';
                statusText = 'Closed';
                break;
            case 'Resolved':
                statusClass = 'ticket-status-resolved';
                statusText = 'Resolved';
                break;
        }
        
        statusBadge.classList.add(statusClass);
        statusBadge.textContent = statusText;
    }
    
    /**
     * Setup ticket assign change functionality
     */
    function setupTicketAssignChange() {
        const assignSelect = document.getElementById('ticket-assign-change');
        
        if (!assignSelect) return;
        
        assignSelect.addEventListener('change', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            const agentId = this.value;
            
            // Make AJAX request to update assignment
            fetch(`/ticket/${ticketId}/assign/change?agent_id=${agentId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', data.message);
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating ticket assignment:', error);
                showNotification('error', 'An error occurred while updating the assignment');
            });
        });
    }
    
    /**
     * Setup reply form functionality
     */
    function setupReplyForm() {
        const replyForm = document.getElementById('ticket-reply-form');
        
        if (!replyForm) return;
        
        replyForm.addEventListener('submit', function(e) {
            const description = document.querySelector('[name="description"]').value.trim();
            
            if (!description) {
                e.preventDefault();
                showNotification('error', 'Reply message cannot be empty');
                return false;
            }
            
            // Display loading state
            const submitBtn = replyForm.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ti ti-loader animate-spin mr-1"></i> Sending...';
            submitBtn.disabled = true;
            
            // Reset after form submission (or error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    }
    
    /**
     * Show a notification message
     */
    function showNotification(type, message) {
        // Check if the notification function exists in the global scope
        if (typeof window.notify === 'function') {
            window.notify(type, message);
        } else {
            // Fallback to console
            if (type === 'error') {
                console.error(message);
            } else {
                console.log(message);
            }
            
            // Show an alert as last resort
            alert(message);
        }
    }
})(); 