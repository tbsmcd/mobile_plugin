<?php
$useMobileSession = true;
//$useMobileSession = false;

if ($useMobileSession === true && isMobile()) {
	ini_set('session.use_trans_sid', 1);
	ini_set('session.use_only_cookies', 0);
	ini_set('session.use_cookies', 0);
	ini_set('session.name', 'mplugin');
}

function isMobile() {
	$agents = array(
			'docomo' => array('/^DoCoMo.+$/'),
			'kddi' => array('/^KDDI.+UP.Browser.+$/', '/^UP.Browser.+$/'),
			'softbank' => array('/^(SoftBank|Vodafone|J-PHONE|MOT-C).+$/'),
			'iphone' => array('/^Mozilla.+iPhone.+$/'),
			'willcom' => array('/^Mozilla.+(WILLCOM|DDIPOCKET|MobilePhone).+$/', '/^PDXGW.+$/'),
			'emobile' => array('/^emobile.+$/'),
	);
	$userAgent = env('HTTP_USER_AGENT');
	
	if (!is_null($userAgent)) {
		foreach ($agents as $carrierName => $regex) {
			foreach ($regex as $reg) {
				if (preg_match($reg, $userAgent)) {
					$carrier = $carrierName;
				}
			}
		}
		if (is_null($carrier)) {
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

?>