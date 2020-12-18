var navbar = document.querySelector('header + nav');
var navbaroffset = navbar.offsetTop;

window.onscroll = function() {
	var sidebar = document.querySelector('aside');
	if (window.pageYOffset >= navbaroffset) {
		navbar.classList.add('sticky');
		if (sidebar)
			sidebar.classList.add('sticky');
	} else {
		navbar.classList.remove('sticky');
		if (sidebar)
			sidebar.classList.remove('sticky');
	}
};

window.onclick = function(event) {
	var modal = document.getElementById('modal-box');
	if (event.target == modal)
		closeModal();
};

function getCSRFToken()
{
	var token = document.querySelector('input[name="csrftoken"]');
	if (!token)
		return null;
	return token.value;
}

function updateCSRFToken(token)
{
	var fields = document.querySelectorAll('input[name="csrftoken"]');
	for (var i = 0; i < fields.length; i++)
		fields[i].value = token;
}

var ajaxlock = false;

function ajax(url, data, handler)
{
	if (ajaxlock)
		return;
	ajaxlock = true;
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState !== 4)
			return;
		ajaxlock = false;
		if (this.status === 200) {
			try {
				var data = JSON.parse(this.responseText);
			} catch (e) {
				return;
			}
			if (!data || typeof data !== 'object')
				return;
			if (data.redirect) {
				location.assign(data.redirect);
				return;
			}
			updateCSRFToken(data.csrftoken);
			if (data.modalhtml) {
				showModal(data.modalhtml);
				return;
			}
			if (data.html) {
				var main = document.querySelector('main');
				if (main)
					main.innerHTML = data.html;
			}
			if (handler)
				handler(data);
		}
	};
	xhttp.open('POST', url, true);
	data.set('ajax', true);
	if (!data.has('csrftoken'))
		data.set('csrftoken', getCSRFToken());
	xhttp.send(data);
}

var modalshown = false;

function showModal(content)
{
	var modal = document.getElementById('modal-box');
	if (!modal)
		return;
	modal.innerHTML = content;
	modal.classList.add('show');
	modalshown = true;
	var closebtn = document.getElementById('modal-close');
	if (closebtn)
		closebtn.addEventListener('click', closeModal);
	var form = document.getElementById('modal-form');
	if (form)
		form.addEventListener('submit', submitForm);
}

function closeModal(event)
{
	var modal = document.getElementById('modal-box');
	if (!modal)
		return;
	modal.classList.remove('show');
	modalshown = false;
	var closebtn = document.getElementById('modal-close');
	if ((event && event.target.hasAttribute('data-reload'))
		|| (closebtn && closebtn.hasAttribute('data-reload')))
		location.reload();
}

function submitForm(event)
{
	event.preventDefault();
	var data = new FormData(this);
	if (modalshown)
		showModalSpinner();
	ajax(this.getAttribute('action'), data);
}

function showModalSpinner()
{
	showModal('<div id="modal-content"><div class="spinner"></div></div>');
}
