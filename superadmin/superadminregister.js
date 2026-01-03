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

  if(birthdateInput.length){
    birthdateInput.datepicker({
      changeMonth:true,
      changeYear:true,
      yearRange:"1900:+0",
      maxDate:new Date(),
      dateFormat:'mm/dd/yy',
      onSelect:function(dateText){
        updateAge();
      }
    });
  }

  function parseBirthdate(value){
    if(!value){
      return null;
    }

    try{
      return $.datepicker.parseDate('mm/dd/yy', value);
    }catch(e){
      const d=new Date(value);
      return Number.isNaN(d.getTime()) ? null : d;
    }
  }

  function calculateAgeFromDate(birthDate){
    const today=new Date();
    let age=today.getFullYear()-birthDate.getFullYear();
    const m=today.getMonth()-birthDate.getMonth();
    if(m<0||(m===0&&today.getDate()<birthDate.getDate())){age--;}
    return age;
  }

  function updateAge(){
    const birthDate=parseBirthdate(birthdateInput.val());
    if(!birthDate){
      ageInput.val('');
      return;
    }

    const age=calculateAgeFromDate(birthDate);
    ageInput.val(age>=0 ? age : '');
  }

  birthdateInput.on('change blur', updateAge);
  updateAge();

    termsCheckbox.on('change',()=>{
      if(termsCheckbox.is(':checked')){
        modal.show();
        acceptBtn.prop('disabled',true).css('background','#808080');
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

  // function validatePassword(){
  //   const regex=/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':\"\\|,.<>/?]).{8,25}$/;
  //   return regex.test(pwd.value);
  // }

  function validatePassword() {
    const pwd = document.getElementById('password');
    const password = pwd.value;
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,25}$/;
    return regex.test(password);
  }

  function updateRegister(){
    // Remove all validation - always enable the register button
    registerBtn.prop('disabled', false);
  }

  $('input,select').on('input change',updateRegister);
  termsCheckbox.on('change',updateRegister);
  updateRegister();

  if(form){
    form.addEventListener('submit',e=>{
      // Remove password match validation - let the backend handle it
    });
  }
});

