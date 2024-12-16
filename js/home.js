let wrapper = document.querySelector('.wrapper'),
    signUpLink = document.querySelector('.link .signup-link'),
    signInLink = document.querySelector('.link .signin-link');

signUpLink.addEventListener('click', () => {
    wrapper.classList.add('animated-signin');
    wrapper.classList.remove('animated-signup');
});

signInLink.addEventListener('click', () => {
    wrapper.classList.add('animated-signup');
    wrapper.classList.remove('animated-signin');
});

const passwordInput = document.getElementById('pass');
const togglePassword = document.getElementById('toggle');

togglePassword.addEventListener('click', () => {
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        togglePassword.classList.remove('bxs-lock');
        togglePassword.classList.add('bxs-lock-open');
    } else {
        passwordInput.type = 'password';
        togglePassword.classList.remove('bxs-lock-open');
        togglePassword.classList.add('bxs-lock');
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const toggleIcon = document.getElementById("toggle");

    toggleIcon.addEventListener("click", function() {
        toggleIcon.classList.toggle("active");
    });
});

const passwordInput1 = document.getElementById('pass1');
const togglePassword1 = document.getElementById('toggle1');

togglePassword1.addEventListener('click', () => {
    if (passwordInput1.type === 'password') {
        passwordInput1.type = 'text';
        togglePassword1.classList.remove('bxs-lock');
        togglePassword1.classList.add('bxs-lock-open');
    } else {
        passwordInput1.type = 'password';
        togglePassword1.classList.remove('bxs-lock-open');
        togglePassword1.classList.add('bxs-lock');
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const toggleIcon1 = document.getElementById("toggle1");

    toggleIcon1.addEventListener("click", function() {
        toggleIcon1.classList.toggle("active");
    });
});

const passwordInput2 = document.getElementById('confirmpass');
const togglePassword2 = document.getElementById('toggle2');

togglePassword2.addEventListener('click', () => {
    if (passwordInput2.type === 'password') {
        passwordInput2.type = 'text';
        togglePassword2.classList.remove('bxs-lock');
        togglePassword2.classList.add('bxs-lock-open');
    } else {
        passwordInput2.type = 'password';
        togglePassword2.classList.remove('bxs-lock-open');
        togglePassword2.classList.add('bxs-lock');
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const toggleIcon2 = document.getElementById("toggle2");

    toggleIcon2.addEventListener("click", function() {
        toggleIcon2.classList.toggle("active");
    });
});

function closeError() {
	var errorDiv = document.querySelector('.errors');
	errorDiv.style.display = 'none';

	var currentURL = window.location.href;
	var newURL = currentURL.split('?')[0];
	history.replaceState(null, null, newURL);
}