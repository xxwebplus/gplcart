<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Checks critical system requirements
 */
function gplcart_setup_requirements()
{
    if (version_compare(PHP_VERSION, '5.4.0') < 0) {
        exit('Your PHP installation is too old. GPLCart requires at least PHP 5.4.0');
    }

    if (!function_exists('mb_internal_encoding')) {
        exit('"mbstring" must be enabled in your PHP settings');
    }
}

/**
 * Check and fix if needed some importan server vars
 */
function gplcart_setup_server()
{
    if (GC_CLI) {
        return null;
    }

    if (!isset($_SERVER['SERVER_PROTOCOL']) || !in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1'), true)) {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    }

    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
        if (isset($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }

    if (!isset($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
    }

    $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
}

/**
 * Sets up PHP INI options
 */
function gplcart_setup_php()
{
    if (GC_CLI) {
        $bytes = gplcart_bytes('1G');
        $limit = trim(ini_get('memory_limit'));
        if ($limit != -1 && $bytes < 1024 * 1024 * 1024) {
            ini_set('memory_limit', '1G');
        }

        ini_set('html_errors', 'off');
    } else {
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cache_limiter', '');
        ini_set('session.cookie_httponly', '1');
    }
}

/**
 * Provides class autoloading functionality
 * @return boolean
 */
function gplcart_setup_autoload()
{
    return spl_autoload_register(function($namespace) {

        // Skip foreign namespaces
        if (strpos($namespace, 'gplcart\\') !== 0) {
            return false;
        }

        // Flip slashes and remove "gplcart/" from the beginning
        $path = substr(str_replace('\\', '/', $namespace), 8);
        $file = strpos($path, 'tests') === 0 ? GC_ROOT_DIR : GC_SYSTEM_DIR;
        $file .= "/$path.php";

        if (file_exists($file)) {
            require $file;
            return true;
        }

        // Check lowercase class name to prevent "file not found" for
        // classes like gplcart\\modules\\test_module\\TestModule
        $lowerfile = strtolower($file);

        foreach (glob(dirname($file) . '/*') as $file) {
            if (strtolower($file) == $lowerfile) {
                require $file;
                return true;
            }
        }

        return false;
    });
}
