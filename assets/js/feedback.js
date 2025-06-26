document.addEventListener("DOMContentLoaded", function () {
  const submitBtn = document.getElementById('submit_feedback');
  const feedbackForm = document.getElementById('feedbackForm');

  if (submitBtn && feedbackForm) {
    submitBtn.addEventListener('click', async function (e) {
      e.preventDefault();

      const name = feedbackForm.querySelector('input[name="feed_u_name"]').value.trim();
      const feedback = feedbackForm.querySelector('textarea[name="feedback"]').value.trim();
      const userPhone = feedbackForm.querySelector('input[name="user_phone"]').value;

      if (!name) {
        toastr.warning("Please enter your name.");
        return;
      }
      if (!feedback) {
        toastr.warning("Please enter your feedback.");
        return;
      }

      // Disable button and show loader
      submitBtn.disabled = true;
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

      try {
        // Use FormData to include all form fields including CSRF token
        const formData = new FormData(feedbackForm);
        
        const response = await fetch('action/submit-feedback.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          toastr.success("Thank you for your feedback!");
          feedbackForm.reset();
          setTimeout(() => {
            const feedbackModal = bootstrap.Modal.getInstance(document.getElementById('feedback'));
            if (feedbackModal) feedbackModal.hide();
          }, 1200);
        } else {
          toastr.error(result.message || "Failed to submit feedback.");
        }
      } catch (error) {
        toastr.error("An error occurred. Please try again later.");
      } finally {
        // Re-enable button and restore original text
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  }
});