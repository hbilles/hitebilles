<?php
namespace Craft;

class Perform_SubmissionElementType extends BaseElementType
{
	// Public Methods
	// =========================================================================

	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Submissions');
	}

	/**
	 * @return bool
	 */
	public function hasContent()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasTitles()
	{
		return false;
	}

	/**
	 * @param string|null $context
	 * @return array
	 */
	public function getSources($context = null)
	{
		$sources = array();

		foreach (craft()->perform_forms->getAllForms() as $form)
		{
			$sources[$form->id] = array(
				'label'    => $form->name,
				'criteria' => array('formId' => $form->id),
				'key' => $form->id,
			);
		}

		return $sources;
	}

	/**
	 * @param string|null $source
	 * @return array
	 */
	public function defineTableAttributes($source = null)
	{
		$form = null;
		$sourceId = -1;

		if (isset($_POST['source']))
		{
			$sourceId = $_POST['source'];
			$form = craft()->perform_forms->getFormById($sourceId);
		}

		$attributes = array();
		$attributes['id'] = 'ID';

		foreach (craft()->perform_forms->getAllForms() as $form)
		{
			if ($sourceId === -1 || $sourceId === $form->id)
			{
				foreach ($form->getQuestions() as $question)
				{
					if ($question->type != Perform_QuestionType::MultilineText &&
						$question->type != Perform_QuestionType::Assets)
					{
						if ($sourceId === $form->id)
						{
							$attributes[$question->handle] = $question->name;	
						}
						else
						{
							$attributes[$question->handle] = $form->name . '-' . $question->name;
						}
					}

					if ($sourceId === $form->id && count($attributes) >= 5)
					{
						break;
					}
				}
			}
		}

		$attributes['dateCreated'] = Craft::t('Date Created');
		$attributes['action'] = '';

		return $attributes;
	}

	/**
	 * @param BaseElementModel $element
	 * @param string           $attribute
	 * @return mixed|null|string
	 */
	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		if ($attribute == 'action')
		{
			return '<a class="delete icon" role="button" title="Delete"></a>';
		}
		else
		{
			return parent::getTableAttributeHtml($element, $attribute);
		}
	}

	/**
	 * @return array
	 */
	public function defineCriteriaAttributes()
	{
		return array(
			'form'   => AttributeType::Mixed,
			'formId' => AttributeType::Mixed,
		);
	}

	/**
	 * @param DbCommand            $query
	 * @param ElementCriteriaModel $criteria
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query
			->addSelect('submissions.formId')
			->join('perform_submissions submissions', 'submissions.id = elements.id');

		if ($criteria->formId)
		{
			$query->andWhere(DbHelper::parseParam('submissions.formId', $criteria->formId, $query->params));
		}

		if ($criteria->form)
		{
			$query->join('perform_forms forms', 'forms.id = submissions.formId');
			$query->andWhere(DbHelper::parseParam('perform_forms.handle', $criteria->form, $query->params));
		}
	}

	/**
	 * @param array $row
	 * @return BaseElementModel|BaseModel|void
	 */
	public function populateElementModel($row)
	{
		return Perform_SubmissionModel::populateModel($row);
	}
}