// ---------------------------------------------------------------------
// set form validation...

import '../vendor/jquery.validate'
import '../vendor/jquery.validate.additional'


export default function($input) {

	var inputRules = {},
		inputMessages = {};

	if ($input.data('validation-required')) {
		inputMessages.required = $input.data('validation-required');
		inputRules.required = true;
		//console.log('Added a required rule.');
	} else {
		inputRules.required = false;
	}

	if ($input.data('validation-email')) {
		inputRules.email = true;
		inputMessages.email = $input.data('validation-email');
		//console.log('Added an email rule.');
	}

	if ($input.data('validation-tel')) {
		inputRules.intlphone = true;
		inputMessages.intlphone = $input.data('validation-tel');
		//console.log('Added a telephone rule.');
	}

	// add inputMessages to inputRules object
	inputRules.messages = inputMessages;

	$input.rules('add', inputRules);

}
