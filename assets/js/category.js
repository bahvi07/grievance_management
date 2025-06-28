$(document).ready(function() {
    // Initialize DataTable for categories - check if already initialized
    var table;
    if ($.fn.DataTable.isDataTable('#departmentTable')) {
        table = $('#departmentTable').DataTable();
    } else {
        table = $('#departmentTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']]
        });
    }

    // Add Category
    $('#add').on('click', async function(e) {
        e.preventDefault();
        
        const form = document.getElementById('addCategoryForm');
        const formData = new FormData(form);
        const addBtn = document.getElementById('add');

        addBtn.disabled = true;
        addBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

        try {
            const response = await fetch('../admin/action/add_category.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Category Added',
                    text: result.message || 'Category added successfully!',
                    showConfirmButton: false,
                    timer: 1500
                });
                
                form.reset();
                
                // Close modal and reload page
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('categoryModal'));
                    if (modal) modal.hide();
                    location.reload();
                }, 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Something went wrong!',
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: error.message || 'An unexpected error occurred.',
            });
        } finally {
            addBtn.disabled = false;
            addBtn.innerHTML = 'Add Category';
        }
    });

    // Edit Category - Populate modal
    $('#editCategoryModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const category = button.data('category');
        const status = button.data('status');

        const modal = $(this);
        modal.find('#editCategoryId').val(id);
        modal.find('#editCategoryName').val(category);
        modal.find('#editCategoryStatus').val(status);
    });

    // Update Category
    $('#editCategoryForm').on('submit', async function(e) {
        e.preventDefault();
        
        const form = document.getElementById('editCategoryForm');
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

        try {
            const response = await fetch('../admin/action/edit_category.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Category Updated',
                    text: result.message || 'Category updated successfully!',
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Close modal and reload page
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
                    if (modal) modal.hide();
                    location.reload();
                }, 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Something went wrong!',
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: error.message || 'An unexpected error occurred.',
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Update Category';
        }
    });

    // Delete Category - Using simplified endpoint for testing
    $('#departmentTable tbody').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const categoryId = btn.data('id');
        const row = btn.closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "This category will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while the category is being removed.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('../admin/action/delete_category_simple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: categoryId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.close();
                        // Use DataTables API to remove the row for a smooth animation
                        table.row(row).remove().draw(false);
                        
                        toastr.success(data.message || 'Category deleted successfully!');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Deletion Failed',
                            text: data.message || 'An unknown error occurred.'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'A connection error occurred. Please try again.'
                    });
                });
            }
        });
    });
}); 