<?php
namespace Craft;

class Perform_SubmissionsService extends BaseApplicationComponent
{
	private function _renderSubmissionTemplate($template, Perform_SubmissionModel $submission)
	{
		$formHandle = $submission->getForm()->handle;

		// parse the {summary} variable
		$formattedTemplate = preg_replace('/\{summary\}/', $submission->getSummary(), $template);
		// parse the {routedEmail} variable
		$formattedTemplate = preg_replace('/\{routedEmail\}/', $submission->getRoutedEmail(), $formattedTemplate);
		// parse the {submitterName} variable
		$formattedTemplate = preg_replace('/\{submitterName\}/', $submission->submitterName, $formattedTemplate);
		// parse the {submitterEmail} variable
		$formattedTemplate = preg_replace('/\{submitterEmail\}/', $submission->submitterEmail, $formattedTemplate);
		// parse dynamicVars passed to the submission
		if ($submission->dynamicVars && is_array($submission->dynamicVars))
		{
			foreach ($submission->dynamicVars as $key => $value)
			{
				$formattedTemplate = preg_replace('/\{'.$key.'\}/', $value, $formattedTemplate);
			}
		}
		// parse other variables
		$formattedTemplate = preg_replace('/(?<![\{\%])\{(?![\{\%])/', '{'.$formHandle.'_', $formattedTemplate);
		$formattedTemplate = preg_replace('/(?<![\}\%])\}(?![\}\%])/', '}', $formattedTemplate);

		return craft()->templates->renderObjectTemplate($formattedTemplate, $submission);
	}

	private function _getNameParts($name)
	{
		if (!$name)
		{
			return false;
		}

		// Get first and last names from the name
		$nameArray = explode(' ', $name);
		$lastName = array_pop($nameArray);
		$firstName = implode(' ', $nameArray);

		// If there's only one name, make it a first name
		if (!$firstName)
		{
			$firstName = $lastName;
			$lastName = null;
		}

		return array(
			'firstName' => $firstName,
			'lastName'  => $lastName 
		);
	}



	public function getSubmissionById($submissionId)
	{
		return craft()->elements->getElementById($submissionId, 'Perform_Submission');
	}

	public function postSubmission(Perform_SubmissionModel $submission)
	{
		$this->onBeforePost(new Event($this, array(
			'submission' => $submission
		)));

		if ($this->saveSubmission($submission))
		{
			// get the form so we can pass it to our tasks
			$form = $submission->getForm();

			$this->sendSubmissionEmails($submission, $form);
			$this->saveToCm($submission, $form);
			$this->saveToHighrise($submission, $form);

			$this->onPost(new Event($this, array(
				'submission' => $submission
			)));

			return true;
		}

		return false;
	}

	public function saveSubmission(Perform_SubmissionModel $submission)
	{
		$submissionRecord = new Perform_SubmissionRecord();

		$submissionRecord->formId = $submission->formId;

		$submissionRecord->validate();
		$submission->addErrors($submissionRecord->getErrors());

		if (!$submission->hasErrors())
		{
			// Check the honeypot
			if (craft()->config->exists('perform') &&
				array_key_exists('honeypotField', craft()->config->get('perform')) )
			{
				$honeypotField = craft()->config->get('perform')['honeypotField'];

				if (isset($_REQUEST[$honeypotField]) && $_REQUEST[$honeypotField] != null)
				{
					// triggered honeypot!
					// no error, just return back
					return false;
				}
			}

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				if (craft()->elements->saveElement($submission))
				{
					$submissionRecord->id = $submission->id;

					$submissionRecord->save(false);

					if ($transaction !== null) { $transaction->commit(); }

					return true;
				}
				else
				{
					return false;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction !== null) { $transaction->rollback(); }
			}
		}

		return false;
	}

	public function deleteSubmissionById($submissionId)
	{
		if (!$submissionId)
		{
			return false;
		}

		$affectedRows = craft()->elements->deleteElementById($submissionId);

		return (bool) $affectedRows;
	}

	public function getFilePath(AssetFileModel $assetModel)
	{
		if ($assetModel)
		{
			$sourcePath = $assetModel->getSource()->settings['path'];
			// parse any variables in the sourcePath
			$sourcePath = craft()->config->parseEnvironmentString($sourcePath);
			$folderPath = $assetModel->getFolder()->path;
			
			return $sourcePath . $folderPath . $assetModel->filename;
		}
		else
		{
			return null;
		}
	}

	protected function sendSubmissionEmails(Perform_SubmissionModel $submission, Perform_FormModel $form)
	{
		if (!$submission || !$form)
		{
			return false;
		}

		$emailSettings = craft()->systemSettings->getSettings('email');

		// Check if emails are sent through Campaign Monitor
		$_cm = false;
		if (!empty($emailSettings['host']) && $emailSettings['host'] === 'smtp.api.createsend.com')
		{
			$_cm = true;
		}

		if ($form->emails !== null)
		{
			// Set a count to track number of emails sent for a submission for logging purposes
			$emailCount = 0;

			foreach ($form->emails as $emailAttributes)
			{
				// skip this loop is no recipient is defined
				if (empty($emailAttributes['to'])) { continue; }

				$toEmails = $this->_renderSubmissionTemplate($emailAttributes['to'], $submission);
				$subject = !empty($emailAttributes['subject']) ? $this->_renderSubmissionTemplate($emailAttributes['subject'], $submission) : 'Message from ' . craft()->getSiteName() . ' website';
				$emailAttachmentHandle = craft()->perform_forms->getEmailAttachmentHandleByFormId($submission->formId);
				$template = $emailAttributes['template'];

				if (!empty($emailAttributes['fromEmail']))
				{
					$from = $this->_renderSubmissionTemplate($emailAttributes['fromEmail'], $submission);

					// https://regex101.com/r/yI0hL1/1
					preg_match('/^(.+)\<(.+)\>$/', $from, $matches);

					if (count($matches) >= 3)
					{
						// The provided from email is in the format Name <email>.
						$fromName  = trim($matches[1]);
						$fromEmail = trim($matches[2]);
					}
					else
					{
						// Note: If no from email is set, the default is the craft admin email address.
						$fromEmail = $from;
					}
				}

				if ($emailAttachmentHandle)
				{
					$emailAttachment = $submission->getAssetModel($emailAttachmentHandle);
				}

				if (!empty($emailAttributes['body']))
				{
					$body = $this->_renderSubmissionTemplate($emailAttributes['body'], $submission);
				}
				else
				{
					$body = $submission->getSummary();
				}

				$htmlBody = StringHelper::parseMarkdown($body);

				if (!empty($template))
				{
					$htmlBody = "{% extends '{$template}' %}\n" .
						"{% set body %}\n" .
						$htmlBody .
						"{% endset %}\n";
				}

				// allow for multiple recipients separated by commas
				$toEmails = ArrayHelper::stringToArray($toEmails);
				foreach ($toEmails as $toEmail)
				{
					$email = new EmailModel();
					$email->toEmail = $toEmail;
					$email->subject = $subject;

					// Note: If no from email is set, the Craft admin email is used as a default.
					if ($fromEmail)
					{
						$email->fromEmail = $fromEmail;

						if ($fromName)
						{
							$email->fromName = $fromName;
						}
					}

					// Set custom header to group emails in Campaign Monitor
					if ($_cm)
					{
						$email->customHeaders = array(
							'X-CMail-GroupName' => $subject
						);
					}

					$email->body = $body;
					$email->htmlBody = $htmlBody;

					// Add attachment if any
					if (isset($emailAttachment) && $emailAttachment)
					{
						$emailAttachmentFilePath = $this->getFilePath($emailAttachment);

						$email->addAttachment($emailAttachmentFilePath,
											$emailAttachment->filename,
											'base64',
											$emailAttachment->mimeType);
					}

					if (!empty($email->body))
					{
						$error = null;

						try
						{
							if (craft()->email->sendEmail($email))
							{
								$emailCount++;
								Craft::log('Sent email notification #' . $emailCount .' for Submission #' . $submission->id . ' to ' . $toEmail . ' with subject: ' . $subject, LogLevel::Info, true, 'application', 'Perform');
							};
						}
						catch (\Exception $e)
						{
							$error = 'An exception was thrown: ' . $e->getMessage();
						}

						if ($error)
						{
							$emailCount++;
							Craft::log('Failed to send email notification #' . $emailCount . ' for Submission #' . $submission->id . ' to ' . $toEmail . ' with subject: ' . $subject . ': ' . $error, LogLevel::Error, true, 'application', 'Perform');
						}
					}
				}
			}
		}
	}

	protected function saveToCm(Perform_SubmissionModel $submission, Perform_FormModel $form)
	{
		if (!$submission || !$form)
		{
			return false;
		}

		require_once(CRAFT_PLUGINS_PATH.'perform/helpers/ExceptionThrower.php');

		if (craft()->config->exists('perform') &&
			array_key_exists('cmApiKey', craft()->config->get('perform')) &&
			array_key_exists('cmListId', craft()->config->get('perform')) )
		{
			if ($submission->getForm()->saveToCm)
			{
				// If opt-in is required, check if submitter opted-in
				if ($form->cmRequireOptIn && $submission->cmOptIn === '')
				{
					// nope, so don't add them to CM
					return false;
				}

				$cmApiKey = craft()->config->get('perform')['cmApiKey'];
				$cmListId = craft()->config->get('perform')['cmListId'];

				$name = $submission->submitterName;
				$email = $submission->submitterEmail;
				$segments = array();

				// get the segments if they exist
				if ($form->cmSegments)
				{
					foreach ($form->cmSegments as $segment)
					{
						// skip this loop is no segment is set
						if (empty($segment['field']) || empty($segment['segment'])) { continue; }

						$segments[] = array(
							'Key'   => $segment['field'],
							'Value' => $segment['segment']
						);
					}
				}

				if ( $email )
				{
					\ExceptionThrower::Start();
					try
					{
						require_once(CRAFT_PLUGINS_PATH.'perform/vendor/createsend-php/csrest_subscribers.php');
						$auth = array('api_key' => $cmApiKey);
						$wrap = new \CS_REST_Subscribers($cmListId, $auth);

						$result = $wrap->add(array(
							'EmailAddress' => $email,
							'Name'         => $name,
							'CustomFields' => $segments
						));
					}
					catch (\Exception $e)
					{
						return false;
					}
					\ExceptionThrower::Stop();
				}
			}
		}
	}

	// NOTE: This function needs work!
	protected function saveToHighrise(Perform_SubmissionModel $submission, Perform_FormModel $form)
	{
		if (!$submission || !$form)
		{
			return false;
		}

		if (craft()->config->exists('perform') &&
			array_key_exists('highriseUsername', craft()->config->get('perform')) &&
			array_key_exists('highriseAccessToken', craft()->config->get('perform')) )
		{
			$highriseUsername = craft()->config->get('perform')['highriseUsername'];
			$highriseAccessToken = craft()->config->get('perform')['highriseAccessToken'];

			$name = $submission->submitterName;
			$email = $submission->submitterEmail;

			if ($name && $email)
			{
				try
				{
					require_once(CRAFT_PLUGINS_PATH.'perform/vendor/highrise/HighriseAPI.class.php');

					$tags = array();

					// get the tags
					if ($form->highriseTags)
					{
						foreach ($form->highriseTags as $tag)
						{
							// skip this loop is no tag is set
							if (empty($tag['tag'])) { continue; }

							$tags[] = $tag['tag'];
						}
					}

					// if there are tags, save to Highrise
					if (count($tags) > 0)
					{
						$highrise = new \HighriseAPI();
						$highrise->setAccount($highriseUsername);
						$highrise->setToken($highriseAccessToken);

						$people = $highrise->findPeopleByEmail($email);

						if ($people)
						{
							$person = $people[0];
						}
						else
						{
							$nameParts = $this->_getNameParts($name);
							$firstName = $nameParts['firstName'];
							$lastName  = $nameParts['lastName'];
							
							$person = new \HighrisePerson($highrise);
							$person->setFirstName($firstName);
							if ($lastName) { $person->setLastName($lastName); }
							$person->addEmailAddress($email);
						}

						foreach ($tags as $tag)
						{
							$person->addTag($tag);
						}

						// Save the person's entry
						$person->save();

						// Escape the summary content for XML
						$escBody = '<![CDATA[' . $submission->getSummary() . ']]>';

						// Attach a note to the person
						$note = new \HighriseNote($highrise);
						$note->setSubjectType('Party');
						$note->setSubjectId($person->getId());
						$note->setBody($escBody);
						$note->save();
					}
				}
				catch (\Exception $e)
				{
					return false;
				}
			}
		}
	}

	public function onBeforePost(Event $event)
	{
		$this->raiseEvent('onBeforePost', $event);
	}

	public function onPost(Event $event)
	{
		$this->raiseEvent('onPost', $event);
	}
}