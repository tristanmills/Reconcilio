<?php

namespace Reconcilio;

class Reconcilio {

	/**
	 * Properly format an HTML file.
	 *
	 * @param string $file The path to the HTML file.
	 * @param bool $strict Format additional elements.
	 */
	public static function repairFile($file, $strict = false) {

		// TODO: Tags breaks some times.
		$originalEmptyTags = array('<span', 'span>', '<i>', '<i ', '/i>');
		$tmpEmptyTags = array('<tidyspan', 'tidyspan>', '<tidyi>', '<tidyi ', '/tidyi>');

		$html = file_get_contents($file);
		$html = str_replace($originalEmptyTags, $tmpEmptyTags, $html);

		$config = array(
			'alt-text' 	          => '',
			'drop-empty-paras'    => false,
			'new-blocklevel-tags' => 'article aside audio details dialog figcaption figure footer header hgroup nav section source summary track video',
			'new-empty-tags'      => 'command embed keygen source track wbr',
			'new-inline-tags'     => 'canvas command data datalist embed keygen mark meter output progress time wbr tidyspan tidyi',
			'output-html'         => true,
			'break-before-br'     => true,
			'indent'              => true,
			'indent-spaces'       => 4,
			'sort-attributes'     => 'alpha',
			'wrap'                => 0,
		);

		$Tidy = new \tidy;
		$Tidy->parseString($html, $config);

		$html = $Tidy->value;

		$html = str_replace($tmpEmptyTags, $originalEmptyTags, $html);

		$html = preg_replace('| {4}|', "\t", $html);
		$html = preg_replace('|<li(.*?)>\s*<a|', '<li$1><a', $html);
		$html = preg_replace('|<\/a>\s*<\/li|', '</a></li', $html);
		$html = preg_replace('|\s*<\/script>|', '</script>', $html);

		if ($strict) {

			$html = self::repairOpenNocripts($html);
			$html = self::repairCloseNocripts($html);
			$html = self::repairOpenComments($html);
			$html = self::repairCloseComments($html);
			$html = self::repairOpenScripts($html);
			$html = self::repairCloseScripts($html);

		} else {

			$html = preg_replace('|^(\t*)(.*?)><\!--|m', '$1$2>' ."\n". '$1<!--', $html);
			$html = preg_replace('|^(\t*)<\!--(.*?)<\!\[|ms', '$1<!--$2$1<![', $html);
			$html = preg_replace('|^(\t*)(.*?)><script|m', '$1$2>' ."\n". '$1<script', $html);
			$html = preg_replace('|^(\t*)(.*?)><noscript|m', '$1$2>' ."\n". '$1<noscript', $html);
			$html = preg_replace('|^(\t*)(.*?)><\/noscript|m', '$1$2>' ."\n". '$1</noscript', $html);

		}

		return $html;

	}

	/**
	 * Indents opening script tags properly
	 *
	 * @param string $html The HTML to be tidied.
	 * @return string Returns tidied HTML.
	 */
	public static function repairOpenScripts($html) {

		$count = preg_match_all('|(\t*)(.*?)><script|m', $html, $matches);

		if ($count >= 1) {

			$html = preg_replace('|^(\t*)(.*?)><script|m', '$1$2>' ."\n". '$1<script', $html);

			return self::repairOpenScripts($html);

		} else {

			return $html;

		}

	}

	/**
	 * Indents closing script tags properly
	 *
	 * @param string $html The HTML to be tidied.
	 * @return string Returns tidied HTML.
	 */
	public static function repairCloseScripts($html) {

		return $html;

	}

	/**
	 * Indents opening noscript tags properly
	 *
	 * @param string $html The HTML to be tidied.
	 * @return string Returns tidied HTML.
	 */
	public static function repairOpenNocripts($html) {

		$count = preg_match_all('|(\t*)(.*?)><noscript|m', $html, $matches);

		if ($count >= 1) {

			$html = preg_replace('|^(\t*)(.*?)><noscript|m', '$1$2>' ."\n". '$1<noscript', $html);

			return self::repairOpenNocripts($html);

		} else {

			return $html;

		}

	}

	/**
	 * Indents closing noscript tags properly
	 *
	 * @param string $html The HTML to be tidied.
	 * @return string Returns tidied HTML.
	 */
	public static function repairCloseNocripts($html) {

		$count = preg_match_all('|(\t*)(.*?)><\/noscript|m', $html, $matches);

		if ($count >= 1) {

			$html = preg_replace('|^(\t*)(.*?)><\/noscript|m', '$1$2>' ."\n". '$1</noscript', $html);

			return self::repairCloseNocripts($html);

		} else {

			return $html;

		}

	}

	/**
	 * Indents opening comments properly
	 *
	 * @param string $html The HTML to be tidied.
	 * @return string Returns tidied HTML.
	 */
	public static function repairOpenComments($html) {

		$count = preg_match_all('|(\t*)(.*?)><\!--|m', $html, $matches);

		if ($count >= 1) {

			$html = preg_replace('|^(\t*)(.*?)><\!--|m', '$1$2>' ."\n". '$1<!--', $html);

			return self::repairOpenComments($html);

		} else {

			return $html;

		}

	}

	/**
	 * Indents closing comments properly
	 *
	 * @param string $html The HTML to be tidied.
	 * @return string Returns tidied HTML.
	 */
	public static function repairCloseComments($html) {

		$count = preg_match_all('|(\t*)<\!--(.*?)<\!\[|ms', $html, $matches);

		if ($count >= 1) {

			$html = preg_replace('|^(\t*)<\!--(.*?)<\!\[|ms', '$1<!--$2$1<![', $html);

			return self::repairCloseNocripts($html);

		} else {

			return $html;

		}

	}

}
