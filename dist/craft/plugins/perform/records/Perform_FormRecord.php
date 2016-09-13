<?php
namespace Craft;

class Perform_FormRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'perform_forms';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'name'            => array(AttributeType::Name, 'required' => true),
			'handle'          => array(AttributeType::Handle, 'required' => true),
			'emails'          => AttributeType::Mixed,
			'successRedirect' => AttributeType::String,
			'highriseTags'    => AttributeType::Mixed,
			'saveToCm'        => AttributeType::Bool,
			'cmRequireOptIn'  => AttributeType::Bool,
			'cmSegments'      => AttributeType::Mixed
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'fieldGroup' => array(static::BELONGS_TO, 'FieldGroupRecord', 'onDelete' => static::SET_NULL),
			'submissions' => array(static::HAS_MANY, 'Perform_SubmissionRecord', 'formId')
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('name'), 'unique' => true),
			array('columns' => array('handle'), 'unique' => true)
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'name')
		);
	}
}