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

function ajax(url, data, handler)
{
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState !== 4)
			return;
		if (this.status === 200) {
			var data = JSON.parse(this.responseText);
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
	//xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	//data += '&csrftoken=' + encodeURIComponent(getCSRFToken());
	data.set('ajax', true);
	data.set('csrftoken', getCSRFToken());
	xhttp.send(data);
}

function showModal(content)
{
	var modal = document.getElementById('modal-box');
	if (!modal)
		return;
	modal.innerHTML = content;
	modal.classList.add('show');
	var closebtn = document.getElementById('modal-close');
	if (closebtn)
		closebtn.addEventListener('click', closeModal);
	var form = document.getElementById('modal-form');
	if (form)
		form.addEventListener('submit', submitForm);
}

function closeModal()
{
	var modal = document.getElementById('modal-box');
	if (!modal)
		return;
	modal.classList.remove('show');
}

function submitForm(event)
{
	event.preventDefault();
	/*var data = '';
	var elements = this.querySelectorAll('input');
	for (var i = 0; i < elements.length; i++) {
		if (elements[i].name === 'csrftoken')
			continue;
		if (elements[i].type === 'checkbox' && elements[i].checked == false)
			continue;
		data += elements[i].name + '=' + encodeURIComponent(elements[i].value) + '&';
	}
	data = data.slice(0, -1);*/
	var data = new FormData(this);
	ajax(this.getAttribute('action'), data);
}
