// Sidebar Toggle
document.getElementById("toggleSidebar").addEventListener("click", () => {
  document.getElementById("sidebar").classList.toggle("show");
});

// Submit Complaint AJAX
document.getElementById("submitComplain").addEventListener("click", async (e) => {
  e.preventDefault();
  const form = document.getElementById("complaintForm");
  const submitBtn = document.getElementById("submitComplain");
  const formData = new FormData(form);

  submitBtn.disabled = true;
  submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Submitting...`;

  try {
    const response = await fetch("raise-complaint.php", {
      method: "POST",
      body: formData,
    });

    const text = await response.text();
    const result = JSON.parse(text);

    if (result.success) {
      Swal.fire({
        icon: "success",
        title: "Success",
        html: `${result.message}<br><strong>Ref ID:</strong> ${result.refId}`,
      }).then(() => {
        form.reset();
        bootstrap.Modal.getInstance(document.getElementById("complaintModal")).hide();
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        html: result.message,
      });
    }
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Request Failed",
      html: err.message,
    });
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = "Submit";
  }
});
