<?php
App::import('Core', 'HttpSocket');
class getEmojiShell extends Shell {
	public $uses = array('Emoji');
//	public $emoji4unicode = 'http://emoji4unicode.googlecode.com/svn/trunk/data/emoji4unicode.xml';
	public $emoji4gif = 'http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html';

	function main() {
		if (!$this->__saveEmoji()) {
			exit();
		}
	}

	function __saveEmoji() {
		$this->out('Start Save Emoji');
		$this->Emoji->begin();
		
		$this->out('TRUNCATE TABLE `emojis`');
		$this->Emoji->query('TRUNCATE TABLE `emojis`');

		$this->out('Get Emoji Data');
	    
		$HttpSocket = new HttpSocket();
	    $raw_data = $HttpSocket->get($this->emoji4gif);
		if ($HttpSocket->response['status']['code'] !== 200) {
			$this->out('Failure. Download emoji4unicode.xml');
			return false;
		}

	    $pattern = '/<tr[^>]*>(.+)<\/tr>/';
		preg_match_all($pattern, $raw_data, $tr_list);
		foreach ($tr_list[1] as $tr) {
		    if (!strpos($tr, 'category') and !strpos($tr, 'subcategory')) {
			    $pattern = '/<td[^>]*>(.+)<\/td><td[^>]*>(.+)<\/td><td[^>]*>(.+)<\/td><td[^>]*>(.+)<\/td><td[^>]*>(.+)<\/td><td[^>]*>(.+)<\/td><td[^>]*>(.+)<\/td>/';
				preg_match($pattern, $tr, $td_list);

			    $id = substr($td_list[7], 2);
			    if (preg_match('/[A-F0-9]+/', $id)) {

			    	for ($carrier = 6; $carrier > 3; $carrier--) {

			    		$td_list[$carrier] = str_replace('+U+', ',', $td_list[$carrier]);
			    		$td_list[$carrier] = str_replace('+SJIS-', ',', $td_list[$carrier]);
			    		$td_list[$carrier] = str_replace('+JIS-', ',', $td_list[$carrier]);

			    		$code[$carrier] = array(
			    			'utf' => null,
			    			'sjis' => null,
			    			'jis' => null,
			    		);

				    	$pattern = '/U\+([A-F0-9\,]+)/';
						preg_match($pattern, $td_list[$carrier], $utf);
						if (isset($utf[1])) {
							$code[$carrier]['utf'] = $utf[1];
						}

				    	$pattern = '/SJIS-([A-F0-9\,]+)/';
						preg_match($pattern, $td_list[$carrier], $sjis);
						if (isset($sjis[1])) {
							$code[$carrier]['sjis'] = $sjis[1];
						}

				    	$pattern = '/[^S]+JIS-([A-F0-9\,]+)/';
						preg_match($pattern, $td_list[$carrier], $jis);
						if (isset($jis[1])) {
							$code[$carrier]['jis'] = $jis[1];
						}
					}

					$gif = null;

					for ($img_carrier = 6; $img_carrier > 3; $img_carrier--) {
						if (strpos($td_list[$img_carrier], '>+<img') === false) {
							$pattern = '/<img src=(http[^ >]+)[^>]*>.*/';
							preg_match($pattern, $td_list[$img_carrier], $img_url);
							if (isset($img_url[1])) {
								$gif = $img_url[1];
								break;
							}
						}
					}

				    $data = array(
				    	'id' => $id,
				    	'softbank_utf' => $code[6]['utf'],
				    	'softbank_sjis' => $code[6]['sjis'],
				    	'kddi_utf' => $code[5]['utf'],
				    	'kddi_sjis' => $code[5]['sjis'],
				    	'kddi_jis' => $code[5]['jis'],
				    	'docomo_utf' => $code[4]['utf'],
				    	'docomo_sjis' => $code[4]['sjis'],
				    	'docomo_jis' => $code[4]['jis'],
				    	'gif' => $gif,
				    );

	    			if ($this->Emoji->save($data)) {
						echo 'o';
						$this->Emoji->id = null;
					} else {
						$this->out('');
						$this->out('Error: ' . implode(' ', $data));
						$this->Emoji->rollback();
						return false;
					}
			    }
		    }
	    }
		
		$this->Emoji->commit();
		$this->out('');
		$this->out('Success! Save Emoji');

		return true;
	}
}
?>