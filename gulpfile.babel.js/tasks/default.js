import gulp from 'gulp'
import sequence from 'gulp-sequence'

// Default Task
gulp.task('default', sequence(
	'clean',
	[
		'images',
		'rootfiles',
		//'webfonts',
	],
	'styles',
	'scripts',
	'templates'
))
