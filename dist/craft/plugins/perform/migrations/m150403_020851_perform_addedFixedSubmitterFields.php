<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150403_020851_perform_addedFixedSubmitterFields extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$questionsTable = $this->dbConnection->schema->getTable('{{perform_questions}}');

		if ($questionsTable->getColumn('submitterName') === null)
		{
			// Add the 'submitterName' column to the perform_questions table
			$this->addColumnAfter('perform_questions', 'submitterName', array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true), 'required');
			$this->addColumnAfter('perform_questions', 'submitterEmail', array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true), 'submitterName');
		}

		return true;
	}
}
