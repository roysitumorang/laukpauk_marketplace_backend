<?php

namespace Application\Library\Utils;

use RuntimeException;
use Transliterator;

/**
 * Slug generator.
 *
 * Port of the Phalcon 3 incubator Phalcon\Utils\Slug that this application
 * used, which is not part of Phalcon 5.
 */
class Slug {
	static function generate(string $string, array $replace = [], string $delimiter = '-'): string {
		if (!extension_loaded('intl')) {
			throw new RuntimeException('intl module not loaded');
		}
		$old_locale = setlocale(LC_ALL, '0');
		setlocale(LC_ALL, 'en_US.UTF-8');
		if ($replace) {
			$string = str_replace(array_keys($replace), array_values($replace), $string);
		}
		$transliterator = Transliterator::create('Any-Latin; Latin-ASCII');
		$string         = $transliterator->transliterate(mb_convert_encoding(htmlspecialchars_decode($string), 'UTF-8', 'auto'));
		$string         = preg_replace('#[^\pL\d]+#u', '-', $string);
		$string         = trim($string, '-');
		$clean          = preg_replace('~[^-\w]+~', '', $string);
		$clean          = strtolower($clean);
		$clean          = preg_replace('#[\/_|+ -]+#', $delimiter, $clean);
		$clean          = trim($clean, $delimiter);
		if ($old_locale !== false) {
			setlocale(LC_ALL, $old_locale);
		}
		return $clean;
	}
}
