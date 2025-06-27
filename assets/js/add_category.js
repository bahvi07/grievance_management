document.addEventListener('DOMContentLoaded', function() {
    // Add Category AJAX
   const btn=document.getElementById('add');
    if (btn) {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const addForm = document.getElementById('addCategoryForm');
            const formData = new FormData(addForm);
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfTokenMeta) {
                formData.append('csrf_token', csrfTokenMeta.getAttribute('content'));
            }
            try {
                const response = await fetch('action/add_category.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Category Added!',
                        text: result.message || 'The category was added successfully.',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to add category.'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred.'
                });
            }
        });
    }

    // Populate Edit Category Modal
    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const category = this.getAttribute('data-category');
            const status = this.getAttribute('data-status');
            
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = category;
            document.getElementById('editCategoryStatus').value = status;
        });
    });

    // Edit Category AJAX
    const editForm = document.getElementById('editCategoryForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfTokenMeta) {
                formData.append('csrf_token', csrfTokenMeta.getAttribute('content'));
            }
            try {
                const response = await fetch('action/edit_category.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Category Updated!',
                        text: result.message || 'The category was updated successfully.',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to update category.'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred.'
                });
            }
        });
    }
});