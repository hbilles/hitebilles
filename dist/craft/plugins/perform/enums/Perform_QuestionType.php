<?php
namespace Craft;

class Perform_QuestionType extends BaseEnum
{
	const PlainText                      = 'PlainText';
	const MultilineText                  = 'MultilineText';
	const Dropdown                       = 'Dropdown';
	const RadioButtons                   = 'RadioButtons';
	const Checkboxes                     = 'Checkboxes';
	const DropdownEmailRouter            = 'DropdownEmailRouter';
	const RadioButtonsEmailRouter        = 'RadioButtonsEmailRouter';
	const Email                          = 'Email';
	const Tel                            = 'Tel';
	const Url                            = 'Url';
	const Number                         = 'Number';
	const Date                           = 'Date';
	const Hidden                         = 'Hidden';
	const Assets                         = 'Assets';
	// these constants are used to identify special custom fields
	const DropdownEmailRouterHandle      = 'xo_d_e_r_';
	const RadioButtonsEmailRouterHandle  = 'xo_r_e_r_';
}
