<?php
namespace Craft;

class Perform_SubmissionsController extends BaseController
{
	protected $allowAnonymous = array('actionPostSubmission');

	public function actionSubmissionIndex()
	{
		$variables['forms'] = craft()->perform_forms->getAllForms();
		$this->renderTemplate('perform/submissions', $variables);
	}

	public function actionViewSubmission(array $variables = array())
	{
		if (!empty($variables['submissionId']))
		{
			if (empty($variables['submission']))
			{
				$variables['submission'] = craft()->perform_submissions->getSubmissionById($variables['submissionId']);

				if (!$variables['submission'])
				{
					throw new HttpException(404);
				}

				$form = $variables['submission']->getForm();

				$variables['crumbs'] = array(
					array('label' => Craft::t('Perform'), 'url' => UrlHelper::getCpUrl('perform')),
					array('label' => $form->name, 'url' => UrlHelper::getCpUrl('perform/forms/' . $form->id)),
				);
			}
		}
		else
		{
			throw new HttpException(404);
		}

		$this->renderTemplate('perform/submissions/_view', $variables);
	}

	public function actionViewUpload(array $variables = array())
	{
		if (!empty($variables['fileId']))
		{
			$fileId = $variables['fileId'];

			$asset = craft()->assets->getFileById($fileId);

			$assetFilePath = craft()->perform_submissions->getFilePath($asset);

			$data = file_get_contents($assetFilePath);
			header("Content-type: " . $asset->mimeType);
			header("Content-disposition: attachment;filename=" . $asset->filename);

			echo $data;
		}

		return;
	}

	public function actionPostSubmission()
	{
		$this->requirePostRequest();

		$submission = new Perform_SubmissionModel();

		$submission->formId = craft()->request->getRequiredPost('formId');

		// Check file upload for errors
		$errors = false;

		if (sizeof($_FILES) > 0 && array_key_exists('questions', $_FILES) && array_key_exists('error', $_FILES['questions']))
		{
			foreach ($_FILES['questions']['error'] as $key => $error)
			{
				switch ($error) {
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						break;
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$submission->addError($key, 'File size exceeded size limit.');
						$errors = true;
						// Clear key so setContentFromPost works
						unset($_FILES['questions']['name'][$key]);
						unset($_FILES['questions']['error'][$key]);
						unset($_FILES['questions']['type'][$key]);
						unset($_FILES['questions']['size'][$key]);
						unset($_FILES['questions']['tmp_name'][$key]);
						break;
					default:
						$submission->addError($key, 'File upload failed.');
						$errors = true;
						// Clear key so setContentFromPost works
						unset($_FILES['questions']['name'][$key]);
						unset($_FILES['questions']['error'][$key]);
						unset($_FILES['questions']['type'][$key]);
						unset($_FILES['questions']['size'][$key]);
						unset($_FILES['questions']['tmp_name'][$key]);
				}
			}

			if ($errors)
			{
				$submission->setContentFromPost('questions');
				craft()->urlManager->setRouteVariables(array(
					'submission' => $submission
				));

				return;
			}
		}

		$submitterNameHandle   = craft()->perform_forms->getSubmitterNameHandleByFormId($submission->formId);
		$submitterEmailHandle  = craft()->perform_forms->getSubmitterEmailHandleByFormId($submission->formId);
		$emailAttachmentHandle = craft()->perform_forms->getEmailAttachmentHandleByFormId($submission->formId);
		$postedQuestions       = craft()->request->getPost('questions');
		$dynamicVars           = craft()->request->getPost('dynamic_vars');
		$cmOptIn               = craft()->request->getPost('cm_opt_in');

		$submission->submitterName   = isset($postedQuestions[$submitterNameHandle])   ? $postedQuestions[$submitterNameHandle]   : '';
		$submission->submitterEmail  = isset($postedQuestions[$submitterEmailHandle])  ? $postedQuestions[$submitterEmailHandle]  : '';
		$submission->dynamicVars     = isset($dynamicVars)                             ? $dynamicVars                             : '';
		$submission->cmOptIn         = isset($cmOptIn)                                 ? $cmOptIn                                 : '';

		$submission->setContentFromPost('questions');

		if (craft()->perform_submissions->postSubmission($submission))
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array('ok' => 'yes', 'id' => $submission->id));
			}
			else
			{
				$this->redirectToPostedUrl($submission);
			}
		}
		else
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array('ok' => 'no', 'errors' => $submission->getErrors()));
			}
			else
			{
				craft()->urlManager->setRouteVariables(array(
					'submission' => $submission
				));
			}
		}
	}

	public function actionDeleteSubmission()
	{
		$this->requireAjaxRequest();

		$submissionId = craft()->request->getRequiredPost('submissionId');
		$success = craft()->perform_submissions->deleteSubmissionById($submissionId);

		$this->returnJson(array('success' => $success));
	}
}