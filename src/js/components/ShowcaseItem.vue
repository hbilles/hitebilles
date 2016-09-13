<template>
	<div class="showcase__item"
		:class="itemClass"
		:id="item.slug"
		@click="clickHandler">
		<figure class="showcase__figure">
			<img
				:src="item.image"
				:alt="item.title">
		</figure>
		<div class="sk-fading-circle"
			v-if="isLoading">
			<div class="sk-circle1 sk-circle"></div>
			<div class="sk-circle2 sk-circle"></div>
			<div class="sk-circle3 sk-circle"></div>
			<div class="sk-circle4 sk-circle"></div>
			<div class="sk-circle5 sk-circle"></div>
			<div class="sk-circle6 sk-circle"></div>
			<div class="sk-circle7 sk-circle"></div>
			<div class="sk-circle8 sk-circle"></div>
			<div class="sk-circle9 sk-circle"></div>
			<div class="sk-circle10 sk-circle"></div>
			<div class="sk-circle11 sk-circle"></div>
			<div class="sk-circle12 sk-circle"></div>
		</div>
	</div>
</template>

<script>
import smoothscroll from 'smoothscroll'
import request from 'superagent'
import baseUrl from '../helpers/baseUrl'
import mq from '../helpers/media-queries'

import {
	updateActive,
	startLoading
} from '../vuex/actions'
import {
	getActiveItemId,
	getLoadingState
} from '../vuex/getters'

export default {
	name: 'ShowcaseItem',

	props: {
		item: {
			'type': Object,
			'required': true,
		},

		row_id: {
			'type': String,
			'required': true,
		},

		vp_width: {
			'type': Number,
			'required': true,
		},
	},

	computed: {
		itemClass: function() {
			return this.item.id === this.activeItemId ? 'showcase__item--loaded' : ''
		},

		isLoading: function() {
			if (this.item.id === this.activeItemId && this.loadingState) {
				return true
			} else {
				return false
			}
		},
	},

	methods: {
		clickHandler: function() {
			this.startLoading(this.item.id, this.row_id)
			this.fetchItemData()
			
			if (this.vp_width < mq.px.medium) {
				var self = this,
					delay

				function delayAction() {
					delay = window.setTimeout(doIt, 300)
				}

				function doIt() {
					self.scrollToViewportTop()
					window.clearTimeout(delay)
				}

				delayAction()
			}
		},

		fetchItemData: function() {
			var self = this
			request
				.get(baseUrl() + 'api/projects/' + self.item.slug + '.json')
				.set('Accept', 'application/json')
				.end(function(err, res) {
					// save content to store
					self.updateActive(res.body.data[0])
				});
		},

		scrollToViewportTop: function() {
			smoothscroll(this.$el)
		},
	},

	vuex: {
		actions: {
			updateActive: updateActive,
			startLoading: startLoading
		},

		getters: {
			activeItemId: getActiveItemId,
			loadingState: getLoadingState
		},
	}
}
</script>

<style lang="scss">

</style>
