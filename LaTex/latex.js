(function() {
	var $ = document.querySelectorAll.bind(document);
	var latex = $('code.lang-laTex, code.lang-latex, code.lang-tex');
	for (var i = 0, l = latex.length; i < l; i++) {
		var node = latex[i];
		var latex_image = document.createElement("img");
		latex_image.src = "http://latex.codecogs.com/png.latex?"+ node.innerHTML;

		var parent = node.parentNode;
		parent.insertBefore(latex_image, node);
		parent.removeChild(node);
	}
})();