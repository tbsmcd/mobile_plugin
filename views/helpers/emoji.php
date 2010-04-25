<?php
class EmojiHelper extends AppHelper {
	public $unicode2Emoji = null;
	public $carrier = null;
	//docomo:use use_trans_sid only for DOCOMO.
	//mobile:use_trans_sid only for Mobile(Keitai).
	//all:use_trans_sid for all.
	
	public $sidMode = null;
	
	function afterRender() {
		if ($this->carrier != 'PC') {
			$out = ob_get_clean();
			$out = mb_convert_kana($out, 'rak', 'UTF-8');
			$out = mb_convert_encoding($out, 'SJIS', 'UTF-8');
			if (!is_null($this->unicode2Emoji)) {
				$out = str_replace($this->unicode2Emoji['unicode'], $this->unicode2Emoji['emoji'], $out);
			} else {
				$out = str_replace($this->unicode2Emoji['unicode'], $this->unicode2Emoji['gif'], $out);
			}
			echo $out;
		} else {
			$out = ob_get_clean();
			$out = str_replace($this->unicode2Emoji['unicode'], $this->unicode2Emoji['gif'], $out);
			echo $out;
		}
	}
	
	function addSid() {
		if (!is_null($this->sidMode)) {
			if ($this->sidMode == 'docomo') {
				if ($this->carrier == 'docomo') {
					return '?' . session_name() . '=' . session_id();
				}
			} elseif ($this->sidMode == 'mobile') {
				if ($this->carrier == 'docomo' || $this->carrier == 'kddi' || $this->carrier == 'softbank') {
					return '?' . session_name() . '=' . session_id();
				}
			} elseif ($this->sidMode == 'all') {
				return '?' . session_name() . '=' . session_id();
			}
		}
	}

}

?>