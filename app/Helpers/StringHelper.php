<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * String Helper Utility Class
 * 
 * Provides static helper methods for common string manipulations
 * used throughout the LunaOS application.
 *
 * @package App\Helpers
 * @author LunaOS Development Team
 * @since 1.0.0
 */
final class StringHelper
{
    /**
     * Convert a string into a URL-safe slug.
     *
     * Transforms the input string into a lowercase, hyphen-separated
     * slug suitable for URLs. Handles special characters, multiple
     * spaces, and ensures consistent output.
     *
     * @param string $value The string to convert
     * @param string $separator The separator to use (default: '-')
     * @return string The slugified string
     *
     * @example
     * ```php
     * StringHelper::slugify('Hello World!'); // Returns 'hello-world'
     * StringHelper::slugify('My Awesome Post', '_'); // Returns 'my_awesome_post'
     * ```
     */
    public static function slugify(string $value, string $separator = '-'): string
    {
        if ($value === '') {
            return '';
        }

        // Convert HTML entities to their corresponding characters
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        // Convert to lowercase
        $value = mb_strtolower($value, 'UTF-8');

        // Replace whitespace with separator
        $value = preg_replace('/\s+/u', $separator, $value);

        // Remove all non-alphanumeric characters except the separator
        $pattern = '/[^a-z0-9' . preg_quote($separator, '/') . ']/u';
        $value = preg_replace($pattern, '', $value);

        // Remove duplicate separators
        $value = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $value);

        // Trim separators from beginning and end
        $value = trim($value, $separator);

        return $value;
    }

    /**
     * Convert a string to title case.
     *
     * Capitalizes the first character of each word while handling
     * special cases like apostrophes and hyphenated words.
     *
     * @param string $value The string to convert
     * @return string The title-cased string
     *
     * @example
     * ```php
     * StringHelper::titleCase('hello world'); // Returns 'Hello World'
     * StringHelper::titleCase("john's book"); // Returns "John's Book"
     * StringHelper::titleCase('self-aware'); // Returns 'Self-Aware'
     * ```
     */
    public static function titleCase(string $value): string
    {
        if ($value === '') {
            return '';
        }

        // Trim whitespace
        $value = trim($value);

        // Convert to lowercase first for consistent results
        $value = mb_strtolower($value, 'UTF-8');

        // Use mb_convert_case for proper Unicode support
        $value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');

        // Handle special cases for common words that should remain lowercase
        // (except when they're the first word)
        $lowercaseWords = ['a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'in', 'nor', 'of', 'on', 'or', 'so', 'the', 'to', 'up', 'yet'];
        
        $words = preg_split('/(\s+|-)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        if ($words === false) {
            return $value;
        }

        $result = '';
        $isFirstWord = true;

        foreach ($words as $word) {
            if (preg_match('/^\s+$/', $word) || $word === '-') {
                $result .= $word;
                continue;
            }

            $lowerWord = mb_strtolower($word, 'UTF-8');

            if (!$isFirstWord && in_array($lowerWord, $lowercaseWords, true)) {
                $result .= $lowerWord;
            } else {
                $result .= $word;
            }

            $isFirstWord = false;
        }

        return $result;
    }
}
