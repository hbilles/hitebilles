<?php

namespace Craft;

/**
 * Perform Variable
 */
class PerformVariable
{
	/**
	 * Returns the specified form
	 *
	 * @param string $formHandle
	 */
	function getForm($formHandle)
	{
		return craft()->perform_forms->getFormByHandle($formHandle);
	}

	/**
	 * Returns whether Highrise or Campaign Monitor credentials have been set
	 *
	 * @param string $setting
	 * @return bool
	 */
	function settingIsSet($setting)
	{
		$isSet = false;

		if (craft()->config->exists('perform'))
		{
			switch ($setting)
			{
				case 'cm':
				case 'CM':
				case 'Campaign Monitor':

					$isSet = (array_key_exists('cmApiKey', craft()->config->get('perform')) &&
						      array_key_exists('cmListId', craft()->config->get('perform')) ) ? true : false;
					break;

				case 'Highrise':
				case 'highrise':

					$isSet = (array_key_exists('highriseUsername', craft()->config->get('perform')) &&
						      array_key_exists('highriseAccessToken', craft()->config->get('perform')) ) ? true : false;
					break;
			}
		}
		
		return $isSet;
	}
}