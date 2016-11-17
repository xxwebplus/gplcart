<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\helpers\Tool;

/**
 * Basic CLI controller
 */
class CliController
{

    /**
     * Validator model instance
     * @var \core\models\Validator $validator
     */
    protected $validator;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * CLI router class instance
     * @var \core\CliRoute $route
     */
    protected $route;

    /**
     * An array of the current CLI route data
     * @var array
     */
    protected $current_route = array();

    /**
     * The current command
     * @var string
     */
    protected $command;

    /**
     * An array of mapped data ready for validation
     * @var array
     */
    protected $submitted = array();

    /**
     * An array of messages to output to the user
     * @var array
     */
    protected $messages = array();

    /**
     * An array of errors to output to the user
     * @var array
     */
    protected $errors = array();

    /**
     * Whether in dialog mode
     * @var bool
     */
    protected $dialog = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        /* @var $validator \core\models\Validator */
        $this->validator = Container::instance('core\\models\\Validator');

        /* @var $config \core\Config */
        $this->config = Container::instance('core\\Config');

        /* @var $logger \core\Logger */
        $this->logger = Container::instance('core\\Logger');

        /* @var $route \core\CliRoute */
        $this->route = Container::instance('core\\CliRoute');

        $this->current_route = $this->route->get();
        $this->command = $this->current_route['command'];

        $this->outputHelp();
    }
    
    /**
     * Sets an array of submitted mapped data
     * @param array $map
     * @param array $default
     * @param boolean $filter
     * @return array
     */
    protected function setSubmittedMapped(array $map, $default = array(),
            $filter = true)
    {
        $arguments = $this->getArguments($filter);
        $mapped = $this->mapArguments($arguments, $map);
        $this->submitted = Tool::merge($default, $mapped);
        return $this->submitted;
    }
    
    /**
     * Sets submitted data
     * @param array $data
     */
    protected function setSubmitted(array $data)
    {
        $this->submitted = $data;
    }

    /**
     * Returns a submitted value
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSubmitted($key = null, $default = null)
    {
        if (isset($key)) {
            $result = Tool::getArrayValue($this->submitted, $key);
            return isset($result) ? $result : $default;
        }

        return $this->submitted;
    }

    /**
     * Returns an array of cleaned arguments
     * @param bool $filter
     * @return array
     */
    protected function getArguments($filter = true)
    {
        return Tool::trimArray($this->current_route['arguments'], $filter);
    }

    /**
     * Sets a single message
     * @param string $message
     */
    protected function setMessage($message, $severity = '')
    {
        $this->messages[$message] = array($message, $severity);
    }

    /**
     * Sets php errors recorded by the logger
     */
    protected function setPhpErrors()
    {
        $errors = $this->logger->getErrors();
        foreach ($errors as $severity => $messages) {
            foreach ($messages as $message) {
                $this->setMessage($message, $severity);
            }
        }
    }

    /**
     * Whether a error is set
     * @param null|string $key
     * @return type
     */
    protected function isError($key = null)
    {
        if (isset($key)) {
            return isset($this->errors[$key]);
        }

        return !empty($this->errors);
    }

    /**
     * Returns a colored string
     * @param string $text
     * @param string $sevetity
     * @return string
     */
    protected function getColored($text, $sevetity)
    {
        $default = array(
            'info' => "\e[34m%s\e[0m\n",
            'warning' => "\e[33m%s\e[0m\n",
            'danger' => "\e[31m%s\e[0m\n",
            'success' => "\e[32m%s\e[0m\n",
        );

        $map = $this->config->get('cli_colors', $default);
        return isset($map[$sevetity]) ? sprintf($map[$sevetity], $text) : $text;
    }
    
    /**
     * Displays the progress bar
     * @param type $done
     * @param type $total
     */
    protected function progressBar($done, $total)
    {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }

    /**
     * Sets an error
     * @param string $error
     * @param string $key
     */
    protected function setError($error, $key = null)
    {
        if (isset($key)) {
            $this->errors[$key] = $error;
            return $this->errors;
        }

        $this->errors[] = $error;
        return $this->errors;
    }

    /**
     * Returns a user input
     * @return string
     */
    protected function getInput()
    {
        return fgets(STDIN);
    }

    /**
     * Outputs all defined messages
     * @param bool $exit
     */
    protected function outputMessages($exit = true)
    {
        foreach ($this->messages as $key => $message) {

            if (is_array($message)) {
                list($text, $sevetity) = $message;
                $message = $this->getColored($text, $sevetity);
            }

            fwrite(STDOUT, (string) $message);
            unset($this->messages[$key]);
        }

        if ($exit) {
            exit;
        }
    }

    /**
     * Outputs a error message(s)
     * @param null|string $key
     * @param bool $exit
     * @return null
     */
    protected function outputError($key, $exit = true)
    {
        if (isset($this->errors[$key])) {
            fwrite(STDERR, $this->getColored($this->errors[$key], 'danger'));
            unset($this->errors[$key]);
        }

        if ($exit) {
            exit(1);
        }
    }

    /**
     * Outputs all defined errors
     * @param bool $exit
     */
    protected function outputErrors($exit = true)
    {
        $errors = Tool::flattenArray($this->errors);

        foreach ($errors as $error) {
            fwrite(STDERR, $this->getColored($error, 'danger'));
        }

        $this->errors = array();

        if ($exit) {
            exit(1);
        }
    }

    /**
     * Displays --help message for the curren command
     * @return null
     */
    protected function outputHelp()
    {
        $arguments = $this->getArguments();

        if (empty($arguments['help'])) {
            return null;
        }

        $message = '';
        if (!empty($this->current_route['help']['description'])) {
            $message .= 'Description: ' . $this->current_route['help']['description'] . "\n";
        }

        if (!empty($this->current_route['help']['options'])) {
            $message .= "Options:\n";
            foreach ($this->current_route['help']['options'] as $option => $description) {
                $message .= "    $option - $description\n";
            }
        }

        if (empty($message)) {
            $message = "Sorry. Developers were to lazy to describe this command\n";
        }

        $this->setMessage($message);
        $this->outputMessages(true);
        return null;
    }

    /**
     * Outputs errors and messages to the user
     */
    protected function output()
    {
        $this->setPhpErrors();

        if ($this->isError()) {
            $this->outputErrors();
        }

        $this->outputMessages();
    }

    /**
     * Returns an array of mapped data
     * @param array $arguments
     * @param array $map
     * @return array
     */
    protected function mapArguments(array $arguments, array $map)
    {
        if (empty($map) || empty($arguments)) {
            return array();
        }

        $mapped = array();
        foreach ($arguments as $key => $value) {
            if (isset($map[$key])) {
                Tool::setArrayValue($mapped, $map[$key], $value);
            }
        }

        return $mapped;
    }

    /**
     * Help command callback. Lists all available commands
     */
    public function help()
    {
        $list = $this->route->getList();

        $message = "List of available commands. To see command options use '--help' option:\n";
        foreach ($list as $command => $info) {
            $description = 'No description available';
            if (!empty($info['help']['description'])) {
                $description = $info['help']['description'];
            }

            $message .= "    $command - $description\n";
        }

        $this->setMessage($message);
        $this->output();
    }

    /**
     * Validates a submitted data
     * @param string $handler_id
     * @param array $options
     * @return array
     */
    protected function validate($handler_id, array $options = array())
    {
        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return array();
        }

        $this->errors = (array) $result;
        return $this->errors;
    }

}