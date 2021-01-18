var usernamefield = document.getElementById('username');
if (usernamefield)
	usernamefield.addEventListener('change', validateLength);
var emailfield = document.getElementById('email');
if (emailfield)
	emailfield.addEventListener('change', validateEmail);
var passwordfield = document.getElementById('password');
if (passwordfield) {
	passwordfield.addEventListener('keyup', validatePasswordMatch);
}
var confirmfield = document.getElementById('password-confirm');
if (confirmfield)
	confirmfield.addEventListener('keyup', validatePasswordMatch);

var registerform = document.getElementById('register-form');
if (registerform)
	registerform.addEventListener('submit', submitForm);
var loginform = document.getElementById('login-form');
if (loginform)
	loginform.addEventListener('submit', submitForm);
var changepwdform = document.getElementById('changepwd-form');
if (changepwdform)
	changepwdform.addEventListener('submit', submitForm);
var secureform = document.getElementById('secure-form');
if (secureform)
	secureform.addEventListener('submit', submitForm);

function validateLength()
{
	if (this.value === "")
		return;
	if (this.value.length < this.getAttribute('minlength'))
		this.setCustomValidity('Too short.');
	else
		this.setCustomValidity('');
}

function validateEmail()
{
	if (this.value === "")
		return;
	var form = document.getElementById('register-form');
	if (!form)
		return;
	var url = form.getAttribute('action');
	var data = new FormData();
	data.set('verify', true);
	data.set('email', this.value);
	ajax(url, data, handleEmailValidationResponse);
}

function handleEmailValidationResponse(data)
{
	if (data.invalid) {
		emailfield.setCustomValidity('Invalid email address.');
		return;
	}
	if (data.inuse) {
		emailfield.setCustomValidity('Email address already in use.');
		document.getElementById('account-recovery').classList.add('show');
		return;
	}
	emailfield.setCustomValidity('');
	document.getElementById('account-recovery').classList.remove('show');
}

function validatePasswordMatch()
{
	if (!passwordfield || !confirmfield)
		return;
	if (passwordfield.value === confirmfield.value) {
		confirmfield.setCustomValidity('');
		return;
	}
	confirmfield.setCustomValidity('Passwords do not match.');
}
