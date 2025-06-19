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
            
            codeBtn.innerText="Sending....";
            codeBtn.disabled=true;
            if (!email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Email is required.'
                });
                return;
            }
            
            try {
                const response = await fetch('../otp/send-code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, id })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                
                const result = await response.json();
                if (result.success) {
                  codeBtn.style.display='none';
                  
                    // Enable the verification code input
                    const verificationCodeInput = document.getElementById('verificationCode');
                    document.getElementById('newPassword').disabled=false;
                    document.getElementById('confirmPassword').disabled=false;
                    document.getElementById('reset_pswd').disabled=false;
                    
                    
                    if (verificationCodeInput) verificationCodeInput.disabled = false;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to send code.'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            }
        });
    }

    if(resetBtn){
        resetBtn.addEventListener('click',async(e)=>{
            resetBtn.disabled=true;
            resetBtn.innerHTML='Wait...';
const form=document.getElementById('changePasswordForm');
// Build a JS object from form fields
const verificationCode = document.getElementById('verificationCode').value;
const newPassword = document.getElementById('newPassword').value;
const confirmPassword = document.getElementById('confirmPassword').value;

const payload = {
    verificationCode,
    newPassword,
    confirmPassword
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
    }else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Failed to reset Password.'
        });
    }
} catch (error) {
    console.error('Error:', error);
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'An error occurred. Please try again.'
    });
} finally {
    resetBtn.disabled = false;
    resetBtn.innerHTML = 'Succesfully Updated';
}
    });}
});

// Toggle pswd 
$(document).ready(() => {
    $('#toggleNewPassword').on('click', () => {
        const passwordInput = $('#newPassword');
        const icon = $('#newPasswordIcon');

        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);

        icon.toggleClass('fa-eye fa-eye-slash'); // Toggle both classes
    });
    $('#toggleConfirmPassword').on('click', () => {
        const passwordInput = $('#confirmPassword');
        const icon = $('#confirmPasswordIcon');

        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);

        icon.toggleClass('fa-eye fa-eye-slash'); // Toggle both classes
    });
});