<?php
class MobileComponent extends Object {
	public $userAgent = null;
	public $carrier = null;
	public $useMobileSession = true;
	public $resolution = array(240, 320);
	
	public $_agents = array(
			'docomo' => array('/^DoCoMo.+$/'),
			'kddi' => array('/^KDDI.+UP.Browser.+$/', '/^UP.Browser.+$/'),
			'softbank' => array('/^(SoftBank|Vodafone|J-PHONE|MOT-C).+$/'),
			'iphone' => array('/^Mozilla.+iPhone.+$/'),
			'willcom' => array('/^Mozilla.+(WILLCOM|DDIPOCKET|MobilePhone).+$/', '/^PDXGW.+$/'),
			'emobile' => array('/^emobile.+$/'),
		);

	function __construct() {
		$this->userAgent = $userAgent = env('HTTP_USER_AGENT');
		$this->carrier = $carrier = $this->getCarrier($userAgent);
		$this->resolution = $this->getResolution($carrier);
		$this->unicodeToEmoji = $this->getUnicodeToEmoji($carrier);
		
		if (true == $this->useMobileSession && $carrier != 'PC') {
			$this->_setMobileSession();
		}
	}
	
	function getCarrier($userAgent = null) {
		if (!is_null($userAgent)) {
			foreach ($this->_agents as $carrier => $regex) {
				foreach ($regex as $reg) {
					if (preg_match($reg, $userAgent)) {
						return $this->carrier = $carrier;
					}
				}
			}
			if (is_null($this->carrier)) {
				return $this->carrier = 'PC';
			}
		} else {
			return null;
		}
	}
	
	function getResolution($carrier = null) {
		$resolution = $this->resolution;
		if (is_null($carrier)) {
			return $resolution;
		}
		$_resolution = array();
		if ($carrier === 'softbank') {
			$_resolution = explode("*", env('HTTP_X_JPHONE_DISPLAY'));
		}
		elseif ($carrier === 'kddi') {
			$_resolution = explode(",", env('HTTP_X_UP_DEVCAP_SCREENPIXELS'));
		}
		if (2 === count($_resolution)) {
			return $_resolution;
		} else {
			return $resolution;
		}
	}
	
	function getUnicodeToEmoji($carrier = null) {
		App::import('Model', 'mobile_plugin.emoji');
		$Emoji = new Emoji;
		$carrierFields = array(
			'docomo' => 'docomo_sjis',
			'softbank' => 'softbank_utf',
			'kddi' => 'kddi_sjis',
		);
		if ($carrier === 'docomo' || $carrier === 'softbank' || $carrier === 'kddi') {
			if (!$emojisAndUnicodes = Cache::read('unicode2' . $carrierFields[$carrier])) {
				$tmpArray = $Emoji->find('all', array('fields' => array('id', $carrierFields[$carrier])));
				$unicodes = Set::extract('/Emoji/id', $tmpArray);
				foreach ($unicodes as $value) {
						$emojisAndUnicodes['unicode'][] = '&#x' . $value . ';';
					}
				unset($unicodes);
				$emojis = Set::extract('/Emoji/' . $carrierFields[$carrier], $tmpArray);
				unset($tmpArray);
				foreach ($emojis as $key => $value) {
						$packedStr = '';
						if (isset($value) && $value != '') {
							if (strpos($value, ',') === FALSE) {
								if ($carrier == 'docomo') {
									$packedStr = pack('H4', $value);
								} else {
									$packedStr = '&#x' . $value . ';';
								}
							} else {
								$tmpUnicodes = explode(',', $value);
								if ($carrier == 'docomo') {
									foreach ($tmpUnicodes as $unicode) {
										$packedStr .= pack('H4', $unicode);
									}
								} else {
									foreach ($tmpUnicodes as $unicode) {
										$packedStr .= '&#x' . $unicode . ';';
									}
								}
								unset($tmpUnicodes);
							}
							$emojisAndUnicodes['emoji'][$key] = $packedStr;
						} else {
							if ($carrier == 'docomo') {
								$emojisAndUnicodes['emoji'][$key] = pack('H*', '81AC');
							}
						}
					}
				Cache::write('unicode2' . $carrierFields[$carrier], $emojisAndUnicodes);
			}
			return $emojisAndUnicodes;
		} elseif ($carrier == 'PC') {
			if (!$gifsAndUnicodes = Cache::read('unicode2gif')) {
				$tmpArray = $Emoji->find('all', array('fields' => array('id', 'gif')));
				$unicodes = Set::extract('/Emoji/id', $tmpArray);
				foreach ($unicodes as $value) {
					$gifsAndUnicodes['unicode'][] = '&#x' . $value . ';';
				}
				unset($unicodes);
				$gifs = Set::extract('/Emoji/gif', $tmpArray);
				unset($tmpArray);
				foreach ($gifs as $key => $value) {
					$imgTag = '';
					if (isset($value) && $value != '') {
						$imgTag = '<img src="' . $value . '">';
					}
					$gifsAndUnicodes['gif'][$key] = $imgTag;
				}
				Cache::write('unicode2gif', $gifsAndUnicodes);
			}
			return $gifsAndUnicodes;
		} else {
			return null;
		}
	}
	
	function inputConvert($input = null) {
		App::import('Model', 'mobile_plugin.emoji');
		$Emoji = new Emoji;
		$carrier = $this->carrier;
		if (is_array($input)) {
			$output = array_map(array($this, __METHOD__), $input);
		} elseif ($carrier == 'PC') {
			$output = $input;
		} elseif ($carrier === 'docomo' || $carrier === 'softbank' || $carrier === 'kddi') {
			$carrierFields = array(
				'docomo' => 'docomo_sjis',
				'softbank' => 'softbank_utf',
				'kddi' => 'kddi_sjis',
			);
			$field = $carrierFields[$carrier];
			$cacheName = $field . 'ToUnicodes';
			if (!$emoijsAndUnicodes = Cache::read($cacheName)) {
				$tmpArray = $Emoji->find('all', array('fields' => array('id', $field)));
				$unicodes = Set::extract('/Emoji/id', $tmpArray);
				foreach ($unicodes as $value) {
					$emojisAndUnicodes['unicode'][] = '&#x' . $value . ';';
				}
				unset($unicodes);
				$emojis = Set::extract('/Emoji/' . $field, $tmpArray);
				foreach ($emojis as $key => $value) {
					if (isset($value) && $value != '') {
						$emojisAndUnicodes['emoji'][] = 'BAD+' . $value;
					} else {
						$emojisAndUnicodes['emoji'][] = '';
					}
				}
				unset($emojis);
				Cache::write($cacheName, $emojisAndUnicodes);
			}
			mb_substitute_character('long');
			$output = mb_convert_encoding($input, 'UTF-8', 'SJIS');
			$output = str_replace($emojisAndUnicodes['emoji'], $emojisAndUnicodes['unicode'], $output);
			$output = preg_replace('/BAD\+([0-9A-F]{4})/', '', $output);
		} else {
			mb_substitute_character('long');
			$output = mb_convert_encoding($input, 'UTF-8', 'SJIS');
			$output = preg_replace('/BAD\+([0-9A-F]{4})/', '', $output);
		}
		return $output;
	}
	
	function _setMobileSession() {
		if (!isset($_SESSION)) {
			Configure::write('Session.save', 'mplugin');
			ini_set('session.use_trans_sid', 1);//上手く行かない...
			ini_set('session.use_only_cookies', 0);
			ini_set('session.use_cookies', 0);
			ini_set('session.name', 'mplugin');
			debug(ini_get('url_rewriter.tags'));
		}
	}
}
?>