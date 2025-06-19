$(document).ready(function () {
    const table = $('#departmentTable').DataTable();

// Auto-filter when category changes
$('#category').on('change', function () {
    const category = $('#category').val().toLowerCase();
    const area = $('#area').val().toLowerCase();
    table.search(category + ' ' + area).draw();
});

// Auto-filter as user types in area input
$('#area').on('input', function () {
    const category = $('#category').val().toLowerCase();
    const area = $('#area').val().toLowerCase();
    table.search(category + ' ' + area).draw();
});

    // Edit modal script
     $('.edit-btn').on('click', function () {
        const btn = $(this);

        // Get data attributes
        const id = btn.data('id');
        const category = btn.data('category');
        const area = btn.data('area');
        const name = btn.data('name');
        const phone = btn.data('phone');
        const email = btn.data('email');

        // Set values in modal inputs
        $('#edit_id').val(id);
        $('#edit_category').val(category);
        $('#edit_area').val(area);
        $('#edit_name').val(name);
        $('#edit_phone').val(phone);
        $('#edit_email').val(email);
    });

});

// alert for deleteing department
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".delete-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault(); // Prevent immediate form submit

            Swal.fire({
                title: 'Are you sure?',
                text: "This department will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FD3A69',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form manually
                }
            });
        });
    });
});

// Update department
const updateBtn=document.getElementById('updateDept');
if(updateBtn){
updateBtn.addEventListener('click', async (e) => {
  e.preventDefault();
  const form = document.getElementById('saveEditDetails');
  const formData = new FormData(form);
  const editBtn = document.getElementById('updateDept');

  editBtn.disabled = true;
  editBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm"></span> Updating...';

  try {
    const response = await fetch('../admin/updatedept.php', {
      method: 'POST',
      body: formData
    });
    const rawText = await response.text();
    let result = {};
    try {
      result = JSON.parse(rawText);
    } catch (parseError) {
      throw new Error("Invalid JSON response from server");
    }

    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'Detail updated successfully',
        text: result.message || 'The department detail updated successfully!',
        showConfirmButton: false,
        timer: 1500
      });
      form.reset();

      // Close the modal after a short delay
      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('editDepartment'));
        if (modal) modal.hide();
        // Optionally, reload the page to reflect changes
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
    editBtn.disabled = false;
    editBtn.innerHTML = "Update Department";
  }
});
}

// category selection script
document.addEventListener('DOMContentLoaded', function () {
  const triggerBtn = document.querySelector('[data-bs-target="#addDepartmentModal"]');
  if (triggerBtn) {
    triggerBtn.addEventListener('click', function () {
      const selectedCategory = document.getElementById('departmentCategory').value;
      const deptCategorySelect = document.getElementById('deptCategory');
      if (deptCategorySelect) {
        deptCategorySelect.value = selectedCategory;
      }
    });
  }
});


document.addEventListener('DOMContentLoaded', function () {
  const submitBtn = document.getElementById('saveDept');
  if (submitBtn) {
    submitBtn.addEventListener('click', async (e) => {
      e.preventDefault();

      const form = document.getElementById("saveDepartmentDetails");
      const formData = new FormData(form);

      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Submitting...';

      try {
        const response = await fetch('../admin/addDept.php', {
          method: "POST",
          body: formData
        });

        const rawText = await response.text();
        let result = {};
        try {
          result = JSON.parse(rawText);
        } catch (parseError) {
          throw new Error("Invalid JSON response from server");
        }

        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Department Added',
            text: result.message || 'The department was added successfully!',
            showConfirmButton: false,
            timer: 2000
          });
          form.reset();
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
        submitBtn.innerHTML = "Save";
      }
    });
  }
});


const tabMap = {
  'allTab': 'tbodyAll',
  'pendingTab': 'tbodyPending',
  'resolvedTab': 'tbodyResolved',
  'rejectedTab': 'tbodyRejected'
};

Object.keys(tabMap).forEach(tabId => {
    const tabElement = document.getElementById(tabId);
    if (tabElement) {
        tabElement.addEventListener('click', (e) => {
            e.preventDefault();

            // Hide all tbody sections
            Object.values(tabMap).forEach(tbodyId => {
                document.getElementById(tbodyId).classList.add('d-none');
            });

            // Remove 'active' class from all tabs
            document.querySelectorAll('#complaintTabs .nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Show the selected tbody
            document.getElementById(tabMap[tabId]).classList.remove('d-none');

            // Mark tab active
            e.target.classList.add('active');
        });
    }
});
