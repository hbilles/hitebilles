<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'perform/enums/Perform_QuestionType.php');

class Perform_QuestionRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'perform_questions';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'required'        => array(AttributeType::Bool, 'required' => true),
			'submitterName'   => AttributeType::Bool,
			'submitterEmail'  => AttributeType::Bool,
			'emailAttachment' => AttributeType::Bool,
			'sortOrder'       => array(AttributeType::SortOrder, 'required' => true),
			'type'            => array(AttributeType::Enum, 'required' => true, 'values' => array(
				Perform_QuestionType::PlainText,
				Perform_QuestionType::MultilineText,
				Perform_QuestionType::Dropdown,
				Perform_QuestionType::RadioButtons,
				Perform_QuestionType::Checkboxes,
				Perform_QuestionType::DropdownEmailRouter,
				Perform_QuestionType::RadioButtonsEmailRouter,
				Perform_QuestionType::Email,
				Perform_QuestionType::Tel,
				Perform_QuestionType::Url,
				Perform_QuestionType::Number,
				Perform_QuestionType::Date,
				Perform_QuestionType::Hidden,
				Perform_QuestionType::Assets
			))
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'form'  => array(static::BELONGS_TO, 'Perform_FormRecord', 'onDelete' => static::CASCADE),
			'field' => array(static::BELONGS_TO, 'FieldRecord', 'onDelete' => static::CASCADE)
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'sortOrder')
		);
	}
}