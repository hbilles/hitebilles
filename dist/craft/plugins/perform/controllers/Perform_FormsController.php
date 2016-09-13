<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'perform/helpers/PerformHelpers.php');

class Perform_FormsController extends BaseController
{
	public function actionFormIndex()
	{
		$variables['forms'] = craft()->perform_forms->getAllForms();
		return $this->renderTemplate('perform/forms', $variables);
	}

	public function actionEditForm(array $variables = array())
	{
		// get the existing form and questions for editing
		if (!empty($variables['formId']))
		{
			if (empty($variables['form']))
			{
				$variables['form']      = craft()->perform_forms->getFormById($variables['formId']);
				$variables['questions'] = craft()->perform_forms->getQuestionsByFormId($variables['formId'], 'id');
				if (!$variables['form'])
				{
					throw new HttpException(404);
				}
			}
			$variables['title'] = $variables['form']->name;
		}
		// else create a new form with boilerplate emails and questions
		else
		{
			if (empty($variables['form']))
			{
				// get logged in user and system email settings
				$user = craft()->userSession->getUser();
				$systemEmail = craft()->systemSettings->getSettings('email');
				
				$form = new Perform_FormModel();
				// create boilerplate emails as examples
				$form->emails = array(
					array(
						'to'          => '{submitterEmail}',
						'fromEmail'   => $systemEmail["senderName"] . ' <' . $systemEmail['emailAddress'] . '>',
						'subject'     => 'Thanks for your inquiry!',
						'body'        => "Hi {submitterName},\n\nThanks for your inquiry! We'll get back to you soon.\n\n" . $systemEmail['senderName']
					),
					array(
						'to'          => $user->email,
						'fromEmail'   => $systemEmail["senderName"] . ' <' . $systemEmail['emailAddress'] . '>',
						'subject'     => 'Form Submission from the ' . craft()->getSiteName() . ' website',
						'body'        => "Someone submitted a form on the " . craft()->getSiteName() . " website. Details are below:\n\n{summary}"
					)
				);

				// create boilerplate questions as examples
				$questions = array(
					'new_1' => new Perform_QuestionModel(array(
						'name'          => 'Name',
						'handle'        => 'name',
						'type'          => Perform_QuestionType::PlainText,
						'required'      => true,
						'submitterName' => true
					)),
					'new_2' => new Perform_QuestionModel(array(
						'name'           => 'Email',
						'handle'         => 'email',
						'type'           => Perform_QuestionType::Email,
						'required'       => true,
						'submitterEmail' => true
					)),
					'new_3' => new Perform_QuestionModel(array(
						'name'     => 'Subject',
						'handle'   => 'subject',
						'type'     => Perform_QuestionType::Dropdown,
						'required' => true
					)),
					'new_4' => new Perform_QuestionModel(array(
						'name'     => 'Message',
						'handle'   => 'message',
						'type'     => Perform_QuestionType::MultilineText,
						'required' => true
					)),
				);

				$variables['form']      = $form;
				$variables['questions'] = $questions;
			}

			$variables['title'] = Craft::t('Create a new form');
		}

		$this->renderTemplate('perform/forms/_edit', $variables);
	}

	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$formId   = craft()->request->getPost('formId');
		// try to get an existing form
		$form     = craft()->perform_forms->getFormById($formId);
		$question = array();

		if (!$form)
		{
			$form = new Perform_FormModel();
		}

		// save posted data to form model
		$form->name            = craft()->request->getPost('name');
		$form->handle          = craft()->request->getPost('handle');
		$form->emails          = craft()->request->getPost('emails');
		$form->successRedirect = craft()->request->getPost('successRedirect');
		$form->highriseTags    = craft()->request->getPost('highriseTags');
		$form->saveToCm        = (bool) craft()->request->getPost('saveToCm');
		$form->cmRequireOptIn  = (bool) craft()->request->getPost('cmRequireOptIn');
		$form->cmSegments      = craft()->request->getPost('cmSegments');

		$postedQuestions = craft()->request->getPost('questions');
		$sortOrder = 0;

		if($postedQuestions)
		{
			foreach($postedQuestions as $questionId => $postedQuestion)
			{
				// try to get an existing question
				$question = craft()->perform_forms->getQuestionById($questionId);

				if(!$question)
				{
					$question = new Perform_QuestionModel();
				}

				// save posted question data to the question model
				$question->name            = $postedQuestion['name'];
				$question->handle          = PerformHelpers::generateHandle($question->name);
				$question->required        = $postedQuestion['required'];
				$question->submitterName   = $postedQuestion['submitterName'];
				$question->submitterEmail  = $postedQuestion['submitterEmail'];
				$question->emailAttachment = $postedQuestion['emailAttachment'];
				$question->type            = $postedQuestion['type'];
				$question->sortOrder       = ++$sortOrder;

				// save question configuration options
				if (isset($postedQuestion['options']))
				{
					$options = array();

					foreach ($postedQuestion['options'] as $postedOption)
					{
						$options[] = array(
							'label' => $postedOption['label'],
							'value' => $postedOption['value'] ? $postedOption['value'] : $postedOption['label'],
							'default' => (bool) $postedOption['default']
						);
					}

					$question->options = $options;
				}

				$questions[$questionId] = $question;
			}
		}

		$allSystemsGo = true;

		// save the form
		if(craft()->perform_forms->saveForm($form))
		{
			$existingQuestions = craft()->perform_forms->getQuestionsByFormId($form->id, 'id');
			$questionsToDelete = array_diff_key($existingQuestions, $questions);

			foreach ($questionsToDelete as $question)
			{
				craft()->perform_forms->deleteQuestion($question);
			}

			foreach ($questions as $question)
			{
				$question->formId = $form->id;

				// save the questions
				if (!craft()->perform_forms->saveQuestion($question))
				{
					$allSystemsGo = false;
					break;
				}
			}
		}
		else
		{
			$allSystemsGo = false;
		}

		if ($allSystemsGo)
		{
			// Success!
			craft()->userSession->setNotice(Craft::t('Form Saved.'));
			$this->redirectToPostedUrl($form);
		}
		else
		{
			// Boo!
			craft()->userSession->setError(Craft::t('Form could not be saved.'));
			// Send the form data back to the template
			craft()->urlManager->setRouteVariables(array(
				'form'      => $form,
				'questions' => $questions
			));
		}
	}

	public function actionDeleteForm()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$formId = craft()->request->getRequiredPost('id');

		$success = craft()->perform_forms->deleteFormById($formId);
		$this->returnJson(array('success' => $success));
	}
}