document.addEventListener('DOMContentLoaded', function() {
    const codeBtn = document.getElementById('sendCode');
    const resetBtn=document.getElementById('reset_pswd');
    if (codeBtn) {
        codeBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('adminEmail');
            const email = emailInput ? emailInput.value : '';
            const admin_id = document.getElementById('adminId');
            const id = admin_id ? admin_id.value : '';
            
            // Disable button and show loader
            codeBtn.disabled = true;
            const originalText = codeBtn.innerHTML;
            codeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            
            if (!email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Email is required.'
                });
                // Re-enable button
                codeBtn.disabled = false;
                codeBtn.innerHTML = originalText;
                return;
            }
            
            try {
                const form = document.getElementById('changePasswordForm');
                const csrfToken = form.querySelector('input[name="csrf_token"]').value;
                
                const response = await fetch('../otp/send-code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        email, 
                        id,
                        csrf_token: csrfToken 
                    })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                
                const result = await response.json();
                if (result.success) {
                    codeBtn.style.display = 'none';
                    
                    // Enable the verification code input
                    const verificationCodeInput = document.getElementById('verificationCode');
                    document.getElementById('newPassword').disabled = false;
                    document.getElementById('confirmPassword').disabled = false;
                    document.getElementById('reset_pswd').disabled = false;
                    
                    if (verificationCodeInput) verificationCodeInput.disabled = false;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to send code.'
                    });
                    // Re-enable button
                    codeBtn.disabled = false;
                    codeBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
                // Re-enable button
                codeBtn.disabled = false;
                codeBtn.innerHTML = originalText;
            }
        });
    }

    if(resetBtn){
        resetBtn.addEventListener('click',async(e)=>{
            // Disable button and show loader
            resetBtn.disabled = true;
            const originalText = resetBtn.innerHTML;
            resetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
            
            const form=document.getElementById('changePasswordForm');
            // Build a JS object from form fields
            const verificationCode = document.getElementById('verificationCode').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const csrfToken = form.querySelector('input[name="csrf_token"]').value;

            const payload = {
                verificationCode,
                newPassword,
                confirmPassword,
                csrf_token: csrfToken
            };

            try{
                const res=await fetch('../otp/verify-code.php',{
                    method:'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body:JSON.stringify(payload),
                });
                const data=await res.json();
                if(data.success){
                    Swal.fire({
                        icon:'success',
                        title:'Code verified',
                        text:'Your password is updated successfully'
                    }).then(() => {
                        // Optionally reset form and close modal
                        document.getElementById('changePasswordForm').reset();
                         bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                        window.location.reload();
                    });
                    resetBtn.innerHTML = 'Successfully Updated';
                }else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to reset Password.'
                    }).then(() => {
                        // Optionally clear password fields and focus
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                        document.getElementById('newPassword').focus();
                    });
                    resetBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
                resetBtn.innerHTML = originalText;
            } finally {
                resetBtn.disabled = false;
            }
        });
    }

    // Password strength indicator
    const passwordInput = document.getElementById('newPassword');
    const bar = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');
    if (passwordInput && bar && text) {
        passwordInput.addEventListener('input', function () {
            const val = passwordInput.value;
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            // Update bar and text
            if (score <= 2) {
                bar.style.width = '33%';
                bar.style.background = 'red';
                text.textContent = 'Weak';
                text.style.color = 'red';
            } else if (score === 3 || score === 4) {
                bar.style.width = '66%';
                bar.style.background = 'orange';
                text.textContent = 'Medium';
                text.style.color = 'orange';
            } else if (score === 5) {
                bar.style.width = '100%';
                bar.style.background = 'green';
                text.textContent = 'Strong';
                text.style.color = 'green';
            } else {
                bar.style.width = '0';
                text.textContent = '';
            }
        });
    }
});

// Toggle pswd 
$(document).ready(() => {
    $('#toggleNewPassword').on('click', () => {
        const passwordInput = $('#newPassword');
        const icon = $('#newPasswordIcon');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });
    $('#toggleConfirmPassword').on('click', () => {
        const passwordInput = $('#confirmPassword');
        const icon = $('#confirmPasswordIcon');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });
});