// =============================
// SEND OTP & VALIDATE PHONE
// =============================
let countdownInterval;
const resendBtn = document.getElementById('resend');
const expElement = document.getElementById('expire_time');
let currentPhone = '';
let allowImmediateResend = false; // Flag to track if immediate resend is allowed

// Function to start or restart the countdown
function startCountdown() {
  let seconds = 60;
  expElement.classList.remove('d-none');
  resendBtn.classList.add('d-none');
  allowImmediateResend = false; // Reset flag when countdown starts

  // Clear any existing timer
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }

  countdownInterval = setInterval(() => {
    expElement.innerHTML = `Expire in ${seconds} seconds`;
    seconds--;

    if (seconds < 0) {
      clearInterval(countdownInterval);
      expElement.classList.add('d-none');
      resendBtn.classList.remove('d-none');
    }
  }, 1000);
}

document.addEventListener("DOMContentLoaded", () => {
  const otpBtn = document.querySelector(".otpBtn")
  const bootstrap = window.bootstrap // Declare bootstrap variable
  const Swal = window.Swal // Declare Swal variable
  const toastr = window.toastr // Declare toastr variable

  if (otpBtn) {
    otpBtn.addEventListener("click", async () => {
      const phoneInput = document.getElementById("phone");
      const phone = phoneInput.value.trim();
      currentPhone = phone; // Store phone for resend and verify
      
      otpBtn.disabled=true;
      const pattern = /^[6-9]\d{9}$/;
      if (!pattern.test(phone)) {
        showToastr("error", "Invalid phone number! Enter 10 digits starting with 6-9.");
        phoneInput.focus();
        return;
      }

      try {
        const response = await fetch("../otp/send-otp.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `phone=${encodeURIComponent(phone)}`,
        });

        const result = await response.text();

        try {
          const data = JSON.parse(result);
          if (data.status === "success") {
            showToastr("success", "OTP sent successfully!");
            const otpModal = new bootstrap.Modal(document.getElementById("otpModal"));
            
            setTimeout(() => {
              otpModal.show();
              startCountdown();
              
              // OTP Info Toast (delayed for better UX)
              if (data.otp) {
                setTimeout(() => {
                  toastr.info(`Your OTP is: <b>${data.otp}</b>`, "Your OTP", { timeOut: 7000, escapeHtml: false });
                }, 1400);
              }
            }, 800);

          } else {
            Swal.fire("Error", data.message || "An unknown error occurred.", "error");
          }
        } catch (e) {
          console.error("Non-JSON response:", result);
          Swal.fire("Server Error", "Unexpected server response.", "error");
        }
      } catch (error) {
        console.error("Error sending OTP:", error);
        Swal.fire("Failed", "Failed to send OTP: " + error.message, "error");
      }
    });
  }

  // Event listener for the resend button
  if (resendBtn) {
    resendBtn.addEventListener('click', async () => {
      if (!currentPhone) {
        showToastr("error", "Could not find the phone number to resend OTP.");
        return;
      }

      resendBtn.disabled = true;
      resendBtn.innerHTML = '<em>Resending...</em>';

      try {
        // ✅ FIXED: Send flag if immediate resend is allowed after failed verification
        let requestBody = `phone=${encodeURIComponent(currentPhone)}`;
        if (allowImmediateResend) {
          requestBody += '&failed_verification=true';
          
        }

        const response = await fetch("../otp/resend-otp.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: requestBody,
        });

        const data = await response.json();

        if (data.status === 'success') {
          showToastr("success", "A new OTP has been sent!");
          // Clear OTP fields when new OTP is sent
          clearOtpFields();
          startCountdown(); // This will also reset allowImmediateResend to false
          // New OTP Info Toast (delayed)
          if (data.otp) {
             setTimeout(() => {
                toastr.info(`New OTP: <b>${data.otp}</b>`, "Your OTP", { timeOut: 7000, escapeHtml: false });
             }, 1400);
          }
        } else {
          Swal.fire("Error", data.message || "Could not resend OTP.", "error");
        }
      } catch (error) {
        console.error("Resend OTP error:", error);
        Swal.fire("Failed", "An error occurred while trying to resend the OTP.", "error");
      } finally {
        resendBtn.disabled = false;
        resendBtn.innerHTML = 'Resend';
      }
    });
  }
})

// =============================
// TOASTR NOTIFICATIONS
// =============================
function showToastr(type, message) {
  const toastrOptions = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000,
    extendedTimeOut: 1200,
  };

  switch (type) {
    case "success":
      toastr.success(message, "Success", { ...toastrOptions, timeOut: 1500 });
      break;
    case "error":
      toastr.error(message, "Error", toastrOptions);
      break;
    case "info":
      toastr.info(message, "Info", toastrOptions);
      break;
    case "warning":
      toastr.warning(message, "Warning", toastrOptions);
      break;
    default:
      toastr.info(message, "Notification", toastrOptions);
  }
}

// =============================
// OTP INPUT FIELD BEHAVIOR
// =============================

// Function to clear all OTP input fields
function clearOtpFields() {
  const otpFields = document.querySelectorAll(".otp-input");
  otpFields.forEach(field => {
    field.value = "";
  });
  // Focus on first field after clearing
  if (otpFields.length > 0) {
    otpFields[0].focus();
  }
}

// Function to clear all OTP fields (for long press backspace)
function clearAllOtpFields() {
  const otpFields = document.querySelectorAll(".otp-input");
  otpFields.forEach(field => {
    field.value = "";
  });
  // Focus on first field
  if (otpFields.length > 0) {
    otpFields[0].focus();
  }
}

const inputs = document.querySelectorAll(".otp-input");

inputs.forEach((input, index) => {
  let backspaceTimeout;
  let backspaceCount = 0;
  const longPressDelay = 500; // 500ms for long press detection

  // Handle input (typing digits)
  input.addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/\D/, ""); // Only allow digits
    
    if (e.target.value && index < inputs.length - 1) {
      inputs[index + 1].focus();
    }
  });

  // Handle keydown events
  input.addEventListener("keydown", (e) => {
    if (e.key === "Backspace") {
      e.preventDefault();
      
      if (input.value === "") {
        // If current field is empty, go to previous field
        if (index > 0) {
          inputs[index - 1].focus();
          inputs[index - 1].value = "";
        }
      } else {
        // If current field has value, clear it
        input.value = "";
      }
    }
  });

  // Handle keyup events for long press detection
  input.addEventListener("keyup", (e) => {
    if (e.key === "Backspace") {
      backspaceCount++;
      
      // Clear previous timeout
      if (backspaceTimeout) {
        clearTimeout(backspaceTimeout);
      }
      
      // Set timeout to detect long press
      backspaceTimeout = setTimeout(() => {
        if (backspaceCount >= 3) { // If backspace pressed 3+ times quickly
          clearAllOtpFields();
        }
        backspaceCount = 0;
      }, longPressDelay);
    }
  });

  // Handle focus events
  input.addEventListener("focus", (e) => {
    // Select all text when focused (for easy replacement)
    e.target.select();
  });

  // Handle click events
  input.addEventListener("click", (e) => {
    // Select all text when clicked
    e.target.select();
  });

  // Handle paste events
  input.addEventListener("paste", (e) => {
    e.preventDefault();
    const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim().slice(0, inputs.length);

    pasteData.split("").forEach((char, i) => {
      if (/\d/.test(char)) {
        inputs[i].value = char;
      }
    });

    if (pasteData.length === inputs.length) {
      inputs[inputs.length - 1].focus();
    } else {
      inputs[pasteData.length]?.focus();
    }
  });

  // Handle mobile-specific events
  input.addEventListener("touchstart", (e) => {
    // Reset backspace count on touch
    backspaceCount = 0;
  });

  // Handle context menu (right-click) to prevent default behavior
  input.addEventListener("contextmenu", (e) => {
    e.preventDefault();
  });
});

// Handle Enter key for all OTP inputs
document.querySelectorAll(".otp-input").forEach((input) => {
  input.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const verifyBtn = document.getElementById("verifyOtpBtn");
      if (verifyBtn) {
        verifyBtn.click();
      }
    }
  });
});

// =============================
// VERIFY OTP
// =============================
const verifyOtpBtn = document.querySelector("#verifyOtpBtn")
if (verifyOtpBtn) {
  verifyOtpBtn.addEventListener("click", async (e) => {
    e.preventDefault()
    const otpFields = document.querySelectorAll(".otp-input")
    const otp = []

    otpFields.forEach((digit) => {
      otp.push(digit.value.trim())
    })

    const finalOtp = otp.join("")
    
    if (finalOtp.length !== 6) {
      showToastr("error", "Please enter all 6 digits of the OTP.")
      return
    }

    // Fallback: If currentPhone is not set, get it from the phone input
    if (!currentPhone) {
      const phoneInput = document.getElementById("phone");
      if (phoneInput) {
        currentPhone = phoneInput.value.trim();
      }
    }

    // Show spinner/disable button
    verifyOtpBtn.disabled = true;
    const originalText = verifyOtpBtn.innerHTML;
    verifyOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';

    try {
      const response = await fetch("../otp/verify-otp.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `phone=${encodeURIComponent(currentPhone)}&otp=${encodeURIComponent(finalOtp)}`,
      })

      const resultText = await response.text()
      
      try {
        const data = JSON.parse(resultText)

        if (data.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Verified",
            text: "OTP matched! Redirecting...",
            timer: 1500,
            showConfirmButton: false,
          }).then(() => {
            window.location.href = "../user/user-dashboard.php"
          })
        } else {
          showToastr("error", data.message || "OTP verification failed.")
          // Clear OTP fields on failed verification
          clearOtpFields();
          // If verification fails, allow immediate resend
          allowImmediateResend = true
          
          resendBtn.classList.remove("d-none")
          clearInterval(countdownInterval) // Stop the timer
          expElement.classList.add('d-none');
        }
      } catch (e) {
        console.error("Failed to parse server response:", resultText)
        Swal.fire("Server Error", "An unexpected error occurred. Please try again.", "error")
      }
    } catch (error) {
      console.error("Error verifying OTP:", error)
      Swal.fire("Failed", "Could not verify OTP: " + error.message, "error")
    } finally {
      // Restore button state
      verifyOtpBtn.disabled = false;
      verifyOtpBtn.innerHTML = originalText;
    }
  })
}

// =============================
// UNLOCK ACCOUNT
// =============================
const unlockBtn = document.getElementById("unlock");
if (unlockBtn) {
  unlockBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const phone = document.getElementById('phone').value;
    const ip = unlockBtn.dataset.ip;
    
    // Disable button to prevent multiple clicks
    unlockBtn.disabled = true;
    unlockBtn.innerHTML = '<em>Unlocking...</em>';

    try {
        const response = await fetch("../auth/unlock-account.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `phone=${phone}&ip=${ip}`
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire("Unlocked!", "Your account has been unlocked. You can now log in.", "success")
                .then(() => location.reload());
        } else {
            Swal.fire("Error", data.message || "Could not unlock the account.", "error");
            unlockBtn.disabled = false;
            unlockBtn.textContent = 'Unlock Account';
        }
    } catch (error) {
        console.error("Unlock error:", error);
        Swal.fire("Error", "An unexpected error occurred while unlocking the account.", "error");
        unlockBtn.disabled = false;
        unlockBtn.textContent = 'Unlock Account';
    }
  });
}