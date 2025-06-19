document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("toggleSidebar");
  const sidebar = document.getElementById("sidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("show");
    });
  }

// Send Complain through AJAX
const submitBtn = document.getElementById("submitComplain");
if (submitBtn) {
  submitBtn.addEventListener("click", async (e) => {
    e.preventDefault();

    const form = document.getElementById("complaintForm");
    const submitBtn = document.getElementById("submitComplain");
    const formData = new FormData(form);

    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm"></span> Submitting...';

    try {
      const response = await fetch("raise-complaint.php", {
        method: "POST",
        body: formData,
      });

      const rawText = await response.text(); // Read once
      console.log("Raw response:", rawText);

      let result = {};
      try {
        result = JSON.parse(rawText);
        console.log("Parsed JSON:", result);
      } catch (parseError) {
        throw new Error("Invalid JSON response from server");
      }

      if (result.success) {
        // Function to copy text to clipboard
        const copyToClipboard = (text) => {
          navigator.clipboard.writeText(text).then(() => {
            // Show temporary feedback
            Swal.fire({
              icon: "success",
              title: "Copied!",
              text: "Reference ID copied to clipboard",
              timer: 1500,
              showConfirmButton: false
            });
          }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            
            Swal.fire({
              icon: "success",
              title: "Copied!",
              text: "Reference ID copied to clipboard",
              timer: 1500,
              showConfirmButton: false
            });
          });
        };

        Swal.fire({
          icon: "success",
          title: "Success!",
          html:
            result.message +
            (result.refId
              ? `<br><strong>Reference ID:</strong> 
                 <span id="refIdText" style="color: #007bff; cursor: pointer; text-decoration: underline; user-select: all;" 
                       title="Click to copy">${result.refId}</span>
                 <button id="copyRefBtn" style="margin-left: 8px; padding: 2px 6px; border: 1px solid #007bff; background: #007bff; color: white; border-radius: 3px; cursor: pointer; font-size: 12px;" 
                         title="Copy Reference ID">📋</button>`
              : ""),
          confirmButtonColor: "#3085d6",
          didOpen: () => {
            // Add click event to reference ID text
            const refIdText = document.getElementById("refIdText");
            if (refIdText) {
              refIdText.addEventListener("click", () => {
                copyToClipboard(result.refId);
              });
            }

            // Add click event to copy button
            const copyBtn = document.getElementById("copyRefBtn");
            if (copyBtn) {
              copyBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                copyToClipboard(result.refId);
              });
            }
          }
        }).then(() => {
          form.reset();
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("complaintModal")
          );
          modal.hide();
        });
      } else {
        let errorMessage = result.message || "Something went wrong";
        if (result.errors) {
          errorMessage += "<br>" + Object.values(result.errors).join("<br>");
        }

        Swal.fire({
          icon: "error",
          title: "Error",
          html: errorMessage,
          confirmButtonColor: "#d33",
        });
      }
    } catch (error) {
      console.error("Caught Error:", error);
      Swal.fire({
        icon: "error",
        title: "Request Failed",
        html: error.message || "Unknown error occurred",
        confirmButtonColor: "#d33",
      });
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = "Submit";
    }
  });
}});

// Update Phone Script 
const updateBtn = document.getElementById('updatePhone');

if (updateBtn) {
  updateBtn.addEventListener('click', async (e) => {
    e.preventDefault();

    let form = document.getElementById('editPhoneForm');
    let formData = new FormData(form);

    updateBtn.disabled = true;
    updateBtn.innerText = 'Updating...';

    try {
      const response = await fetch('edit-phone.php', {
        method: 'POST',
        body: formData,
      });

      const result = await response.json(); // ✅ FIX: parse as JSON, not stringify
console.log(result.message);
      if (result.success) {
        Swal.fire({
          icon: "success",
          title: "Success",
          html: `${result.message}`,
        }).then(() => {
          form.reset();
          bootstrap.Modal.getInstance(document.getElementById("edit-profile")).hide();
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
      updateBtn.disabled = false;
      updateBtn.innerText = "Update";
    }
  });
}
