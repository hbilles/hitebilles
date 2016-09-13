<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'perform/enums/Perform_QuestionType.php');

class Perform_QuestionModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'              => AttributeType::Number,
			'formId'          => AttributeType::Number,
			'fieldId'         => AttributeType::Number,
			'name'            => AttributeType::String,
			'handle'          => AttributeType::Handle,
			'required'        => AttributeType::Bool,
			'submitterName'   => AttributeType::Bool,
			'submitterEmail'  => AttributeType::Bool,
			'emailAttachment' => AttributeType::Bool,
			'sortOrder'       => AttributeType::SortOrder,
			'options'         => AttributeType::Mixed,
			'type'            => array(AttributeType::Enum, 'values' => array(
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
}