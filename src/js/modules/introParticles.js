var canvas = document.querySelector('.intro__canvas'),
	ctx = canvas.getContext('2d'),
	generator = {},
	spawns = [],
	maxSpawnTicks = 500,
	tock = false,
	frame = 0,
	hScale,
	sScale,
	w,
	h

updateCanvasSize()



function updateCanvasSize() {
	w = window.innerWidth
	h = window.innerHeight
	canvas.width = w * 2
	canvas.height = h * 2

	hScale = 360 / w
	sScale = 100 / h
}

function findDistance(p1,p2) {  
	return Math.sqrt( Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2) )
}

function findLength(x, y) {  
	return Math.sqrt( Math.pow(x, 2) + Math.pow(y, 2) )
}

window.requestAnimFrame = (function() {
	return  window.requestAnimationFrame       ||
			window.webkitRequestAnimationFrame ||
			window.mozRequestAnimationFrame    ||
			function( callback ) {
				window.setTimeout(callback, 1000 / 60)
			}
})()

function factory() {
	var angle = Math.atan2(generator.vy, generator.vx) + 90
	var length = findLength(generator.vy, generator.vx)

	this.x     = generator.x
	this.y     = generator.y
	this.vx    = Math.cos(angle) * length
	this.vy    = Math.sin(angle) * length
	this.rad   = .5
	this.ticks = 0
}

function draw() {
	ctx.globalCompositeOperation = 'lighter'
	ctx.lineWidth = .75

	generator.x += generator.vx
	generator.y += generator.vy

	if (generator.x >= w || generator.x <= 0) {
		generator.vx = generator.vx * -1
	}

	if (generator.y >= h || generator.y <= 0) {
		generator.vy = generator.vy * -1
	}
	
	if (tock && frame < 1000) {
		spawns.push(new factory)
	}

	for (var i = 0; i < spawns.length; i++) {
		var spawn = spawns[i]

		if (spawn.ticks < maxSpawnTicks) {
			spawn.x += spawn.vx
			spawn.y += spawn.vy

			var hsl = 'hsl(' + spawn.x * hScale + ',' + spawn.y * sScale + '%,' + (100 - (spawn.ticks / maxSpawnTicks * 50) - 50) + '%)'
			ctx.fillStyle = hsl
			ctx.beginPath()
			ctx.moveTo(spawn.x, spawn.y)
			ctx.arc(spawn.x, spawn.y, spawn.rad, 0, Math.PI * 2, true)
			ctx.fill()
			ctx.closePath()

			spawn.vx = ((Math.random() / 4) - .125) + spawn.vx
			spawn.vy = ((Math.random() / 4) - .125) + spawn.vy

			spawn.ticks++
		}
	}

	generator.vx = ((Math.random() / 4) - .125) + generator.vx
	generator.vy = ((Math.random() / 4) - .125) + generator.vy

	if (generator.vx > 2 || generator.x < -2) {
		generator.vx = (Math.random() * 2) - 1
	}

	if (generator.vy > 2 || generator.vy < -2) {
		generator.vy = (Math.random() * 2) - 1
	}

	tock = !tock
	frame++
}

(function init() {
	// set the canvas scale for retina devices
	ctx.scale(2,2)

	generator.x = Math.round(Math.random() * w)
	generator.y = Math.round(Math.random() * h)
	generator.vx = 0
	generator.vy = 0
})();

(function loop() {
	draw()
	if (frame < 1500) {
		requestAnimFrame(loop)
	}
})()
