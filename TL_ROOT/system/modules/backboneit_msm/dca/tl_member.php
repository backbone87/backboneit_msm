<?php

// remove the Contao toggle icons because they don't refresh the other icons
unset($GLOBALS['TL_DCA']['tl_member']['list']['operations']['toggle']);

$GLOBALS['TL_DCA']['tl_member']['list']['operations']['bbit_msm'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_member']['bbit_msm_deactivate'],
	'href'				=> 'key=bbit_msm',
	'icon'				=> 'system/modules/backboneit_msm/html/img/active.png',
	'button_callback'	=> array('MSMBackend', 'buttonChangeStatus')
);
