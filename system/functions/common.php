<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Deletes a variable from the static storage
 * @param string|null $key
 */
function gplcart_cache_clear($key = null)
{
    gplcart_cache($key, null, true);
}

/**
 * Central static variable storage
 * Taken from Drupal
 * @param string $name
 * @param mixed $default_value
 * @param boolean $reset
 * @return mixed
 */
function &gplcart_cache($name, $default_value = null, $reset = false)
{
    static $data = array(), $default = array();

    if (isset($data[$name]) || array_key_exists($name, $data)) {
        if ($reset) {
            $data[$name] = $default[$name];
        }

        return $data[$name];
    }

    if (isset($name)) {
        if ($reset) {
            return $data;
        }

        $default[$name] = $data[$name] = $default_value;
        return $data[$name];
    }

    foreach ($default as $name => $value) {
        $data[$name] = $value;
    }

    return $data;
}

/**
 * Converts human readable file syzes to numeric bytes
 * @param string $value
 * @return integer
 */
function gplcart_to_bytes($value)
{
    $unit = strtolower(substr($value, -1, 1));
    $value = (int) $value;
    switch ($unit) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
}

/**
 * Validates a domain name, e.g domain.com
 * @param string $domain
 * @return boolean
 */
function gplcart_valid_domain($domain)
{
    $pattern = '/^(?!\-)'
            . '(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.)'
            . '{1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';

    return (bool) preg_match($pattern, $domain);
}

/**
 * Whether the URL is absolute, e.g starts with http://, https:// etc
 * @param string $url
 * @return boolean
 */
function gplcart_absolute_url($url)
{
    $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

    return (bool) preg_match($pattern, $url);
}

/**
 * Parses and extracts arguments from a string
 * @param string $string
 * @param string $pattern
 * @return boolean|array
 */
function gplcart_parse_pattern($string, $pattern)
{
    $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

    if (preg_match($pattern, $string, $params)) {
        array_shift($params);
        return array_values($params);
    }

    return false;
}

/**
 * Validates $_SERVER['HTTP_HOST'] variable
 * @return boolean
 */
function gplcart_valid_host($host)
{
    return (strlen($host) <= 1000 //
            && substr_count($host, '.') <= 100 //
            && substr_count($host, ':') <= 100 //
            && preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host) === 1);
}

/**
 * Generates an array of time zones and their local data
 * @return array
 */
function gplcart_timezones()
{
    $zones = array();
    $timestamp = GC_TIME;
    $default_timezone = date_default_timezone_get();

    foreach (timezone_identifiers_list() as $zone) {
        date_default_timezone_set($zone);
        $zones[$zone] = '(UTC/GMT ' . date('P', $timestamp) . ') ' . $zone;
    }

    date_default_timezone_set($default_timezone);
    return $zones;
}

/**
 * Wrapper for Kint's ddd() debugger
 * @param mixed $data
 */
function ddd($data)
{
    require_once GC_LIBRARY_DIR . '/kint/Kint.class.php';

    if (Kint::enabled()) {
        Kint::dump($data);
        exit;
    }
}

/**
 * Wrapper for Kint's d() debugger
 * @param mixed $data
 */
function d($data)
{
    require_once GC_LIBRARY_DIR . '/kint/Kint.class.php';

    if (Kint::enabled()) {
        Kint::dump($data);
    }
}

/**
 * Extracts an array of components from strings like ">= 1.0.0"
 * @param string $data
 * @return array
 */
function gplcart_version_components($data)
{
    $string = str_replace(' ', '', $data);
    
    $matches = array();
    preg_match_all('/(^(==|=|!=|<>|>|<|>=|<=)?(?=\d))(.*)/', $string, $matches);

    if (empty($matches[3][0])) {
        return array();
    }

    $operator = empty($matches[2][0]) ? '=' : $matches[2][0];
    return array($operator, $matches[3][0]);
}

/**
 * Requires a library file
 * @param string $path
 * @todo Remove
 */
function gplcart_require_library($path)
{
    $file = GC_LIBRARY_DIR . "/$path";

    if (!is_readable($file)) {
        throw new \RuntimeException("Could not read library file $file");
    }

    require_once $file;
}
