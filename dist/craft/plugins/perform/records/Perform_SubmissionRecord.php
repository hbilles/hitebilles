<?php
namespace Craft;

class Perform_SubmissionRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'perform_submissions';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
			'form' => array(static::BELONGS_TO, 'Perform_FormRecord', 'required' => true, 'onDelete' => static::CASCADE)
		);
	}
}