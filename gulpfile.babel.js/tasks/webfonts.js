import config from '../config'

import gulp from 'gulp'
import path from 'path'

const paths = {
	src: path.join(config.tasks.webfonts.src, '/**/*'),
	dist: config.tasks.webfonts.dist,
}

// Copy web root files to dist
gulp.task('webfonts', () => {
	gulp.src(paths.src)
		.pipe(gulp.dest(paths.dist))
})
