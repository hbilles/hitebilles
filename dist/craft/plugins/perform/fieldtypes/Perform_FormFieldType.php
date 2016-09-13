<?php
namespace Craft;

class Perform_FormFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('Perform Form');
	}

	public function getInputHtml($name, $value)
	{
		$forms = craft()->perform_forms->getAllForms();

		$options = array();

		foreach ($forms as $form)
		{
			$options[$form->handle] = $form->name;
		}

		return craft()->templates->render('_includes/forms/select', array(
			'name'    => $name,
			'value'   => $value,
			'options' => $options
		));
	}
}