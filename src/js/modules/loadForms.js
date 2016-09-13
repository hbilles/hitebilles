// ---------------------------------------------------------------------
// load forms from javascript templates

import $ from 'jquery'
import _ from 'lodash'
import setValidation from './setValidation'
import Textfield from '../components/textfield'


var formTemplates  = document.querySelectorAll('script[data-type="form"]'),
	formCount      = formTemplates.length

// function to add validation rules for each input
function validateForm($form, options) {
	var options = options || {},
		$inputs = $form.find(':input');

	$form.validate(options);

	$inputs.each(function() {
		var $this = $(this);

		setValidation($this);
	});
}

// function to indicated a form has been submitted
function disableSubmit(form) {
	var button = form.querySelector('button[type="submit"]');

	button.classList.add('submitted');
	button.setAttribute('disabled', 'disabled');
}

// function to indicate submission success
function ajaxSubmitSuccess(form) {
	var button = form.querySelector('button[type="submit"]'),
		message = form.querySelector('.message--success');

	button.classList.remove('submitted');
	button.removeAttribute('disabled');

	form.reset()

	message.classList.remove('hidden');
}

// function to indicate error from ajax submission
function ajaxSubmitError(form) {
	var button = form.querySelector('button[type="submit"]'),
		message = form.querySelector('.message--error')

	button.classList.remove('submitted');
	button.removeAttribute('disabled');

	message.classList.remove('hidden');
}


for ( var i = 0; i < formCount; i++ ) {
	var formTemplate = formTemplates[i],
		formType = formTemplate.getAttribute('data-form-type'),
		formAction = formTemplate.getAttribute('data-form-action'),
		ajaxRedirect = formTemplate.getAttribute('data-ajax-redirect'),
		formEl = document.createElement('form'),
		formEnctype = formTemplate.getAttribute('data-enctype');

	// set the form's attributes and content
	formEl.className = formTemplate.getAttribute('data-class') || '';
	formEl.id = formTemplate.getAttribute('data-id') || '';	
	
	if ( formEnctype ) {
		formEl.setAttribute('enctype', formEnctype);
	}

	formEl.setAttribute('method', 'post');
	formEl.setAttribute('accept-charset', 'UTF-8');
	formEl.innerHTML = formTemplate.innerHTML;

	// add the form to the DOM
	var parent = formTemplate.parentNode,
		form = parent.insertBefore(formEl, formTemplate),
		$form = $(form);

	// Enhance the textfield inputs
	var textfields = form.querySelectorAll('.textfield')

	_.forEach(textfields, function(textfield) {
		var upgradedTextfield = new Textfield(textfield)
	})

	// set the form's validation rules
	if ( formType === 'ajax' ) {
		var options = {
			submitHandler: function(form) {
				disableSubmit(form);
				
				$.ajax({
					url: formAction,
					type: 'POST',
					data: $form.serialize(),
					success: function() {
						ajaxSubmitSuccess(form);
					},
					error: function() {
						ajaxSubmitError(form)
					}
				});
			}
		}

		validateForm($form, options);
	} else {
		var options = {
			submitHandler: function(form) {
				disableSubmit(form);

				form.submit();
			}
		};
		
		validateForm($form, options);
	}

}
