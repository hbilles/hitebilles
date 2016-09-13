export default function baseUrl() {
	if (location.hostname == 'localhost' || location.hostname == 'hitebilles.dev') {
		return 'http://hitebilles.dev/'
	} else {
		return location.protocol + '//' + location.host + '/'
	}
}
