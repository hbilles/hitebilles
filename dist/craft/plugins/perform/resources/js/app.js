(function() {
	
	var $editForm = $('#perform-editForm'),
		$submissionElements = $('.elements');


	function nextId() {
		var count = $('#questions .question').length;
		return 'new_' + (count + 1);
	}

	function toggleOptions($question) {
		var val           = $question.find('select[name*=type]').val();
		var isSimple      = ['PlainText', 'MultilineText', 'FileUpload', 'Email', 'Tel', 'Url', 'Number', 'Date', 'Assets', 'Hidden'].indexOf(val) != -1;
		var isName        = ['PlainText'].indexOf(val) == -1;
		var isEmail       = ['Email'].indexOf(val) == -1;
		var isEmailRouter = ['DropdownEmailRouter', 'RadioButtonsEmailRouter'].indexOf(val) != -1
		var isAttachment  = ['Assets'].indexOf(val) == -1;

		$question.find('.options').toggleClass('hidden', isSimple);
		$question.find('.submitterName').toggleClass('hidden', isName);
		$question.find('.submitterEmail').toggleClass('hidden', isEmail);
		$question.find('.emailAttachment').toggleClass('hidden', isAttachment);

		if (isEmailRouter) {
			$question.find('.options thead th:nth-child(2)').html('Email');
		} else {
			$question.find('.options thead th:nth-child(2)').html('Value');
		}
	}

	function toggleCm() {
		var val             = $('#saveToCm-field').find('input[name="saveToCm"]').val(),
			isCmDisabled    = val ? false : true,
			$cmRequireOptIn = $('#cmRequireOptIn-field'),
			$cmSegments     = $('#cmSegments-field');

		$cmRequireOptIn.toggleClass('hidden', isCmDisabled);
		$cmSegments.toggleClass('hidden', isCmDisabled);
	}



	if ($editForm.length) {
		
		var $questions     = $('#questions'),
			$questionItems = $questions.find('.question'),
			questionSort   = new Garnish.DragSort($('#questions .question'), {
				caboose: '<div/>',
				handle: '> .actions > .move',
				axis: 'y',
				helperOpacity: 0.9
			});

		$questionItems.each(function() {
			toggleOptions($(this));
		});

		$('#perform-addQuestion').on('click', function(e) {
			e.preventDefault();
			
			var id = nextId();

			var html = $('#questionTemplate').html();
			html = html.replace(/__QUESTION_ID__/g, id);
			var $question = $(html);

			$question.appendTo($questions);
			questionSort.addItems($question);

			toggleOptions($question);

			new Craft.EditableTable('questions-' + id + '-options', 'questions[' + id + '][options]', {
				label: {
					heading: 'Label',
					type: 'singleline',
					width: '50%'
				},
				value: {
					heading: 'Value',
					type: 'singleline',
					width: '50%'
				},
				default: {
					heading: 'Default',
					type: 'checkbox'
				}
			});
		});

		$questions.on('click', '.actions .delete', function(e) {
			$(e.currentTarget).parents('.question').remove();
		});

		$questions.on('change', 'select[name*=type]', function(e) {
			var $question = $(e.currentTarget).parents('.question');
			toggleOptions($question);
		});


		// Show or hide Campaign Monitor options
		toggleCm();

		$('#saveToCm-field').on('click', '.lightswitch', function(e) {
			toggleCm();
		});
	}


	if ($submissionElements.length) {

		$submissionElements.on('click', '.delete', function(e) {
			var $row = $(e.currentTarget).parents('tr');
			var id = $row.attr('data-id');
			if (confirm('Are you sure you want to delete this submission?')) {
				Craft.postActionRequest('perform/submissions/deleteSubmission', { submissionId: id }, function(response) {
					if (response && response.success) {
						$row.remove();
						Craft.cp.displayNotice('Submission deleted.');
					} else {
						Craft.cp.displayError('Error deleting submission.');
					}
				});
			}
		});

	}

})();