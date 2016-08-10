<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;
use core\models\File as ModelsFile;

class Settings extends Controller
{

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param ModelsFile $file
     */
    public function __construct(ModelsFile $file)
    {
        parent::__construct();
        $this->file = $file;
    }

    /**
     * Displays edit settings form
     */
    public function settings()
    {
        $this->controlAccessSuperAdmin();

        $this->setSettings();

        if ($this->request->post('save')) {
            $this->submit();
        }

        $this->prepareSettings();

        $this->setTitleSettings();
        $this->setBreadcrumbSettings();
        $this->outputSettings();
    }

    /**
     * Sets titles on the settings form page
     */
    protected function setTitleSettings()
    {
        $this->setTitle($this->text('Settings'));
    }

    /**
     * Sets breadcrumbs on the settings form page
     */
    protected function setBreadcrumbSettings()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
    }

    /**
     * Renders settings page
     */
    protected function outputSettings()
    {
        $this->output('settings/settings');
    }

    /**
     * Returns an array of settings with their default values
     * @return array
     */
    protected function getDefaultSettings()
    {
        return array(
            'cron_key' => '',
            'error_level' => 2,
            'gapi_email' => '',
            'gapi_certificate' => '',
            'email_method' => 'mail',
            'smtp_auth' => 1,
            'smtp_secure' => 'tls',
            'smtp_host' => array('smtp.gmail.com'),
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_port' => 587
        );
    }

    /**
     * Sets settings values to be send to the template
     */
    protected function setSettings()
    {
        foreach ($this->getDefaultSettings() as $key => $default) {
            $this->data['settings'][$key] = $this->config->get($key, $default);
        }
    }

    /**
     * Prepares settings values before passing them to template
     */
    protected function prepareSettings()
    {
        if (isset($this->data['settings']['smtp_host'])) {
            $this->data['settings']['smtp_host'] = implode("\n", (array) $this->data['settings']['smtp_host']);
        }
    }

    /**
     * Saves settings
     */
    protected function submit()
    {
        $this->submitted = $this->request->post('settings');
        $this->validate();
        $errors = $this->getErrors();

        if (!empty($errors)) {
            $this->data['settings'] = $this->submitted;
            return;
        }

        foreach ($this->submitted as $key => $value) {
            $this->config->set($key, $value);
        }

        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Validates settings
     */
    protected function validate()
    {
        $this->validateCron();
        $this->validateGapi();
        $this->validateSmtp();
    }

    /**
     * Validates / prepares submitted SMTP settings
     * @return boolean
     */
    protected function validateSmtp()
    {
        $this->submitted['smtp_host'] = Tool::stringToArray($this->submitted['smtp_host']);
        return true;
    }

    /**
     * Validates / prepares submitted GAPI settings
     * @return boolean
     */
    protected function validateGapi()
    {
        if (isset($this->submitted['delete_gapi_certificate'])) {
            unlink(GC_FILE_DIR . '/' . $this->config->get('gapi_certificate'));
            $this->config->reset('gapi_certificate');
            unset($this->submitted['delete_gapi_certificate']);
        }

        if (!empty($this->submitted['gapi_email']) && !filter_var($this->submitted['gapi_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['gapi_email'] = $this->text('Invalid E-mail');
            return false;
        }

        $file = $this->request->file('gapi_certificate');

        if (empty($file)) {
            return empty($this->errors);
        }

        $this->file->setHandler('p12');

        if ($this->file->upload($file) === true) {
            $destination = $this->file->getUploadedFile();
            $this->submitted['gapi_certificate'] = $this->file->path($destination);
        } else {
            $this->errors['gapi_certificate'] = $this->text('Unable to upload the file');
        }

        return empty($this->errors);
    }

    /**
     * Validates/prepares submitted cron settings
     * @return boolean
     */
    protected function validateCron()
    {
        if (empty($this->submitted['cron_key'])) {
            $this->submitted['cron_key'] = Tool::randomString();
        }

        return true;
    }

}
