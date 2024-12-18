document.addEventListener("DOMContentLoaded", function() {
  const signinForm = document.querySelector(".signin-form");
  const signupForm = document.querySelector(".signup-form");
  const forgotForm = document.querySelector(".forgot-form");
  const signinBtn = document.querySelector('.signin-btn');
  const signupBtn = document.querySelector('.signup-btn');
  
  // Add active class for styling
  signinBtn.classList.add('active');

  // Default state
  signupForm.style.display = 'none';
  forgotForm.style.display = 'none';
  signinForm.style.display = 'block';

  // Sign In button
  signinBtn.addEventListener('click', function() {
      signinForm.style.display = 'block';
      signupForm.style.display = 'none';
      forgotForm.style.display = 'none';
      signinBtn.classList.add('active');
      signupBtn.classList.remove('active');
  });

  // Sign Up button
  signupBtn.addEventListener('click', function() {
      signinForm.style.display = 'none';
      signupForm.style.display = 'block';
      forgotForm.style.display = 'none';
      signupBtn.classList.add('active');
      signinBtn.classList.remove('active');
  });

  // Forgot password link
  document.querySelector('.forgot-btn').addEventListener('click', function(e) {
      e.preventDefault();
      signinForm.style.display = 'none';
      signupForm.style.display = 'none';
      forgotForm.style.display = 'block';
      signinBtn.classList.remove('active');
      signupBtn.classList.remove('active');
  });

  // Handle URL parameters for error states
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('register-error') || urlParams.has('register-success')) {
      signupForm.style.display = 'block';
      signinForm.style.display = 'none';
      forgotForm.style.display = 'none';
      signupBtn.classList.add('active');
      signinBtn.classList.remove('active');
  } else if (urlParams.has('forgot-error')) {
      forgotForm.style.display = 'block';
      signinForm.style.display = 'none';
      signupForm.style.display = 'none';
      signinBtn.classList.remove('active');
      signupBtn.classList.remove('active');
  }
});

// This function is used by your PHP error handling
function register() {
  const signinForm = document.querySelector(".signin-form");
  const signupForm = document.querySelector(".signup-form");
  const forgotForm = document.querySelector(".forgot-form");
  const signinBtn = document.querySelector('.signin-btn');
  const signupBtn = document.querySelector('.signup-btn');
  
  signupForm.style.display = 'block';
  signinForm.style.display = 'none';
  forgotForm.style.display = 'none';
  signupBtn.classList.add('active');
  signinBtn.classList.remove('active');
}