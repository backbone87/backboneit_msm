<?php

class MSMBackend extends Backend {
	
	public function buttonChangeStatus($row, $href, $label, $title, $icon, $attributes) {
		$blnDisable = $this->isInactive($row) ? '' : 1; 
		if(!$blnDisable) {
			$label = $GLOBALS['TL_LANG']['tl_member']['bbit_msm_activate'][0];
			$icon = 'system/modules/backboneit_msm/html/img/inactive.png';
		}

		return sprintf('<a href="%s" title="%s"%s>%s</a>',
			$this->addToUrl($href . '&id=' . $row['id'] . '&disable=' . $blnDisable),
			specialchars($label),
			$attributes,
			$this->generateImage($icon, $label)
		);
	}
	
	public function isInactive($arrMember) {
		return $arrMember['disable'] || (
			strlen($arrMember['username']) && strlen($arrMember['username']) && !$arrMember['login']
		);
	}

	public function changeStatus($objDC) {
		$blnEnable = !$this->Input->get('disable');
		$intID = $objDC->id;
		
		$objMember = $this->Database->prepare(
			'SELECT * FROM tl_member WHERE id = ?'
		)->execute($intID);
		
		if(!$objMember->numRows) {
			$this->redirect('contao/main.php?do=member');
			return;
		}
		if($blnEnable xor $this->isInactive($objMember->row())) {
			$this->redirect('contao/main.php?do=member');
			return;
		}
		
		if($blnEnable) {
			if(strlen($objMember->username) && strlen($objMember->username)) {
				$strLogin = ', login = \'1\'';
			}
			$this->Database->prepare(
				'UPDATE tl_member SET disable = \'\'' . $strLogin . ' WHERE id = ?'
			)->execute($intID);
			
			$this->sendMail($objMember->row(), true);
			
		} else {
			$this->Database->prepare(
				'UPDATE tl_member SET disable = \'1\' WHERE id = ?'
			)->execute($intID);
			
			$this->sendMail($objMember->row(), false);
		}
		
		$this->redirect('contao/main.php?do=member');
	}

	protected function sendMail($arrData, $blnEnabled) {
		$this->loadLanguageFile('bbit_msm');
		
		$strEnabled = $blnEnabled ? 'enabled' : 'disabled';
		
		$subject = $GLOBALS['TL_LANG']['bbit_msm'][$strEnabled]['subject'];
		$text = $GLOBALS['TL_LANG']['bbit_msm'][$strEnabled]['text'];
		
		foreach(array('subject', 'text') as $strField) {
			$arrMatches = array();
			preg_match_all('/##[^#]+##/i', $$strField, $arrMatches);
			foreach(array_unique($arrMatches[0]) as $strKey) {
				$$strField = str_replace($strKey, $arrData[substr($strKey, 2, -2)], $$strField);
			}
		}
		
		$objMail = new Email();
		$objMail->from = sprintf($GLOBALS['TL_LANG']['bbit_msm']['sender_mail'], $this->Environment->host);
		$objMail->fromName = $GLOBALS['TL_LANG']['bbit_msm']['sender_name'];
		$objMail->subject = $subject;
		$objMail->text = $text;
		$objMail->sendTo($arrData['email']);
	}
}

?>