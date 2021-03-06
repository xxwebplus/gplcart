<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Returns a hashed string
 * @param string $string
 * @param string $salt
 * @param integer $iterations
 * @return string
 */
function gplcart_string_hash($string, $salt = '', $iterations = 10)
{
    if ($salt === '') {
        $salt = gplcart_string_random();
    }

    if (!empty($iterations)) {
        $salt = sprintf("$2a$%02d$", $iterations) . $salt;
    }

    return crypt($string, $salt);
}

/**
 * Generates a random string
 * @param integer $size
 * @return string
 */
function gplcart_string_random($size = 32)
{
    return bin2hex(openssl_random_pseudo_bytes($size));
}

/**
 * Compares two hashed strings
 * @param string $str1
 * @param string $str2
 * @return boolean
 */
function gplcart_string_equals($str1, $str2)
{
    settype($str1, 'string');
    settype($str2, 'string');

    if (function_exists('hash_equals')) {
        return hash_equals($str1, $str2);
    }

    if (strlen($str1) != strlen($str2)) {
        return false;
    }

    $res = $str1 ^ $str2;
    $ret = 0;

    for ($i = strlen($res) - 1; $i >= 0; $i--) {
        $ret |= ord($res[$i]);
    }

    return !$ret;
}

/**
 * Splits a text by new line
 * @param string $string
 * @return array
 */
function gplcart_string_explode_multiline($string)
{
    return array_filter(array_map('trim', explode("\n", str_replace("\r", "", $string))));
}

/**
 * Splits a text by whitespace
 * @param string $string
 * @param int|null $limit
 * @return array
 */
function gplcart_string_explode_whitespace($string, $limit = null)
{
    $prepared = preg_replace('/\s+/', ' ', trim($string));
    return isset($limit) ? explode(' ', $prepared, $limit) : explode(' ', $prepared);
}

/**
 * Whether the string is valid regular expression
 * @param string
 * @return bool
 */
function gplcart_string_is_regexp($string)
{
    return @preg_match("~$string~", null) !== false;
}

/**
 * Formats a string by replacing a placeholder
 * @param string $string
 * @param array $arguments
 * @return string
 */
function gplcart_string_format($string, array $arguments = array())
{
    foreach ($arguments as $key => $value) {
        switch ($key[0]) {
            case '@':
                $arguments[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                break;
            case '!':
                // Html
                break;
            case '%':
            default:
                $arguments[$key] = '<i class="placeholder">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</i>';
        }
    }

    return strtr($string, $arguments);
}

/**
 * Replaces placeholder in the string
 * @param string $pattern
 * @param array $placeholders
 * @param array $data
 * @return string
 */
function gplcart_string_replace($pattern, array $placeholders, array $data)
{
    $pairs = array();
    foreach ($placeholders as $placeholder => $key) {
        if (isset($data[$key]) && !is_array($data[$key])) {
            $pairs[$placeholder] = $data[$key];
        }
    }

    return strtr($pattern, $pairs);
}

/**
 * Encode a string with URL-safe Base64
 * @param string $string
 * @return string
 */
function gplcart_string_encode($string)
{
    return str_replace('=', '', strtr(base64_encode($string), '+/', '-_'));
}

/**
 * Decode a string with URL-safe Base64
 * @param string $string
 * @return string
 */
function gplcart_string_decode($string)
{
    $remainder = strlen($string) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $string .= str_repeat('=', $padlen);
    }

    return base64_decode(strtr($string, '-_', '+/'));
}

/**
 * Creates a URL slug
 * @param string $text
 * @param string $separator
 * @param string $empty
 * @return string
 */
function gplcart_string_slug($text, $separator = '-', $empty = '')
{
    $flip = $separator == '-' ? '_' : '-';
    $text = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $text);
    $text = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s.]+!u', '', mb_strtolower($text));
    $text = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $text);
    $text = trim($text, $separator);

    return $text === '' ? $empty : $text;
}

/**
 * Replaces variables in a string and applies optional "filter" functions
 * Example: %name|strtoupper will be replaced with NAME
 * @param string $text
 * @param array $data
 * @param string $varprefix
 * @return string
 */
function gplcart_string_render($text, array $data, $varprefix = '%')
{
    $functions = array();
    foreach (preg_split('/[\s]+/', $text) as $string) {

        $string = trim($string);
        if (strpos($string, $varprefix) !== 0 || strpos($string, '|') === false) {
            continue;
        }

        $function = substr($string, strrpos($string, '|') + 1);
        if (!empty($function) && function_exists($function)) {
            $placeholder = substr($string, 0, -strlen($function) - 1);
            $functions[$placeholder] = $function;
        }
    }

    foreach ($data as $key => $replace) {
        $search = "$varprefix$key";
        if (isset($functions["$varprefix$key"])) {
            $search .= "|{$functions["$varprefix$key"]}";
            $replace = $functions["$varprefix$key"]($replace);
        }

        $text = str_replace($search, $replace, $text);
    }

    return trim(preg_replace('/(\s)\s+/', '\\1', $text));
}
