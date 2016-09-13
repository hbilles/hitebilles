<template>
<div class="showcase"
	:class="showcaseClass">
	<div class="showcase__list">
		<h2 class="showcase__heading">Projects</h2>
		
		<showcase-row
			v-for="group in groupedItems"
			:index="$index"
			:group_size="groupSize"
			:group="group"
			:vp_width="windowWidth"></showcase-row>
	</div>

	<style v-if="activeContent">
		.showcase:before {
			{{showcaseCss}}
		}

		.showcase__list:before {
			{{listCss}}
		}
	</style>
</div>
</template>

<script>
import { chunk, debounce } from 'lodash'
import request from 'superagent'
import baseUrl from '../helpers/baseUrl.js'
import mq from '../helpers/media-queries.js'

import ShowcaseRow from './ShowcaseRow.vue'
import store from '../vuex/store'
import { getActiveContent } from '../vuex/getters'

export default {
	name: 'Showcase',
	
	created: function() {
		this.fetchShowcaseData()
		this.getWindowWidth()

		window.onresize = debounce(this.getWindowWidth, 100)
	},

	ready: function() {

	},

	components: {
		'showcase-row': ShowcaseRow
	},

	data() {
		return {
			showcase: [],
			items: [],
			windowWidth: Number,
			nextBgUpdate: 'list',
			showcaseCss: '',
			listCss: '',
		}
	},

	computed: {
		isMobile: function() {
			return this.windowWidth < mq.px.medium ? true : false
		},

		itemsX2: function() {
			return chunk(this.items, 2)
		},

		itemsX3: function() {
			return chunk(this.items, 3)
		},

		groupedItems: function() {
			return this.isMobile ? this.itemsX2 : this.itemsX3
		},

		groupSize: function() {
			return this.groupedItems === this.itemsX2 ? 2 : 3
		},

		showcaseClass: function() {
			return this.activeContent.id ? 'showcase--loaded' : ''
		},

		activeGradient: function() {
			return 'background-image: linear-gradient(to bottom, ' + this.activeContent.bgColor1 + ' 0%, '
				+ this.activeContent.bgColor2 + ' 100%);'
		}
	},

	watch: {
		'activeContent': function(val, oldVal) {
			if (this.nextBgUpdate === 'list') {
				this.listCss = this.activeGradient + 'opacity: 1;'
				this.nextBgUpdate = 'showcase'
			} else {
				this.showcaseCss = this.activeGradient
				this.listCss = 'opacity: 0;'
				this.nextBgUpdate = 'list'
			}
		}
	},

	methods: {
		fetchShowcaseData: function() {
			var self = this
			request
				.get(baseUrl() + 'api/projects/projects.json')
				.set('Accept', 'application/json')
				.end(function(err, res) {
					self.items = res.body.data
				});
		},

		getWindowWidth: function() {
			this.windowWidth = document.body.clientWidth
		},
	},

	store,

	vuex: {
		getters: {
			activeContent: getActiveContent
		},
	}
}

</script>

<style lang="scss">
	
</style>