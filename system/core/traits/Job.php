<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods to process batch jobs
 */
trait Job
{

    /**
     * @see \gplcart\core\Controller::isAjax()
     */
    abstract public function isAjax();

    /**
     * @see \gplcart\core\Controller::setJsSettings()
     */
    abstract public function setJsSettings($key, $data, $weight = null);

    /**
     * @see \gplcart\core\Controller::outputJson()
     */
    abstract public function outputJson($data, array $options = array());

    /**
     * @see \gplcart\core\Controller::getQuery()
     */
    abstract public function getQuery($key = null, $default = null, $type = 'string');

    /**
     * @see \gplcart\core\Controller::render()
     */
    abstract public function render($file, $data = array(), $merge = true, $default = '');

    /**
     * Processes the current job
     * @param \gplcart\core\models\Job $job_model
     */
    public function setJob($job_model)
    {
        if ($this->isCanceledJob($job_model)) {
            return null;
        }

        $job_id = $this->getQuery('job_id');

        if (empty($job_id)) {
            return null;
        }

        $job = $job_model->get($job_id);

        if (empty($job['status'])) {
            return null;
        }

        $this->setJsSettings('job', $job);
        if ($this->getQuery('process_job') === $job['id'] && $this->isAjax()) {
            $this->outputJson($job_model->process($job));
        }
    }

    /**
     * Returns the rendered job widget
     * @param \gplcart\core\models\Job $job_model
     * @param null|array $job
     * @return string
     */
    public function getWidgetJob($job_model, $job = null)
    {
        if (!isset($job)) {
            $job = $job_model->get($this->getQuery('job_id', ''));
        }

        $rendered = '';
        if (!empty($job['status'])) {
            $job += array('widget' => 'common/job');
            $rendered = $this->render($job['widget'], array('job' => $job));
        }

        return $rendered;
    }

    /**
     * Whether the current job is canceled
     * @param \gplcart\core\models\Job $job_model
     * @return boolean
     */
    public function isCanceledJob($job_model)
    {
        $cancel_job_id = $this->getQuery('cancel_job');

        if (empty($cancel_job_id)) {
            return false;
        }

        $job_model->delete($cancel_job_id);
        return true;
    }

}
