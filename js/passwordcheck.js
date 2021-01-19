document.getElementById('password').addEventListener('keyup', validatePassword);
document.getElementById('username').addEventListtener('keyup', validatePassword);
document.getElementById('email').addEventListener('keyup', validatePassword);

function validatePassword()
{
	var minstrength = 3;
	var passwordfield = document.getElementById('password');
	if (passwordfield.hasAttribute('data-minpwdstrength'))
		minstrength = parseInt(passwordfield.getAttribute('data-minpwdstrength'));

	var username = document.getElementById('username').value;
	var email = document.getElementById('email').value;
	var password = passwordfield.value;

	if (!password || password.length === 0) {
		setStrength(null, minstrength);
		return;
	}

	var result = zxcvbn(password, [username, email]);
	if (!result) {
		setStrength(null, minstrength);
		return;
	}

	if (password.length < 8 || result.score === 0) {
		var feedback = result.feedback;
		if (!feedback.warning || feedback.warning.length === 0)
			feedback.warning = 'Password too weak.';
		result = {score: 0, feedback : feedback};
	}

	setStrength(result, minstrength);
}

function setStrength(result = null, minstrength = 3)
{
	var score = 0;
	var message = '';
	var hints = [];
	if (result) {
		score = result.score;
		message = result.feedback.warning;
		hints = result.feedback.suggestions;
	}

	var strengthbar = document.getElementById('strength-bar');
	if (!strengthbar) {
		strengthbar = document.createElement('div');
		strengthbar.setAttribute('id', 'strength-bar');
		var pwdfield = document.getElementById('password');
		pwdfield.parentNode.insertBefore(strengthbar, pwdfield.nextSibling);
	}

	strengthbar.className = '';
	strengthbar.classList.add('pwd-strength-' + score);

	while (strengthbar.hasChildNodes())
		strengthbar.firstChild.remove();

	if (result)
		strengthbar.appendChild(document.createElement('span'));

	if (score >= minstrength)
		message = '';
	else if (!message)
		message = 'Password too weak.'

	createMessage(message);
	createHints(hints);
}

function createMessage(message = "")
{
	document.getElementById("password").setCustomValidity(message);
	document.getElementById("password").reportValidity();
}

function createHints(hints = [])
{
	var listChild = document.getElementById('pwd-strength-hints');
	if (!listChild) {
		listChild = document.createElement('ul');
		listChild.setAttribute('id', 'pwd-strength-hints');
		var strengthbar = document.getElementById('strength-bar');
		strengthbar.parentNode.insertBefore(listChild, strengthbar.nextSibling);
	}
	while (listChild.hasChildNodes())
		listChild.firstChild.remove();
	hints.forEach(function(hint) {
		createHint(hint);
	});
}

function createHint(hint)
{
	var hintElem = document.createElement('li');
	var hintText = document.createTextNode(hint);
	hintElem.appendChild(hintText);
	document.getElementById('pwd-strength-hints').appendChild(hintElem);
};
