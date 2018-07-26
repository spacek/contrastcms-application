<?php

namespace ContrastCms\Application;

class StringHelper
{

	public static function htmlTruncate($s, $maxLen, $append = "\xE2\x80\xA6")
	{
		if (iconv_strlen($s, 'UTF-8') > $maxLen) {
			$maxLen = $maxLen - iconv_strlen($append, 'UTF-8');
			if ($maxLen < iconv_strlen($append, 'UTF-8')) {
				return $append;
			}
			$separators = array(' ', ',', '.', ';', '?', '!', ':');
			$pos = 0;
			$s = iconv('UTF-8', 'windows-1250//TRANSLIT', $s);
			$INITAL_S = $s;
			$append = iconv('UTF-8', 'windows-1250//TRANSLIT', $append);

			$customWhitespaces = array(chr(0x09), chr(0x0a), chr(0x0d), chr(0x00), chr(0x0b));
			foreach ($customWhitespaces AS $customWhitespace) {
				$s = trim($s, $customWhitespace);
			}

			$length = 0;
			$tags = array();
			for ($i = 0; $i < strlen($s) && $length < $maxLen; $i++) {
				switch ($s[$i]) {
					case '<':
						$start = $i + 1;
						while ($i < strlen($s) && $s[$i] != '>' && !ctype_space($s[$i])) {
							$i++;
						}
						$tag = strtolower(substr($s, $start, $i - $start));

						$in_quote = '';
						while ($i < strlen($s) && ($in_quote || $s[$i] != '>')) {
							if (($s[$i] == '"' || $s[$i] == "'") && !$in_quote) {
								$in_quote = $s[$i];

							} elseif ($in_quote == $s[$i]) {
								$in_quote = '';
							}
							$i++;
						}
						if ($s[$start] == '/') {
							array_shift($tags);

						} elseif ($s[$i - 1] != '/') {
							array_unshift($tags, $tag);
						}
						break;

					case '&':
						$length++;
						while ($i < strlen($s) && $s[$i] != ';') {
							$i++;
						}
						break;

					default:
						$length++;

						if (in_array($s[$i], $separators)) {
							if (($s[$i] != $s[$i + 1]) && ($s[$i - 1] != $s[$i])) {
								$pos = $i;
							}
						}
				}
			}

			if ($length >= $maxLen) {

				$s = substr($s, 0, $i);

				$enclosingTags = "";
				if ($tags) {
					$enclosingTags .= "</" . implode("></", $tags) . ">";
				}

				$s_beforeInnerEnclosingTags = $s;
				$innerEnclosingTags = "";
				while (substr(rtrim($s_beforeInnerEnclosingTags), -1, 1) == ">") {
					$innerEnclosingTags = strrchr($s_beforeInnerEnclosingTags, "<");
					$s_beforeInnerEnclosingTags = substr($s_beforeInnerEnclosingTags, 0, strlen($s_beforeInnerEnclosingTags) - strlen($innerEnclosingTags));
				}

				if ($append == iconv('UTF-8', 'windows-1250//TRANSLIT', "\xE2\x80\xA6")) {
					$s_beforeInnerEnclosingTags = rtrim($s_beforeInnerEnclosingTags, '.');
				}

				if (($pos > 0) && (!in_array(substr($INITAL_S, strlen($s_beforeInnerEnclosingTags), 1), $separators))) {
					$s_beforeInnerEnclosingTags = substr($s_beforeInnerEnclosingTags, 0, $pos);
				} elseif (($pos > 0) && (in_array(substr($INITAL_S, strlen($s_beforeInnerEnclosingTags), 1), $separators)) && ((substr($INITAL_S, strlen($s_beforeInnerEnclosingTags), 1) == ((substr($INITAL_S, strlen($s_beforeInnerEnclosingTags) + 1, 1)))))) {
					$s_beforeInnerEnclosingTags = substr($s_beforeInnerEnclosingTags, 0, $pos);
				}

				$s = $s_beforeInnerEnclosingTags . $append . $innerEnclosingTags . $enclosingTags;
			}

			$s = iconv('windows-1250', 'UTF-8//TRANSLIT', $s);
		}

		return $s;
	}
}