var container = document.querySelector('.contact'),
	canvas = document.querySelector('.contact__canvas'),
	ctx = canvas.getContext('2d'),
	w,
	h

function updateCanvasSize() {
	w = window.innerWidth
	h = container.offsetHeight
	canvas.width = w * 2
	canvas.height = h * 2
}

updateCanvasSize()
	
var particles = [],
	patriclesNum = Math.round(w / 25)

function findDistance(p1,p2) {  
	return Math.sqrt( Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2) )
}

window.requestAnimFrame = (function() {
	return  window.requestAnimationFrame       ||
			window.webkitRequestAnimationFrame ||
			window.mozRequestAnimationFrame    ||
			function( callback ) {
				window.setTimeout(callback, 1000 / 60)
			}
})()

window.onresize = updateCanvasSize

function Factory() {  
	this.x    = Math.round( Math.random() * w)
	this.y    = Math.round( Math.random() * h)
	this.rad  = 1.5
	this.vx   = Math.random() * .5 - .25
	this.vy   = Math.random() * -.6
}
	 
function draw() {
	ctx.clearRect(0, 0, w, h)
	ctx.globalCompositeOperation = 'multiply'
	ctx.lineWidth = .75

	for (var i = 0; i < patriclesNum; i++) {
		var temp = particles[i]
		var maxDist = 160
		 
		for (var j = 0; j < patriclesNum; j++) {
			if (i !== j) {
				var temp2 = particles[j]
				var dist = findDistance(temp, temp2)
				var rgba = 'rgba(50, 50, 50, ' + (1 - dist / maxDist - .05) + ')'
				
				if (dist < maxDist) {
					ctx.strokeStyle = rgba
					ctx.strokeStyle = rgba
					ctx.beginPath()
					ctx.moveTo(temp.x, temp.y)
					
					if (temp.x < temp2.x) {
						ctx.quadraticCurveTo(temp2.x, temp.y, temp2.x, temp2.y)
					} else {
						ctx.lineTo(temp2.x, temp2.y)
					}

					ctx.stroke()
				}
			}
		}

		ctx.fillStyle = 'rgba(50, 50, 50, .45)';
		ctx.strokeStyle = ctx.fillStyle;

		ctx.beginPath();
		ctx.arc(temp.x, temp.y, temp.rad, 0, Math.PI * 2, true);
		ctx.fill();
		ctx.closePath();

		temp.x += temp.vx
		temp.y += temp.vy
		
		if (temp.x > w) temp.x = 0
		if (temp.x < 0) temp.x = w
		if (temp.y > h) temp.y = 0
		if (temp.y < 0) temp.y = h
	}
}

(function init() {
	// set the canvas scale for retina devices
	ctx.scale(2,2)
	
	for (var i = 0; i < patriclesNum; i++) {
		particles.push(new Factory)
	}
})();

(function loop() {
	draw()
	requestAnimFrame(loop)
})()
