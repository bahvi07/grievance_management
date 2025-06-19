document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.resBt').forEach(button => {
    button.addEventListener('click', () => {
      const refid = button.getAttribute('data-resid');
      document.getElementById('resolve_refid').value = refid;
      console.log('Selected refid:', refid);
    });
  });

  const btn = document.getElementById('markRes');
  if (btn) {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      btn.disabled = true;

      const form = document.getElementById('resolveForm');
      const formData = new FormData(form);

      try {
        const response = await fetch('../admin/update-status.php', {
          method: "POST",
          body: formData
        });

        const rawText = await response.text();
        console.log("Raw response:", rawText);

        for (let [key, value] of formData.entries()) {
          console.log(`${key}: ${value}`);
        }

        let result = {};
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
        btn.disabled = false;
        btn.innerHTML = "Save";
      }
    });
  }
});
