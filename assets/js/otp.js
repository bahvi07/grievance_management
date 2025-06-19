// =============================
// SEND OTP & VALIDATE PHONE
// =============================
document.addEventListener("DOMContentLoaded", () => {
  const otpBtn = document.querySelector(".otpBtn")
  const bootstrap = window.bootstrap // Declare bootstrap variable
  const Swal = window.Swal // Declare Swal variable
  const toastr = window.toastr // Declare toastr variable

  if (otpBtn) {
    otpBtn.addEventListener("click", async () => {
      const phoneInput = document.getElementById("phone");
      const phone = phoneInput.value.trim()

      const pattern = /^[6-9]\d{9}$/
      if (!pattern.test(phone)) {
        showToastr("error")
        phoneInput.focus()
        return
      }

      showToastr("success")

      try {
        const response = await fetch("../otp/send-otp.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `phone=${encodeURIComponent(phone)}`,
        })

        const result = await response.text()

        try {
          const data = JSON.parse(result)
          if (data.status === "success") {
            const otpModal = new bootstrap.Modal(document.getElementById("otpModal"))
            setTimeout(() => {
              otpModal.show()
              // Show OTP in toastr for development/testing only, after modal opens
              if (data.otp) {
                setTimeout(() => {
                  toastr.info(`Your OTP is: <b>${data.otp}</b>`, "Your OTP", { timeOut: 7000, escapeHtml: false })
                }, 800) // 500ms delay after modal opens
              }
            }, 800)
          } else {
            throw new Error(data.message || "Unknown error occurred")
          }
        } catch (e) {
          console.error("Non-JSON response:", result)
          Swal.fire("Server Error", "Unexpected server response.", "error")
        }
      } catch (error) {
        console.error("Error sending OTP:", error)
        Swal.fire("Failed", "Failed to send OTP: " + error.message, "error")
      }
    })
  }
})

// =============================
// TOASTR NOTIFICATIONS
// =============================
function showToastr(type) {
  const toastr = window.toastr // Declare toastr variable
  if (type === "success") {
    toastr.success("Phone number is valid. Sending OTP...")
  } else {
    toastr.error("Invalid phone number! Enter 10 digits starting with 6-9.")
  }
}

// =============================
// OTP INPUT FIELD BEHAVIOR
// =============================
const inputs = document.querySelectorAll(".otp-input")
inputs.forEach((input, index) => {
  input.addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/\D/, "")
    if (e.target.value && index < inputs.length - 1) {
      inputs[index + 1].focus()
    }
  })

  input.addEventListener("keydown", (e) => {
    if (e.key === "Backspace") {
      if (input.value === "") {
        if (index > 0) {
          inputs[index - 1].focus()
          inputs[index - 1].value = ""
          e.preventDefault()
        }
      } else {
        input.value = ""
      }
    }
  })

  input.addEventListener("paste", (e) => {
    e.preventDefault()
    const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim().slice(0, inputs.length)

    pasteData.split("").forEach((char, i) => {
      if (/\d/.test(char)) {
        inputs[i].value = char
      }
    })

    if (pasteData.length === inputs.length) {
      inputs[inputs.length - 1].focus()
    } else {
      inputs[pasteData.length]?.focus()
    }
  })
})

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

    if (finalOtp.length !== otpFields.length) {
      Swal.fire("Incomplete OTP", "Please enter all digits of the OTP.", "warning")
      return
    }

    try {
      const response = await fetch("../otp/verify-otp.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `otp=${encodeURIComponent(finalOtp)}`,
      })

      const result = await response.text()
console.log(result);
      try {
        const data = JSON.parse(result)

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
          // ✅ Fixed: Wait for user to click OK before reloading
          Swal.fire({
            icon: "error",
            title: "Invalid OTP",
            text: data.message || "OTP didn't match",
            confirmButtonText: "Try Again",
          }).then((result) => {
            if (result.isConfirmed || result.isDismissed) {
              // Clear OTP inputs and close modal
              document.querySelectorAll(".otp-input").forEach((input) => {
                input.value = ""
              })
              // Focus on first OTP input
              const firstOtpInput = document.querySelector(".otp-input")
              if (firstOtpInput) {
                firstOtpInput.focus()
              }
              // Optionally close modal and go back to phone input
              const otpModal = bootstrap.Modal.getInstance(document.getElementById("otpModal"))
              if (otpModal) {
                otpModal.hide()
              }
            }
          })
        }
      } catch (e) {
        console.error("JSON parse error:", result)
        Swal.fire("Server Error", "Unexpected response from server.", "error")
      }
    } catch (error) {
      console.error("Request error:", error)
      Swal.fire("Failed", "Failed to verify OTP: " + error.message, "error")
    }
  })
}

// =============================
// ENTER KEY HANDLERS
// =============================
document.addEventListener("DOMContentLoaded", () => {
  const phoneInput = document.getElementById("phone")
  const Swal = window.Swal // Declare Swal variable

  if (phoneInput) {
    phoneInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault()
        const otpBtn = document.querySelector(".otpBtn")
        if (otpBtn) otpBtn.click()
      }
    })
  }

  document.querySelectorAll(".otp-input").forEach((input) => {
    input.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault()
        const verifyBtn = document.getElementById("verifyOtpBtn")
        if (verifyBtn) verifyBtn.click()
      }
    })
  })
})
