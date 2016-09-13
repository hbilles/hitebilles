<template>
	<div class="showcase__content">
		<article class="project"
			v-if="show"
			transition="expand">
			<div class="project__in"
				v-if="contentLoaded"
				transition="fade-in">
				<div class="project__in__in">
					<figure class="project__hero">
						<a :href="content.projectUrl" target="_blank">
							<img
								:src="content.imageSmall"
								:srcset="srcset"
								sizes="100vw"
								:alt="content.title + ' Showcase'">
						</a>
					</figure>

					<div class="project__description">
						<div class="project__description__in">
							<ul class="project__meta">
								<li v-if="content.projectDate" class="project__date">
									{{{projectDate}}}
								</li>
								
								<li v-if="content.projectUrl" class="project__url">
									<a :href="content.projectUrl" target="_blank">{{content.title}} website</a>
								</li>
							</ul>

							<div v-if="content.copy" class="project__copy">
								<vue-markdown
									:source="content.copy">{{content.copy}}</vue-markdown>
							</div>

							<div v-if="content.attribution" class="project__attribution">
								<vue-markdown
									:source="content.attribution">{{content.attribution}}</vue-markdown>
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>
	</div>
</template>

<script>
import moment from 'moment'
import VueMarkdown from 'vue-markdown'
import {
	getActiveRowId,
	getActiveContent
} from '../vuex/getters'

export default {
	name: 'ShowcaseContent',

	components: {
		'vue-markdown': VueMarkdown,
	},

	props: {
		row_id: {
			'type': String,
			'required': true,
		},
	},

	data() {
		return {
			content: {}
		}
	},

	computed: {
		projectDate: function() {
			var output = moment(this.content.projectDate).format('MMMM YYYY'),
				w3c    = moment(this.content.projectDate).format('YYYY-MM-DD')

			return '<time datetime="' + w3c + '">' + output + '</time>'
		},

		srcset: function() {
			if (this.content.imageLarge && this.content.imageMedium && this.content.imageSmall) {
				return this.content.imageLarge + ' ' + this.content.large + 'w, '
					+ this.content.imageMedium + ' ' + this.content.medium + 'w, '
					+ this.content.imageSmall  + ' ' + this.content.small + 'w'
			} else {
				return ''
			}
		},

		show: function() {
			if (this.row_id !== this.activeRowId) {
				return false
			} else {
				return true
			}
		},

		contentLoaded: function() {
			return this.content.id ? true : false
		},
	},

	watch: {
		'activeRowId': function(val, oldVal) {
			if (val !== this.row_id) {
				// new activeRowId doesn't match row_id, deactivate!
				this.deactivateContent()
			}
		},

		'activeContent': function(val, oldVal) {
			if (this.row_id === this.activeRowId) {
				this.updateContent()
			}
		}
	},

	methods: {
		deactivateContent: function() {
			// collapse the content container &
			// delete the content
			var self = this,
				delay

			function delayAction() {
				delay = window.setTimeout(doIt, 300)
			}

			function doIt() {
				self.content = {}
				window.clearTimeout(delay)
			}

			delayAction()
		},

		updateContent: function() {
			this.content = this.activeContent
		}
	},

	vuex: {
		getters: {
			activeRowId: getActiveRowId,
			activeContent: getActiveContent
		}
	},
}
</script>
