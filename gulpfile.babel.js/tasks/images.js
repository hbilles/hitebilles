import config from '../config'

import browserSync from 'browser-sync'
import cached from 'gulp-cached'
import gulp from 'gulp'
import imagemin from 'gulp-imagemin'
import path from 'path'
import plumber from 'gulp-plumber'
import size from 'gulp-size'

const paths = {
	src: path.join(config.tasks.images.src, `/**/*.{${config.tasks.images.extensions}}`),
	dist: config.tasks.images.dist,
}

// Optimize Images
gulp.task('images', () => {
	return gulp.src(paths.src)
		.pipe(plumber(config.plumber))
		.pipe(cached('images'))
		.pipe(imagemin(config.tasks.images.optimization))
		.pipe(gulp.dest(paths.dist))
		.pipe(size(config.output.size))
		.pipe(browserSync.stream())
})
