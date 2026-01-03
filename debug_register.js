// Debug version of register validation
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    const termsCheckbox = document.getElementById('accept_terms');
    const pwd = document.getElementById('password');
    const confirmPwd = document.getElementById('confirm_password');
    
    function validatePassword() {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,25}$/;
        console.log('Password:', pwd.value);
        console.log('Password valid:', regex.test(pwd.value));
        return regex.test(pwd.value);
    }
    
    function debugValidation() {
        console.log('=== VALIDATION DEBUG ===');
        console.log('Form valid:', form.checkValidity());
        console.log('Password valid:', validatePassword());
        console.log('Passwords match:', pwd.value === confirmPwd.value);
        console.log('Terms checked:', termsCheckbox.checked);
        console.log('Terms accepted:', termsCheckbox.checked && window.termsAccepted);
        
        const formValid = form.checkValidity() && validatePassword();
        const shouldEnable = formValid && termsCheckbox.checked && window.termsAccepted;
        
        console.log('Should enable button:', shouldEnable);
        registerBtn.disabled = !shouldEnable;
        console.log('Button disabled:', registerBtn.disabled);
    }
    
    // Add debug listeners
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', debugValidation);
        input.addEventListener('change', debugValidation);
    });
    
    // Initial debug
    setTimeout(debugValidation, 1000);
});
