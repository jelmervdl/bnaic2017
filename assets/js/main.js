if (!Element.prototype.matches)
	Element.prototype.matches =
		Element.prototype.msMatchesSelector ||
		Element.prototype.webkitMatchesSelector;

function $(selector, callback, element) {
	if (element === undefined) element = document.body;
	return Array.prototype.map.call(element.querySelectorAll(selector), callback);
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
	rootElement.addEventListener(event, function(e) {
		var element = e.target;

		while (element && !element.matches(selector))
			element = element.parentNode;

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

$('.schedule a[href^="program.html#"]', function(link) {
	var url = link.href.match(/^(.+?program.html)(#[a-z0-9_-]+)$/);
	link.addEventListener('click', function(e) {
		if (e.shiftKey || e.metaKey)
			return;

		// Prevent default behaviour
		e.preventDefault();

		// Open a modal popup window
		popup(function(root) {
			// Start with a loading message in the popup
			var loading = $h('span', {'className': 'loading-message'}, ['Loading…']);
			root.appendChild(loading);

			// Fetch the actual information, and replace the loading message
			// with that content.
			fetchSelector(url[1], url[2], function(content) {
				root.removeChild(loading);
				root.appendChild(content);
			});
		});
	});
});

fetchSelector('program.html', '.program', function(program) {
	$('.schedule a[href^="program.html#"]:empty', function(link) {
		var url = link.href.match(/^(.+?program.html)(#[a-z0-9_-]+)$/);
		$(url[2], function(programItem) {
			// Move the speaker to a speaker span
			link.appendChild($h('span', {className: 'speaker'}, (
				programItem.querySelector('.speaker .name')
					? programItem.querySelector('.speaker .name')
					: programItem.querySelector('.speaker')).textContent));
			// Same for the title of the presentation/paper
			link.appendChild($h('span', {className: 'title'}, programItem.querySelector('.title').textContent));
			// Finally add a title attribute 
			link.title = link.querySelector('.speaker').textContent + '\n' + link.querySelector('.title').textContent;
		}, program);

		if (link.matches(':empty'))
			link.appendChild($h('span', {'className': 'error'}, ['Missing info for ' + url[2]]));
	});
});
