<?php
namespace Craft;

class Perform_SubmissionModel extends BaseElementModel
{
	protected $elementType = 'Perform_Submission';

	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'formId'          => AttributeType::Number,
			'submitterName'   => AttributeType::String,
			'submitterEmail'  => AttributeType::String,
			'attachmentId'    => AttributeType::Number,
			'dynamicVars'     => AttributeType::Mixed,
			'cmOptIn'         => AttributeType::Bool,
		));
	}


	public function isEditable()
	{
		return true;
	}

	public function getCpEditUrl()
	{
		$form = $this->getForm();

		if ($form)
		{
			return UrlHelper::getCpUrl('perform/' . $form->handle . '/' . $this->id);
		}
	}

	public function getFieldLayout()
	{
		$form = $this->getForm();

		if ($form)
		{
			return $form->getFieldLayout();
		}
	}

	public function getForm()
	{
		if ($this->formId)
		{
			return craft()->perform_forms->getFormById($this->formId);
		}
	}

	public function downloadLink($handle)
	{
		if (sizeof($this[$handle]) > 0)
		{
			return '<a href="/admin/perform/survey/file/' . $this[$handle][0]->id . '">Download <i>' . $this[$handle][0]->title . '</i></a>';
		}
	}

	public function getAssetModel($handle)
	{
		if (sizeof($this[$handle]) > 0)
		{
			$fileId = $this[$handle][0]->id;
			return craft()->assets->getFileById($fileId);
		}
	}

	public function __toString()
	{
		return "#$this->id";
	}

	public function getSummary()
	{
		$summary = '';

		$questions = $this->getForm()->getQuestions();

		for ($i = 0; $i < count($questions); ++$i)
		{
			$question = $questions[$i];

			if ($question->type != Perform_QuestionType::Assets)
			{
				$name = $question->name;
				$value = addcslashes($this[$question->handle], '$');

				$summary .= $name . ":\n";
				$summary .= $value;

				if ($i != count($questions) -1)
				{
					$summary .= "\n\n";
				}
			}
		}

		return $summary;
	}

	public function getRoutedEmail()
	{
		$questions = $this->getForm()->getQuestions();
		$email = '';

		foreach ($questions as $key => $question)
		{
			if ($question->type === Perform_QuestionType::DropdownEmailRouter ||
				$question->type === Perform_QuestionType::RadioButtonsEmailRouter)
			{
				foreach ($question['options'] as $option)
				{
					if ($option['label'] == $this[$question->handle])
					{
						$email = $option['value'];
						break 2;
					}
				}
			}
		}

		return $email;
	}

	public function getSubmitterName()
	{
		$handle = $this->getForm()->getSubmitterNameHandle();
	}
}
