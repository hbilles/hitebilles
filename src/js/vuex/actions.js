// An action will receive the store as the first argument.
// Since we are only interested in the dispatch (and optionally the state)
// we can pull those two parameters using the ES6 destructuring feature
export const updateActive = function({dispatch}, data) {
	dispatch('UPDATE_ACTIVE', data)
}

export const startLoading = function({dispatch}, itemId, rowId) {
	dispatch('START_LOADING', itemId, rowId)
}
