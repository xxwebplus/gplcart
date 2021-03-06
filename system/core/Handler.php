<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use InvalidArgumentException;

/**
 * Provides methods to retrieve and execute handlers
 */
class Handler
{

    /**
     * Call a handler
     * @param array $handlers
     * @param string|null $handler_id
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function call($handlers, $handler_id, $method, $arguments = array())
    {
        try {
            $callback = static::get($handlers, $handler_id, $method);
            return call_user_func_array($callback, $arguments);
        } catch (\Exception $ex) {
            throw new InvalidArgumentException($ex->getMessage());
        }
    }

    /**
     * Returns a handler
     * @param array $handlers
     * @param string|null $handler_id
     * @param string $name
     * @return object|array
     * @throws InvalidArgumentException
     */
    public static function get($handlers, $handler_id, $name)
    {
        $callable = static::getCallable($handlers, $handler_id, $name);

        if (is_array($callable)) {

            if (is_callable($callable)) {
                $callable[0] = Container::get($callable[0]);
                return $callable;
            }

            throw new InvalidArgumentException(implode('::', $callable) . ' is not callable');
        }

        if ($callable instanceof \Closure) {
            return $callable;
        }

        throw new InvalidArgumentException('Unexpected handler format');
    }

    /**
     * Returns a callable data from the handler array
     * @param array $handlers
     * @param string|null $handler_id
     * @param string $name
     * @return array|object
     * @throws InvalidArgumentException
     */
    protected static function getCallable($handlers, $handler_id, $name)
    {
        if (isset($handler_id)) {

            if (empty($handlers[$handler_id]['handlers'][$name])) {
                throw new InvalidArgumentException("No such handler name '$name'");
            }

            return $handlers[$handler_id]['handlers'][$name];
        }

        if (empty($handlers['handlers'][$name])) {
            throw new InvalidArgumentException("No such handler name '$name'");
        }

        return $handlers['handlers'][$name];
    }

}
