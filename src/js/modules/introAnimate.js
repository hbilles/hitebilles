var introContent = document.querySelector('.intro__content')
var delay

function delayAction() {
	delay = window.setTimeout(doIt, 1500)
}

function doIt() {
	introContent.classList.add('intro__content--fade-in')
	window.clearTimeout(delay)
}

delayAction()
