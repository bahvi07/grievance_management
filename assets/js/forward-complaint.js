document.addEventListener('DOMContentLoaded', function() {
    // Handle forward form submissions
    document.addEventListener('submit', async function(e) {
        if (!e.target.classList.contains('ajax-forward-form')) return;
        
        e.preventDefault();
        
        // Get the submit button from the form
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Forwarding...';
        
        try {
            const response = await fetch(e.target.action, {
                method: 'POST',
                body: new FormData(e.target)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('forwardModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Reload the page to update the status
                    window.location.reload();
                });
            } else {
                throw new Error(result.message || 'Failed to forward complaint');
            }
            
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to forward complaint'
            });
        } finally {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });
}); 