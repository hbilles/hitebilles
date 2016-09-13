<?php
namespace Craft;

class Perform_FormsService extends BaseApplicationComponent
{
	private function _questionAttributesForField(FieldModel $field)
	{
		$attributes = array(
			'name'   => $field->name,
			'handle' => $field->handle
		);

		switch ($field->type)
		{
			case 'Dropdown':
			case 'RadioButtons':
			case 'Checkboxes':
			case 'DropdownEmailRouter':
			case 'RadioButtonsEmailRouter':

				$attributes['options'] = $field->settings['options'];
				break;
		}

		return $attributes;
	}

	private function _fieldForQuestion(Perform_QuestionModel $question, $prefix = '')
	{
		$field = new FieldModel();

		$field->name = $question->name;
		$field->handle = $prefix ? $prefix . '_' . $question->handle : $question->handle;

		switch ($question->type)
		{
			case Perform_QuestionType::PlainText:
			case Perform_QuestionType::MultilineText:

				$field->type = 'PlainText';
				$field->settings = array(
					'multiline' => $question->type == Perform_QuestionType::MultilineText
				);

				break;

			case Perform_QuestionType::Dropdown:
			case Perform_QuestionType::RadioButtons:
			case Perform_QuestionType::Checkboxes:

				$field->type = $question->type;
				$field->settings = array(
					'options' => $question->options
				);

				break;

			case Perform_QuestionType::DropdownEmailRouter:

				$field->type = 'Dropdown';
				$field->settings = array(
					'options' => $question->options
				);

				break;

			case Perform_QuestionType::RadioButtonsEmailRouter:

				$field->type = 'RadioButtons';
				$field->settings = array(
					'options' => $question->options
				);

				break;

			case Perform_QuestionType::Email:
			case Perform_QuestionType::Tel:
			case Perform_QuestionType::Url:
			case Perform_QuestionType::Hidden:
				
				$field->type = 'PlainText';
				break;

			case Perform_QuestionType::Number:
				$field->type = 'Number';
				break;

			case Perform_QuestionType::Date:
				$field->type = 'Date';
				break;

			case Perform_QuestionType::Assets:

				$folderId = 1;
				$allowedKinds = array('image','pdf','text');

				if (craft()->config->exists('perform'))
				{
					if (array_key_exists('assetFolderId', craft()->config->get('perform')))
					{
						$folderId = craft()->config->get('perform')['assetFolderId'];
					}
					if (array_key_exists('allowedKinds', craft()->config->get('perform')))
					{
						$allowedKinds = craft()->config->get('perform')['allowedKinds'];
					}
				}

				$field->type = 'Assets';
				$field->settings = array(
					'useSingleFolder'              => '1',
					'sources'                      => array('folder:' . $folderId),
					'defaultUploadLocationSource'  => '1',
					'defaultUploadLocationSubpath' => '',
					'singleUploadLocationSource'   => $folderId,
					'singleUploadLocationSubpath'  => '',
					'restrictFiles'                => '1',
					'allowedKinds'                 => $allowedKinds,
					'limit'                        => '1'
				);
				break;
		}

		return $field;
	}



	public function getAllForms()
	{
		$formRecords = Perform_FormRecord::model()->ordered()->findAll();
		return Perform_FormModel::populateModels($formRecords);
	}

	public function getFormById($formId)
	{
		$formRecord = Perform_FormRecord::model()->findById($formId);

		if ($formRecord)
		{
			return Perform_FormModel::populateModel($formRecord);
		}
	}

	public function getFormByHandle($formHandle)
	{
		$formRecord = Perform_FormRecord::model()->findByAttributes(array(
			'handle' => $formHandle
		));

		if ($formRecord)
		{
			return Perform_FormModel::populateModel($formRecord);
		}
	}

	public function saveForm(Perform_FormModel $form)
	{		
		// get record
		if ($form->id)
		{
			$formRecord = Perform_FormRecord::model()->findById($form->id);

			if ( !$formRecord )
			{
				throw new Exception(Craft::t('No form exists with the ID "{id}"', array('id' => $form->id)));
			}
		}
		// else create a new form
		else
		{
			$formRecord = new Perform_FormRecord();
		}

		// get the field group model
		$formFieldGroup       = new FieldGroupModel();
		$formFieldGroup->id   = $form->fieldGroupId;
		$formFieldGroup->name = 'Perform: ' . $form->name;

		// set the form's attributes
		$formRecord->name            = $form->name;
		$formRecord->handle          = $form->handle;
		$formRecord->emails          = $form->emails;
		$formRecord->successRedirect = $form->successRedirect;
		$formRecord->highriseTags    = $form->highriseTags;
		$formRecord->saveToCm        = $form->saveToCm;
		$formRecord->cmRequireOptIn  = $form->cmRequireOptIn;
		$formRecord->cmSegments      = $form->cmSegments;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// save the field group
			if (craft()->fields->saveGroup($formFieldGroup))
			{
				$form->fieldGroupId       = $formFieldGroup->id;
				$formRecord->fieldGroupId = $formFieldGroup->id;
			}
			else
			{
				$form->addErrors($formRecord->getErrors());
			}

			// validate the form
			$formRecord->validate();
			$form->addErrors($formRecord->getErrors());

			if (!$form->hasErrors())
			{
				// save without running validation again
				$formRecord->save(false);

				if ($transaction !== null) { $transaction->commit(); }

				$form->id = $formRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null) { $transaction->rollback(); }

				return false;
			}
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }

			throw $e;
		}
	}

	public function deleteFormById($formId)
	{
		$form = $this->getFormById($formId);

		if (!$formId || !$form) { return false; }

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// delete form group
			craft()->fields->deleteGroupById($form->fieldGroupId);

			// delete form
			$affectedRows = craft()->db->createCommand()->delete('perform_forms', array('id' => $formId));

			if ($transaction !== null) { $transaction->commit(); }
			return (bool) $affectedRows;
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }
			
			throw $e;
		}
	}

	public function getQuestionsByFormId($formId, $indexBy = null)
	{
		$questionRecords = Perform_QuestionRecord::model()->ordered()->findAllByAttributes(array(
			'formId' => $formId
		));

		$form = $this->getFormById($formId);
		$fields = craft()->fields->getFieldsByGroupId($form->fieldGroupId, 'id');

		$questions = Perform_QuestionModel::populateModels($questionRecords, $indexBy);

		foreach ($questions as $key => $question)
		{
			$field = $fields[$question->fieldId];
			$question->setAttributes($this->_questionAttributesForField($field));

			if (strpos($field->handle, Perform_QuestionType::DropdownEmailRouterHandle) > 0)
			{
				$question->type = Perform_QuestionType::DropdownEmailRouter;
			}
			if (strpos($field->handle, Perform_QuestionType::RadioButtonsEmailRouterHandle) > 0)
			{
				$question->type = Perform_QuestionType::RadioButtonsEmailRouter;
			}
			if ($field->type == Perform_QuestionType::Assets)
			{
				$question->type = Perform_QuestionType::Assets;
			}
		}

		return $questions;
	}

	public function getQuestionById($questionId)
	{
		$questionRecord = Perform_QuestionRecord::model()->findById($questionId);

		if ($questionRecord)
		{
			$question = Perform_QuestionModel::populateModel($questionRecord);

			$field = craft()->fields->getFieldById($question->fieldId);
			$question->setAttributes($this->_questionAttributesForField($field));

			return $question;
		}
	}

	public function saveQuestion(Perform_QuestionModel $question)
	{
		if (!$question)
		{
			return false;
		}

		// get the form
		$form = $this->getFormById($question->formId);
		if (!$form)
		{
			$question->addError('formId', 'No form with ID ' . $question->formId . ' exists');
			return false;
		}

		// get existing record or create new one
		$questionRecord = Perform_QuestionRecord::model()->findById($question->id);
		if (!$questionRecord)
		{
			$questionRecord = new Perform_QuestionRecord();
		}

		if ($question->type == Perform_QuestionType::DropdownEmailRouter)
		{
			$question->handle = Perform_QuestionType::DropdownEmailRouterHandle . $question->handle;
		}
		elseif ($question->type == Perform_QuestionType::RadioButtonsEmailRouter)
		{
			$question->handle = Perform_QuestionType::RadioButtonsEmailRouterHandle . $question->handle;
		}

		// get existing field or create new one
		$field = $this->_fieldForQuestion($question, $form->handle);
		$field->id = $questionRecord->fieldId;
		$field->groupId = $form->fieldGroupId;

		// set the attibutes on the record
		$questionRecord->formId          = $question->formId;
		$questionRecord->required        = $question->required;
		$questionRecord->submitterName   = $question->submitterName;
		$questionRecord->submitterEmail  = $question->submitterEmail;
		$questionRecord->emailAttachment = $question->emailAttachment;
		$questionRecord->sortOrder       = $question->sortOrder;
		$questionRecord->type            = $question->type;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// save the field
			if (craft()->fields->saveField($field))
			{
				$question->fieldId       = $field->id;
				$questionRecord->fieldId = $field->id;
			}
			else
			{
				$question->addErrors($field->getErrors());
			}

			// save the record
			$questionRecord->validate();
			$question->addErrors($questionRecord->getErrors());

			if (!$question->hasErrors())
			{
				$questionRecord->save(false);

				if ($transaction !== null) { $transaction->commit(); }

				$question->id = $questionRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null) { $transaction->rollback(); }

				return false;
			}
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback; }

			throw $e;
		}
	}

	public function deleteQuestion(Perform_QuestionModel $question)
	{
		if (!$question || !$question->id)
		{
			return false;
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// delete the field
			craft()->fields->deleteFieldById($question->fieldId);

			// delete the question
			$affectedRows = craft()->db->createCommand()->delete('perform_questions', array(
				'id' => $question->id
			));

			if ($transaction !== null) { $transaction->commit(); }

			return (bool) $affectedRows;
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }

			throw $e;
		}
	}

	public function getSubmitterNameHandleByFormId($formId)
	{
		$questionRecord = Perform_QuestionRecord::model()->findByAttributes(array(
			'formId' => $formId,
			'submitterName' => true
		));

		if ($questionRecord)
		{
			$question = Perform_QuestionModel::populateModel($questionRecord);

			$field = craft()->fields->getFieldById($question->fieldId);
			$question->setAttributes($this->_questionAttributesForField($field));

			return $question->handle;
		}

		return false;
	}

	public function getSubmitterEmailHandleByFormId($formId)
	{
		$questionRecord = Perform_QuestionRecord::model()->findByAttributes(array(
			'formId' => $formId,
			'submitterEmail' => true
		));

		if ($questionRecord)
		{
			$question = Perform_QuestionModel::populateModel($questionRecord);

			$field = craft()->fields->getFieldById($question->fieldId);
			$question->setAttributes($this->_questionAttributesForField($field));
			
			return $question->handle;
		}

		return false;
	}

	public function getEmailAttachmentHandleByFormId($formId)
	{
		$questionRecord = Perform_QuestionRecord::model()->findByAttributes(array(
			'formId' => $formId,
			'emailAttachment' => true
		));

		if ($questionRecord)
		{
			$question = Perform_QuestionModel::populateModel($questionRecord);

			$field = craft()->fields->getFieldById($question->fieldId);
			$question->setAttributes($this->_questionAttributesForField($field));

			return $question->handle;
		}

		return false;
	}

	public function getFieldLayout(Perform_FormModel $form)
	{
		if (!$form || !$form->id)
		{
			return false;
		}

		$questions = $this->getQuestionsByFormId($form->id);
		$fields = array();

		foreach ($questions as $question)
		{
			$field            = new FieldLayoutFieldModel();
			$field->fieldId   = $question->fieldId;
			$field->required  = $question->required;
			$field->sortOrder = $question->sortOrder;

			$fields[] = $field;
		}

		$layout = new FieldLayoutModel();
		$layout->type = 'Form';
		$layout->setFields($fields);

		return $layout;
	}
}