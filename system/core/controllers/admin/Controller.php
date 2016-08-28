<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Container;
use core\classes\Tool;
use core\Controller as BaseController;

/**
 * Contents specific to the backend methods
 */
class Controller extends BaseController
{

    /**
     * Current job
     * @var array
     */
    protected $current_job = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setJobProperties();

        $this->data['admin_menu'] = $this->getAdminMenu();
        $this->data['help_summary'] = $this->getHelpSummary();
    }

    /**
     * Returns a rendered help link depending on the current URL
     * @return string
     */
    public function getHelpSummary()
    {
        $folder = $this->langcode ? $this->langcode : 'en';
        $directory = GC_HELP_DIR . "/$folder";

        $file = Tool::contextFile($directory, 'php', $this->path);

        if (empty($file)) {
            return '';
        }

        $content = $this->render($file['path'], array(), true);
        $parts = $this->explodeText($content);

        if (empty($parts)) {
            return '';
        }

        return $this->render('help/summary', array(
                    'content' => array_map('trim', $parts),
                    'file' => $file));
    }

    /**
     * Returns an array of admin menu items
     * @return array
     */
    protected function getAdminMenuArray()
    {
        $routes = $this->route->getList();

        $array = array();
        foreach ($routes as $path => $route) {

            // Exclude non-admin routes
            if (0 !== strpos($path, 'admin/')) {
                continue;
            }

            // Exclude hidden items
            if (empty($route['menu']['admin'])) {
                continue;
            }

            // Check access
            if (isset($route['access']) && !$this->access($route['access'])) {
                continue;
            }

            $data = array(
                'url' => $this->url($path),
                'depth' => (substr_count($path, '/') - 1),
                'text' => $this->text($route['menu']['admin']),
                    //'weight' => isset($route['weight']) ? $route['weight'] : 0
            );

            $array[$path] = $data;
        }

        //Tool::sortWeight($array);

        ksort($array);
        return $array;
    }

    /**
     * Returns rendered admin menu
     * @return string
     */
    public function getAdminMenu()
    {
        $items = $this->getAdminMenuArray();
        return $this->render('common/menu', array('items' => $items));
    }

    /**
     * Displays nesated admin categories
     */
    public function adminSections()
    {
        $this->redirect('admin'); // TODO: replace with real content
    }

    /**
     * Sets a batch job from the current URL
     * @return null
     */
    protected function setJobProperties()
    {
        $job_id = (string) $this->request->get('job_id');

        if (empty($job_id)) {
            return;
        }

        /* @var $job \core\models\Job */
        $job = Container::instance('core\\models\\Job');

        $this->current_job = $job->get($job_id);

        if (empty($this->current_job['status'])) {
            return;
        }

        $this->setJsSettings('job', $this->current_job, -60);

        $process_job_id = (string) $this->request->get('process_job');

        if ($this->request->isAjax() && $process_job_id === $job_id) {
            $response = $job->process($this->current_job);
            $this->response->json($response);
        }
    }

    /**
     * Returns a rendered job widget
     * @return string
     */
    public function getJob()
    {
        if (empty($this->current_job['status'])) {
            return '';
        }

        if (!empty($this->current_job['widget'])) {
            return $this->render($this->current_job['widget'], array('job' => $this->current_job));
        }

        return $this->render('common/job/widget', array('job' => $this->current_job));
    }

}