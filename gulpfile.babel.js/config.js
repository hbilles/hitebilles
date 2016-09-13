import path from 'path'
import yargs from 'yargs'
import errorHandler from './lib/errorHandler'

const paths = {
	url: 'http://hitebilles.dev',
	src: './src',
	dist: './dist/public/assets',
	templates: './dist/craft/templates',
	webroot: './dist/public',
	vendor: './node_modules',
}

const svgoConfig = [
	{ removeDimensions: true },
	{ convertPathData: { floatPrecision: 1 } },
	{ cleanupNumericValues: { floatPrecision: 1 } },
	{ sortAttrs: true },
	{ cleanupIDs: false },
]

export default {
	mode: yargs.argv.production ? 'production' : 'development',

	paths,

	output: {
		size: {
			gzip: true,
			showFiles: true,
		},
	},

	browserSync: {
		proxy: paths.url,
	},

	plumber: {
		errorHandler,
	},

	tasks: {
		styles: {
			src: path.join(paths.src, 'scss'),
			dist: path.join(paths.dist, 'css'),
			extensions: ['css', 'scss', 'sass'],
			dependencies: [],
			autoprefixer: {
				browsers: [
					'last 2 versions',
					'ie >= 10',
					'android >= 4',
				],
			},
		},

		scripts: {
			src: path.join(paths.src, 'js'),
			dist: path.join(paths.dist, 'js'),
			files: ['app.js'],
			extensions: ['js', null],
		},

		templates: {
			src: path.join(paths.src, 'templates'),
			dist: paths.templates,
			extensions: ['html', 'php', 'twig'],
		},

		images: {
			src: path.join(paths.src, 'img'),
			dist: path.join(paths.dist, 'img'),
			extensions: ['svg', 'png', 'jpg', 'jpeg', 'gif'],
			optimization: {
				progressive: true,
				multipass: true,
				svgoPlugins: svgoConfig,
			},
		},

		rootfiles: {
			src: path.join(paths.src, 'rootfiles'),
			dist: paths.webroot,
			extensions: ['*'],
			noclean: true,
		},

		webfonts: {
			src: path.join(paths.src, 'webfonts'),
			dist: path.join(paths.dist, 'webfonts'),
			extensions: ['eot', 'svg', 'ttf', 'woff', 'woff2'],
			noclean: true,
		},
	},
}
