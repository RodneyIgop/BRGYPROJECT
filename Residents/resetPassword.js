document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('resetPasswordForm');
  const submitBtn = document.getElementById('submitBtn');
  const loading = document.getElementById('loading');
  const newPassword = document.getElementById('new_password');
  const confirmPassword = document.getElementById('confirm_password');
  const strengthText = document.getElementById('strength-text');
  const strengthBar = document.getElementById('strength-bar');

  if (!form) return;

  // Password strength checker
  newPassword.addEventListener('input', () => {
    const password = newPassword.value;
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    strengthBar.className = 'password-strength-meter div';
    
    if (strength <= 2) {
      strengthBar.className = 'strength-weak';
      strengthText.textContent = 'Weak';
    } else if (strength <= 3) {
      strengthBar.className = 'strength-medium';
      strengthText.textContent = 'Medium';
    } else {
      strengthBar.className = 'strength-strong';
      strengthText.textContent = 'Strong';
    }
  });

  // Toggle password visibility
  document.querySelectorAll('.toggle-password').forEach(toggle => {
    toggle.addEventListener('click', () => {
      const inputId = toggle.getAttribute('data-target');
      const input = document.getElementById(inputId);
      const icon = toggle.querySelector('i');

      if (!input) return;

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const newPasswordVal = newPassword.value;
    const confirmPasswordVal = confirmPassword.value;
    
    // Validation
    if (newPasswordVal.length < 8) {
      showError('Password must be at least 8 characters long.');
      return;
    }
    
    if (newPasswordVal !== confirmPasswordVal) {
      showError('Passwords do not match.');
      return;
    }
    
    // Check password strength
    let strength = 0;
    if (newPasswordVal.length >= 8) strength++;
    if (newPasswordVal.match(/[a-z]/)) strength++;
    if (newPasswordVal.match(/[A-Z]/)) strength++;
    if (newPasswordVal.match(/[0-9]/)) strength++;
    
    if (strength < 3) {
      showError('Password is too weak. Please use at least 8 characters with uppercase, lowercase, and numbers.');
      return;
    }

    // Show loading state
    submitBtn.disabled = true;
    loading.style.display = 'block';
    submitBtn.textContent = 'Resetting...';

    try {
      const formData = new FormData(form);
      const response = await fetch('resetPasswordProcess.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.text();
      
      if (response.ok && result.includes('success')) {
        // Show success and redirect
        window.location.href = 'resetPassword.php?token=' + encodeURIComponent(formData.get('token')) + '&success=reset';
        setTimeout(() => {
          window.location.href = 'residentlogin.php';
        }, 3000);
      } else {
        // Show error
        window.location.href = 'resetPassword.php?token=' + encodeURIComponent(formData.get('token')) + '&error=failed';
      }
    } catch (error) {
      console.error('Error:', error);
      window.location.href = 'resetPassword.php?token=' + encodeURIComponent(formData.get('token')) + '&error=failed';
    } finally {
      // Reset button state (only if not successful)
      if (!window.location.href.includes('success=reset')) {
        submitBtn.disabled = false;
        loading.style.display = 'none';
        submitBtn.textContent = 'Reset Password';
      }
    }
  });

  function showError(message) {
    // Remove any existing error messages
    const existingError = document.querySelector('.error-msg');
    if (existingError) {
      existingError.remove();
    }

    // Add error message
    const errorDiv = document.createElement('p');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    
    const form = document.getElementById('resetPasswordForm');
    form.insertBefore(errorDiv, form.querySelector('button'));
  }
});