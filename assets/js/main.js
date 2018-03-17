if (!Element.prototype.matches)
	Element.prototype.matches =
		Element.prototype.msMatchesSelector ||
		Element.prototype.webkitMatchesSelector;

function $(selector, callback, element) {
	if (element === undefined) element = document.body;
	return Array.prototype.map.call(element.querySelectorAll(selector), callback);
}

function $parent(child, selector) {
	while (child && !child.matches(selector))
		child = child.parentElement;

	return child;
}

function $previous(sibling, selector) {
	while (sibling && !sibling.matches(selector))
		sibling = sibling.previousElementSibling;

	return sibling;
}

function $h(tagName, properties, content) {
	var el = document.createElement(tagName);

	Object.keys(properties).forEach(function(key) {
		el[key] = properties[key];
	});

	if (!Array.isArray(content))
		content = [content];

	content.forEach(function(child) {
		try {
			el.appendChild(child);
		} catch (e) {
			el.appendChild(document.createTextNode(child));
		}
	});

	return el;
}

function watch(rootElement, event, selector, callback) {
	return rootElement.addEventListener(event, function(e) {
		var element = e.target;

		while (element && !element.matches(selector))
			element = element.parentElement;

		if (element)
			callback.call(element, e);
	});
}

function fetchSelector(url, selector, callback) {
	var request = new XMLHttpRequest();
	request.open('GET', url);
	request.responseType = 'document';
	request.onload = function() {
		$(selector, callback, this.responseXML);
	};
	request.send();
}

function popup(constructor) {
	var content = $h('div', {'className': 'popup-content'}, []);

	var closeButton = $h('button', {
		'className': 'close-button',
		'title': 'Close popup'
	}, ['✖']);

	var root = $h('div', {'className': 'popup'}, [
		$h('div', {'className': 'popup-window'}, [
			content,
			closeButton
		])
	]);

	function close() {
		document.body.removeChild(root);
	}
	
	closeButton.addEventListener('click', function(e) {
		close();
	});

	root.addEventListener('click', function(e) {
		if (e.target === root)
			close();
	});

	root.addEventListener('keypress', function(e) {
		if (e.keyCode == 27)
			close();
	})

	document.body.appendChild(root);

	constructor(content);

	return root;
}

