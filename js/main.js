var navbar = document.querySelector("header + nav");
var navbaroffset = navbar.offsetTop;

window.onscroll = function() {
	var sidebar = document.querySelector("aside");
	if (window.pageYOffset >= navbaroffset) {
		navbar.classList.add("sticky");
		if (sidebar)
			sidebar.classList.add("sticky");
	} else {
		navbar.classList.remove("sticky");
		if (sidebar)
			sidebar.classList.remove("sticky");
	}
};
