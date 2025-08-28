@extends('layouts.main')

@section('page-title')
    {{ __('Ticket Categories') }}
@endsection

@section('page-breadcrumb')
    - {{ __('Ticket Categories') }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Add Category') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ticket.category.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="workspace" value="{{ getActiveWorkSpace() }}">
                    <input type="hidden" name="created_by" value="{{ creatorId() }}">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Color') }}</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#3498db">
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Categories List') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Color') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories ?? [] as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $category->color }}">
                                            {{ $category->color }}
                                        </span>
                                    </td>
                                    <td>{{ \App\Models\Utility::getDateFormated($category->created_at) }}</td>
                                    <td class="action">
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center edit-category" 
                                               data-id="{{ $category->id }}" 
                                               data-name="{{ $category->name }}" 
                                               data-color="{{ $category->color }}"
                                               data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            <form action="{{ route('ticket.category.destroy', $category->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                    <i class="ti ti-trash text-white"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ticket.category.update', 0) }}" method="POST" id="edit-category-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="workspace" value="{{ getActiveWorkSpace() }}">
                <input type="hidden" name="category_id" id="edit-category-id">
                
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-category-name" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Color') }}</label>
                        <input type="color" name="color" id="edit-category-color" class="form-control form-control-color">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.show_confirm').click(function(e) {
            if(!confirm('{{ __("Are you sure you want to delete this category?") }}')) {
                e.preventDefault();
            }
        });
        
        $('.edit-category').click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var color = $(this).data('color');
            
            $('#edit-category-id').val(id);
            $('#edit-category-name').val(name);
            $('#edit-category-color').val(color);
            
            // Update form action URL
            var baseUrl = "{{ route('ticket.category.update', ':id') }}";
            var newUrl = baseUrl.replace(':id', id);
            $('#edit-category-form').attr('action', newUrl);
            
            $('#editCategoryModal').modal('show');
        });
    });
</script>
@endpush 