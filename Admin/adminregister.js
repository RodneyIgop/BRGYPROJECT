document.addEventListener('DOMContentLoaded',()=>{
  const navbar=document.querySelector('.navbar');
  const burger=document.querySelector('.burger');
  const navLinks=document.querySelector('.nav-links');
  const pwd=document.getElementById('password');
  const confirmPwd=document.getElementById('confirm_password');
  const form=document.getElementById('registerForm');
  const termsCheckbox=$('#accept_terms');
  const modal=$('#termsModal');
  const acceptBtn=$('#acceptBtn');
  const declineBtn=$('#declineBtn');
  const termsContent=$('.terms-content');
  let scrolled=false;
  const birthdateInput=$('#birthdate');
  const ageInput=$('#age');
  const emailInput=$('#email');
  const contactInput=$('#contact');
  const registerBtn=$('#registerBtn');
  // enforce numeric-only input
  contactInput.on('input',()=>{
    const digits=contactInput.val().replace(/\D/g,'');
    contactInput.val(digits.slice(0,11));
  });

  if(burger){
    const closeMenu=()=>{navbar.classList.remove('open');burger.setAttribute('aria-expanded','false');};
    const toggleMenu=()=>{const isOpen=navbar.classList.toggle('open');burger.setAttribute('aria-expanded',String(isOpen));};
    burger.addEventListener('click',e=>{e.stopPropagation();toggleMenu();});
    navLinks&&navLinks.addEventListener('click',e=>{if(e.target.tagName==='A')closeMenu();});
    document.addEventListener('click',e=>{if(!navbar.contains(e.target))closeMenu();});
    window.addEventListener('resize',()=>{if(window.matchMedia('(min-width: 769px)').matches)closeMenu();});
  }
//   document.addEventListener("DOMContentLoaded", function() {
//     const hamburger = document.getElementById("hamburger");
//     const navLinks = document.getElementById("navLinks");

//     hamburger.addEventListener("click", function() {
//         navLinks.classList.toggle("active");
//     });
// });

  // Initialize datepicker
  if(birthdateInput.length){
    birthdateInput.datepicker({
      changeMonth:true,
      changeYear:true,
      yearRange:"1900:+0",
      maxDate:new Date(),
      dateFormat:'mm/dd/yy',
      onSelect:function(dateText){
        calculateAge(dateText);
      }
    });
  }

  function calculateAge(birthdate){
    const birthDate=new Date(birthdate);
    const today=new Date();
    let age=today.getFullYear()-birthDate.getFullYear();
    const m=today.getMonth()-birthDate.getMonth();
    if(m<0||(m===0&&today.getDate()<birthDate.getDate())){age--;}
    ageInput.val(age);
  }

  // Terms modal handlers
  termsCheckbox.on('change',()=>{
    if(termsCheckbox.is(':checked')){
      modal.show();
      acceptBtn.prop('disabled',true).css('background','#ccc');
      termsContent.scrollTop(0);
      scrolled=false;
    }
  });
  termsContent.on('scroll',()=>{
    if(termsContent[0].scrollHeight-termsContent.scrollTop()<=termsContent.outerHeight()+10){
      scrolled=true;
      acceptBtn.prop('disabled',false).css('background','#014A7F');
    }
  });
  acceptBtn.on('click',()=>{if(scrolled){modal.hide();updateRegister();}});
  declineBtn.on('click',()=>{modal.hide();termsCheckbox.prop('checked',false);updateRegister();});

  function validatePassword(){
    const regex=/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':\"\\|,.<>/?]).{8,25}$/;
    return regex.test(pwd.value);
  }
  function updateRegister(){
    const formValid=form.checkValidity()&&validatePassword();
    registerBtn.prop('disabled',!(formValid && termsCheckbox.is(':checked')));
  }
  $('input,select').on('input change',updateRegister);
  termsCheckbox.on('change',updateRegister);
  updateRegister();

  if(form){
    form.addEventListener('submit',e=>{
      e.preventDefault();
      
      if(pwd.value!==confirmPwd.value){
        alert('Passwords do not match');
        confirmPwd.focus();
        return;
      }

      // Submit form via AJAX
      const formData = new FormData(form);
      
      fetch('saveAdminRequest.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          // Auto-redirect to verification page
          window.location.href = 'adminVerify.php';
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your request.');
      });
    });
  }

  // Success popup handler
  window.closeSuccessPopup = function() {
    // Redirect to login page
    window.location.href = 'adminLogin.php';
  };
});
