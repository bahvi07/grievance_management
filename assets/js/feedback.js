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

      try {
        const response = await fetch('submit-feedback.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `feed_u_name=${encodeURIComponent(name)}&feedback=${encodeURIComponent(feedback)}&user_phone=${encodeURIComponent(userPhone)}`
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
        console.error(error);
      }
    });
  }
});