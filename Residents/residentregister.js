$(document).ready(function() {
    // Initialize datepicker
    $("#birthdate").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: "mm/dd/yy",
        maxDate: 0,
        onSelect: function(selectedDate) {
            calculateAge(selectedDate);
        }
    });

    // Form elements
    const form = $('#registrationForm');
    const password = $('input[name="Password"]');
    const confirmPassword = $('input[name="confirm_password"]');
    const registerBtn = $('#registerBtn');
    const termsCheckbox = $('#accept_terms');
    const modal = $('#termsModal');
    const acceptBtn = $('#acceptBtn');
    const declineBtn = $('#declineBtn');
    const termsContent = $('.terms-content');
    let scrolled = false;

    // Verification code function
    function generateVerificationCode() {
        return Math.floor(100000 + Math.random() * 900000).toString();
    }

    // Email validation
    $('input[name="email"]').on('input', function() {
        const email = $(this).val();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const isValid = emailRegex.test(email);
        
        $(this).toggleClass('error-field', email && !isValid);
        $(this).siblings('.error-message').toggle(email && !isValid);
    });

    // Password strength checker
    function checkPasswordStrength(pwd) {
        let strength = 0;
        let suggestions = [];
        
        // Length check
        if (pwd.length >= 8 && pwd.length <= 10) strength++;
        else suggestions.push("8-10 characters");
        
        // Character variety checks
        if (/[a-z]/.test(pwd)) strength++;
        else suggestions.push("Lowercase letter");
        
        if (/[A-Z]/.test(pwd)) strength++;
        else suggestions.push("Uppercase letter");
        
        if (/\d/.test(pwd)) strength++;
        else suggestions.push("Number");
        
        if (/[!@#$%^&*()_+\-=[\]{};':\"\\|,.<>\/?]/.test(pwd)) strength++;
        else suggestions.push("Special character");
        
        return { strength, suggestions };
    }

   // Password strength display
password.on('input', function() {
    const pwd = $(this).val();
    const strengthResult = checkPasswordStrength(pwd);

    if (pwd.length > 0) {
        let message = '';
        if (strengthResult.strength >= 5) {
            message = '✅ Strong password';
        } else if (strengthResult.strength >= 3) {
            message = '⚠️ Medium password';
        } else {
            message = '⚠️ Weak password';
        }

        if (strengthResult.suggestions.length > 0) {
            message += '\nSuggestions: ' + strengthResult.suggestions.join(', ');
        }

        // Show as tooltip
        $(this).attr('title', message).tooltip({
            'track': true,
            'show': { 'delay': 0 },
            'hide': { 'delay': 0 }
        });
    } else {
        $(this).attr('title', '');
    }

    updateRegister();
});

// Password confirmation validation
confirmPassword.on('input', function() {
    const pwd = password.val();
    const confirmPwd = $(this).val();
    
    if (confirmPwd.length > 0) {
        if (pwd !== confirmPwd) {
            $(this).attr('title', 'Passwords do not match').tooltip({
                'track': true,
                'show': { 'delay': 0 },
                'hide': { 'delay': 0 }
            }).tooltip('open');
            $(this).addClass('error-field');
        } else {
            $(this).tooltip('destroy').removeAttr('title');
            $(this).removeClass('error-field');
        }
    } else {
        $(this).tooltip('destroy').removeAttr('title');
        $(this).removeClass('error-field');
    }
    
    updateRegister();
});

/// Field validation with tooltips
$('input[required]').on('blur', function() {
    if (!$(this).val()) {
        $(this).attr('title', 'Must fill this field').tooltip({
            'track': true,
            'show': { 'delay': 0 },
            'hide': { 'delay': 0 }
        }).tooltip('open');
        $(this).focus();
    } else {
        $(this).tooltip('destroy').removeAttr('title');
    }
});

// Hide tooltip when user starts typing
$('input[required]').on('focus', function() {
    if ($(this).val()) {
        $(this).tooltip('destroy').removeAttr('title');
    }
});

    // Terms modal handlers
    termsCheckbox.on('change', function() {
    if (termsCheckbox.is(':checked')) {
        modal.css('display', 'block');
        acceptBtn.prop('disabled', true).css('background', '#ccc');
        termsContent.scrollTop(0);
        scrolled = false;
    } else {
        modal.css('display', 'none');
    }
    updateRegister();
});

termsContent.on('scroll', function() {
    if (termsContent[0].scrollHeight - termsContent.scrollTop() <= termsContent.outerHeight() + 10) {
        scrolled = true;
        acceptBtn.prop('disabled', false).css('background', '#014A7F');
    }
});

acceptBtn.on('click', function() {
    if (scrolled) {
        modal.css('display', 'none');
        updateRegister();
    }
});

// Update register button state
function updateRegister() {
    const allRequiredFilled = $('input[required]').filter(function() {
        return $(this).val().trim() !== '';
    }).length === $('input[required]').length;
    
    const termsAccepted = termsCheckbox.is(':checked');
    const passwordsMatch = password.val() === confirmPassword.val() || confirmPassword.val() === '';
    const age = parseInt($('#age').val()) || 0;
    const ageValid = age > 0;
    
    if (allRequiredFilled && termsAccepted && passwordsMatch && ageValid) {
        registerBtn.prop('disabled', false).css('background', '#014A7F');
    } else {
        registerBtn.prop('disabled', true).css('background', '#ccc');
    }
}

    declineBtn.on('click', function() {
        modal.css('display', 'none');
        termsCheckbox.prop('checked', false);
        updateRegister();
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target === modal[0]) {
            modal.css('display', 'none');
            termsCheckbox.prop('checked', false);
            updateRegister();
        }
    });

    // Update register button on input changes
    $('input,select').on('input change', updateRegister);
    termsCheckbox.on('change', updateRegister);
    updateRegister();

    // Form submission

// Form submission
if (form.length) {
    form.on('submit', function(e) {
        e.preventDefault();
        
        // Show loading animation
        registerBtn.addClass('loading').prop('disabled', true);
        
        // Send registration data via AJAX
        $.ajax({
            url: 'residentRegisterProcess.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // Hide loading animation
                registerBtn.removeClass('loading').prop('disabled', false);
                
                if (response.success) {
                    // Show success popup
                    $('#successPopup').css('display', 'block');
                } else {
                    alert(response.message || 'Registration failed. Please try again.');
                }
            },
            error: function() {
                // Hide loading animation
                registerBtn.removeClass('loading').prop('disabled', false);
                alert('An error occurred. Please try again.');
            }
        });
        
        return false;
    });
}

    // Calculate age function
    function calculateAge(birthdate) {
        const birthDate = new Date(birthdate);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age < 0) {
            age = 0;
        }
        $('#age').val(age);
        $('#age_display').val(age);
    }

    // Terms content
    termsContent.html(`
        <h3>Terms and Conditions for Using the Barangay New Era Website</h3>
        
        <p><strong>1. Acceptance of Terms</strong><br>
        By accessing and using the official website of Barangay New Era, Sampaloc V, Dasmariñas, Cavite, you agree to comply with these Terms and Conditions. If you do not agree, please discontinue using the site.</p>
        
        <p><strong>2. Purpose of the Website</strong><br>
        This website is maintained by Barangay New Era to:
        <ul>
            <li>Provide accurate and updated information about Barangay programs, announcements, and services;</li>
            <li>Allow residents to request barangay-related documents and forms online;</li>
            <li>Offer digital support to improve service efficiency and accessibility.</li>
        </ul></p>
        
        <p><strong>3. Accuracy of Information</strong><br>
        Barangay New Era strives to keep all website content updated and accurate. However, the Barangay does not guarantee the completeness, accuracy, or timeliness of all information displayed.</p>
        
        <p><strong>4. Online Document Requests</strong><br>
        <ul>
            <li>Requests for documents such as Barangay Clearance, Certificate of Residency, Certificate of Indigency, and other barangay forms are processed based on the information you provide.</li>
            <li>All requests are subject to verification by Barangay New Era personnel.</li>
            <li>The Barangay reserves the right to approve, deny, or request additional information before releasing any document.</li>
            <li>Some documents may still require personal appearance for identity and residency validation.</li>
            <li>Processing times may vary depending on request volume, completeness of requirements, and verification results.</li>
        </ul></p>
        
        <p><strong>5. User Responsibilities</strong><br>
        By using this website, you agree to:
        <ul>
            <li>Provide accurate, truthful, and complete information in all forms and submissions;</li>
            <li>Avoid submitting fraudulent data or impersonating another individual;</li>
            <li>Refrain from activities that may disrupt website operations, including uploading harmful files or malicious content;</li>
            <li>Use the website solely for legitimate and authorized barangay-related transactions.</li>
        </ul></p>
        
        <p><strong>6. Privacy and Data Protection</strong><br>
        <ul>
            <li>Personal information provided through this website will be collected, stored, and used only for official transactions of Barangay New Era.</li>
            <li>The Barangay implements reasonable technical and organizational measures to protect your data, in compliance with the Data Privacy Act of 2012.</li>
            <li>By submitting information through this website, you consent to its processing for verification, documentation, and service delivery.</li>
        </ul></p>
        
        <p><strong>7. Website Availability</strong><br>
        <ul>
            <li>Barangay New Era does not guarantee uninterrupted or error-free access to the website.</li>
            <li>Temporary downtime may occur due to maintenance, technical issues, or circumstances beyond the Barangay's control.</li>
        </ul></p>
        
        <p><strong>8. Fees and Payments</strong><br>
        <ul>
            <li>Certain barangay documents may require standard fees as approved by the City Government of Dasmariñas or Barangay New Era.</li>
            <li>All applicable fees will be disclosed prior to payment or at the time of document release.</li>
            <li>Payments (including digital payments, if offered) are non-refundable once processing has begun.</li>
        </ul></p>
        
        <p><strong>9. Intellectual Property</strong><br>
        <ul>
            <li>All content on this website—including text, images, forms, graphics, and the Barangay New Era official logo—is the property of Barangay New Era, Sampaloc V, Dasmariñas, Cavite.</li>
            <li>Copying, modifying, or distributing any content without permission is prohibited.</li>
        </ul></p>
        
        <p><strong>10. Limitation of Liability</strong><br>
        Barangay New Era shall not be liable for:
        <ul>
            <li>Inaccuracies or delays in website content;</li>
            <li>Technical issues or downtime affecting website access;</li>
            <li>Damages resulting from the use or inability to use the website;</li>
            <li>Unauthorized access or breaches beyond the Barangay's reasonable control.</li>
        </ul></p>
        
        <p><strong>11. External Links</strong><br>
        If the website includes links to external sites, Barangay New Era is not responsible for the content, privacy practices, or security of those websites.</p>
        
        <p><strong>12. Changes to Terms and Conditions</strong><br>
        <ul>
            <li>Barangay New Era may update these Terms and Conditions at any time without prior notice.</li>
            <li>Continued use of the website means you accept any revised terms.</li>
        </ul></p>
        
        <p><strong>13. Contact Information</strong><br>
        For concerns, inquiries, or assistance regarding online forms or services, you may contact:<br><br>
        Barangay New Era, Sampaloc V, Dasmariñas, Cavite<br>
        Contact Number - 0997645314/(02) 1234 - 5678 <br>
        Hotline - 911 / 8888 <br>
        Email - barangay.newera@email.com <br>
        </p>
    `);

// Success popup handler
window.closeSuccessPopup = function() {
    // Hide popup
    $('#successPopup').css('display', 'none');
    // Redirect to login page
    window.location.href = 'residentVerify.php';
};
});