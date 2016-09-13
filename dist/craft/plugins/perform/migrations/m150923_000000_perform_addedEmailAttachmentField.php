<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150923_000000_perform_addedEmailAttachmentField extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$questionsTable = $this->dbConnection->schema->getTable('{{perform_questions}}');

		if ($questionsTable->getColumn('emailAttachment') === null)
		{
			// Add the 'emailAttachment' column to the perform_questions table
			$this->addColumnAfter('perform_questions', 'emailAttachment', array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true), 'submitterEmail');
		}

		return true;
	}
}
