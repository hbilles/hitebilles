<?php
namespace Craft;

class PerformPlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('Perform');
	}

	function getVersion()
	{
		return '0.13.3';
	}

	function getDeveloper()
	{
		return 'Hite Billes';
	}

	function getDeveloperUrl()
	{
		return 'http://hitebilles.com';
	}

	public function getDescription()
	{
		return 'A plugin to create custom forms with flexible email notifications. Submissions are saved to the CMS, and email addresses can be added to a Campaign Monitor list.';
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'perform/forms'                                          => array('action' => 'perform/forms/formIndex'),
			'perform/forms/new'                                      => array('action' => 'perform/forms/editForm'),
			'perform/forms/(?P<formId>\d+)'                          => array('action' => 'perform/forms/editForm'),
			'perform'                                                => array('action' => 'perform/submissions/submissionIndex'),
			'perform/(?P<formHandle>{handle})/(?P<submissionId>\d+)' => array('action' => 'perform/submissions/viewSubmission'),
			'perform/(?P<formHandle>{handle})/file/(?P<fileId>\d+)'  => array('action' => 'perform/submissions/viewUpload'),
			'perform/export'                                         => array('action' => 'perform/export/exportIndex'),
			'perform/export/csv'                                     => array('action' => 'perform/export/csv')
		);
	}
}
