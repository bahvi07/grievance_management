document.addEventListener("DOMContentLoaded", function () {
  const submitBtn = document.getElementById('submit_feedback');
  const feedbackForm = document.getElementById('feedbackForm');
  const stars = document.querySelectorAll('.star');
  const ratingInput = document.getElementById('ratingInput');
  const ratingText = document.querySelector('.rating-text small');

  // Star rating functionality
  if (stars.length > 0) {
    let currentRating = 5; // Default rating
    
    // Initialize stars (set default 5 stars as filled)
    updateStars(5);
    updateRatingText(5);
    
    stars.forEach((star, index) => {
      star.addEventListener('click', function(e) {
        e.preventDefault();
        const rating = parseInt(this.getAttribute('data-rating'));
        currentRating = rating;
        ratingInput.value = rating;
        updateStars(rating);
        updateRatingText(rating);
        console.log('Rating selected:', rating); // Debug log
      });
      
      star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        // Only show hover effect if no rating is selected yet
        if (currentRating === 5) {
          updateStars(rating);
        }
      });
      
      star.addEventListener('mouseleave', function() {
        updateStars(currentRating);
      });
    });
    
    function updateStars(rating) {
      stars.forEach((star, index) => {
        const starRating = index + 1;
        if (starRating <= rating) {
          star.classList.add('filled');
          star.classList.remove('active');
        } else {
          star.classList.remove('filled', 'active');
        }
      });
    }
    
    function updateRatingText(rating) {
      const ratingMessages = {
        1: 'Poor - We need to improve significantly',
        2: 'Fair - We have room for improvement',
        3: 'Good - We\'re doing okay',
        4: 'Very Good - We\'re doing well',
        5: 'Excellent - We\'re doing great!'
      };
      
      if (ratingText) {
        ratingText.textContent = ratingMessages[rating] || 'Click on a star to rate';
      }
    }
  }

  if (submitBtn && feedbackForm) {
    submitBtn.addEventListener('click', async function (e) {
      e.preventDefault();

      const name = feedbackForm.querySelector('input[name="feed_u_name"]').value.trim();
      const feedback = feedbackForm.querySelector('textarea[name="feedback"]').value.trim();
      const userPhone = feedbackForm.querySelector('input[name="user_phone"]').value;
      const rating = parseInt(ratingInput.value);

      if (!name) {
        toastr.warning("Please enter your name.");
        return;
      }
      if (!feedback) {
        toastr.warning("Please enter your feedback.");
        return;
      }
      if (rating < 1 || rating > 5) {
        toastr.warning("Please select a rating.");
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
          // Reset stars to default
          if (stars.length > 0) {
            currentRating = 5;
            ratingInput.value = 5;
            updateStars(5);
            updateRatingText(5);
          }
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