<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151117_000000_perform_addedCampaignMonitorOptInCheck extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$formsTable = $this->dbConnection->schema->getTable('{{perform_forms}}');

		if ($formsTable->getColumn('cmRequireOptIn') === null)
		{
			// Add the 'cmRequireOptIn' column to the perform_forms table
			$this->addColumnAfter('perform_forms', 'cmRequireOptIn', array('maxLength' => 1, 'default' => false, 'required' => false, 'column' => 'tinyint', 'unsigned' => true), 'saveToCm');
		}

		return true;
	}
}
