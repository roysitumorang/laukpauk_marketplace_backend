<?php

namespace Application\Library\Filter\Sanitize;

/**
 * The "string" sanitizer as it behaved in Phalcon 3.
 *
 * Phalcon 3 implemented "string" with FILTER_SANITIZE_STRING (deprecated in
 * PHP 8.1): it HTML-encodes single and double quotes and strips tags.
 * Phalcon 5 redefined "string" as htmlspecialchars(), which also encodes
 * &, < and >. Stored password hashes, names and other columns were produced
 * with the old behaviour, so the application registers this sanitizer under
 * the "string" name to keep login and lookups working unchanged.
 */
class LegacyString {
	function __invoke($input): string {
		if ($input === null || is_array($input) || (is_object($input) && !method_exists($input, '__toString'))) {
			return '';
		}
		$value = str_replace(["'", '"'], ['&#39;', '&#34;'], (string) $input);
		// FILTER_SANITIZE_STRING treats "< tag" as a tag too; strip_tags() does not.
		$value = preg_replace('/<\s+/', '<', $value);
		return strip_tags($value);
	}
}
