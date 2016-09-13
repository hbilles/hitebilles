// This getter is a function which just returns the count
// With ES6 you can also write it as:
// export const getCount = state => state.count

export function getActiveItemId(state) {
	return state.activeItemId
}

export function getActiveRowId(state) {
	return state.activeRowId
}

export function getActiveContent(state) {
	return state.activeContent
}

export function getLoadingState(state) {
	return state.loading
}
