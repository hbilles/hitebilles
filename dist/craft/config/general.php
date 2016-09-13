<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */

return array(
	'*' => array(
		'siteName' => 'Hite Billes',
		'siteUrl' => getenv('SITE_URL'),
		'timezone' => 'America/Chicago',
		'omitScriptNameInUrls' => true,
		'generateTransformsBeforePageLoad' => true,
		'defaultImageQuality'	=> 80,
		'allowedFileExtensions' => 'jpeg, jpg, png, gif, pdf, svg, vcf, zip',
		
		// custom global variables
		'titleBullet' => '·',
		'homeLinkTitle' => 'back to homepage',
		'googleAnalyticsProfileId' => 'UA-XXXXXXXX-X',

		'environmentVariables' => array(
			'basePath' => '../public/',
			'baseUrl' => getenv('SITE_URL') . '/',
		),

		// dev variables
		'devMode' => getenv('DEVMODE'),
		'enableTemplateCaching' => getenv('ENABLE_CACHE'),
	),

);
