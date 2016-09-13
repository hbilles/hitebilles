import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
	activeItemId: '',
	activeRowId: '',
	activeContent: {},
	loading: false,
}

const mutations = {
	// A mutation receives the current state as the first argument
	// You can make any modifications you want inside this function
	UPDATE_ACTIVE(state, data) {
		state.activeContent = data
		state.loading       = false
	},

	START_LOADING(state, itemId, rowId) {
		state.activeItemId  = itemId
		state.activeRowId   = rowId
		state.loading = true
	}
}

export default new Vuex.Store({
	state,
	mutations
})
