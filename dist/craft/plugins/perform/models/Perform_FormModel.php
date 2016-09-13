<?php
namespace Craft;

class Perform_FormModel extends BaseModel
{
	public function getFieldLayout()
	{
		return craft()->perform_forms->getFieldLayout($this);
	}

	public function getQuestions()
	{
		return craft()->perform_forms->getQuestionsByFormId($this->id);
	}

	public function getQuestion($questionId)
	{
		if (!$questionId) { return false; }

		return craft()->perform_forms->getQuestionById($questionId);
	}



	protected function defineAttributes()
	{
		return array(
			'id'              => AttributeType::Number,
			'fieldGroupId'    => AttributeType::Number,
			'name'            => AttributeType::String,
			'handle'          => AttributeType::Handle,
			'emails'          => AttributeType::Mixed,
			'successRedirect' => AttributeType::String,
			'highriseTags'    => AttributeType::Mixed,
			'saveToCm'        => AttributeType::Bool,
			'cmRequireOptIn'  => AttributeType::Bool,
			'cmSegments'      => AttributeType::Mixed
		);
	}
}