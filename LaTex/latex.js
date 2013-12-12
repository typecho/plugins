(function(global) {
	var previousLatex = global.latex;

	global.latex = {
		parse: function(mark) {
			if (!mark) {
				return;
			}

			var $ = document.querySelectorAll.bind(document);
			var nodes = $('code.lang-' + mark);
			for (var i = 0, l = nodes.length; i < l; i++) {
				var node = nodes[i];
				var latex_image = document.createElement("img");
				latex_image.src = "http://latex.codecogs.com/png.latex?" + node.innerHTML;
				// replace with image
				var parent = node.parentNode;
				parent.insertBefore(latex_image, node);
				parent.removeChild(node);
			}

		},
		noConflict: function() {
			global.latex = previousLatex;
			return this;
		}
	}

})(this);