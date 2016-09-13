<template>
	<div class="showcase__row"
		:id="rowId">
		
		<div class="showcase__row__in">
			<showcase-item
				v-for="item in group"
				:item="item"
				:row_id="rowId"
				:vp_width="vp_width"></showcase-item>
		</div>

		<showcase-content
			:row_id="rowId"></showcase-content>
	</div>
</template>

<script>
import ShowcaseItem from './ShowcaseItem.vue'
import ShowcaseContent from './ShowcaseContent.vue'

export default {
	name: 'ShowcaseRow',

	components: {
		'showcase-item': ShowcaseItem,
		'showcase-content': ShowcaseContent
	},

	props: {
		group: {
			'type': Array,
			'required': true,
		},

		group_size: {
			'type': Number,
			'required': true,
		},

		index: {
			'type': Number,
			'required': true,
		},

		vp_width: {
			'type': Number,
			'required': true,
		},
	},

	data() {
		return {
			content: {}
		}
	},

	computed: {
		rowId: function() {
			return 'row' + this.index + 'by' + this.group_size
		},
	},
}
</script>
