document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.resBt').forEach(button => {
    button.addEventListener('click', () => {
      const refid = button.getAttribute('data-resid');
      document.getElementById('resolve_refid').value = refid;
    });
  });

  const btn = document.getElementById('markRes');
  if (btn) {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      
      // Disable button and show loading state
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

      const form = document.getElementById('resolveForm');
      const formData = new FormData(form);

      try {
        const response = await fetch('../admin/update-status.php', {
          method: "POST",
          body: formData
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const rawText = await response.text();
        let result;
        
        try {
          result = JSON.parse(rawText);
        } catch (parseError) {
          throw new Error("Invalid JSON response from server");
        }

        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: result.message || 'Status updated successfully!',
            showConfirmButton: false,
            timer: 2000
          }).then(() => {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('resolveModal'));
            if (modal) {
              modal.hide();
            }
            // Reload the page to show updated status
            window.location.reload();
          });
        } else {
          throw new Error(result.message || 'Failed to update status');
        }

      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'An unexpected error occurred.',
        });
      } finally {
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = "Mark as Resolved";
      }
    });
  }
});
