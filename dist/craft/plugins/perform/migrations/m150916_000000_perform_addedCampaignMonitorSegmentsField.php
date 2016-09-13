<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150916_000000_perform_addedCampaignMonitorSegmentsField extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$formsTable = $this->dbConnection->schema->getTable('{{perform_forms}}');

		if ($formsTable->getColumn('cmSegments') === null)
		{
			// Add the 'cmSegments' column to the perform_forms table
			$this->addColumnAfter('perform_forms', 'cmSegments', array('required' => false, 'column' => 'text'), 'saveToCm');
		}

		return true;
	}
}
