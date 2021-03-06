<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\Logger;
use gplcart\core\models\Report as ReportModel,
    gplcart\core\models\History as HistoryModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to CRON jobs
 */
class Cron extends FrontendController
{

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * History model instance
     * @var \gplcart\core\models\History $history
     */
    protected $history;

    /**
     * @param Logger $logger
     * @param ReportModel $report
     * @param HistoryModel $history
     */
    public function __construct(Logger $logger, ReportModel $report, HistoryModel $history)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->report = $report;
        $this->history = $history;
    }

    /**
     * Processes CRON requests
     */
    public function executeCron()
    {
        $this->controlAccessExecuteCron();

        register_shutdown_function(array($this, 'shutdownHandlerCron'));
        ini_set('max_execution_time', 0);
        $this->logger->log('cron', 'Cron has started', 'info', true);

        $this->processTasksCron();

        $this->hook->attach('cron', $this);
        $this->config->set('cron_last_run', GC_TIME);
        $this->response->outputHtml($this->text('Cron has started'));
    }

    /**
     * Controls access to execute CRON
     */
    protected function controlAccessExecuteCron()
    {
        if (strcmp($this->getQuery('key', ''), $this->config('cron_key', '')) !== 0) {
            $this->response->outputError403(false);
        }
    }

    /**
     * Processes all defined tasks
     */
    protected function processTasksCron()
    {
        $this->deleteExpiredHistoryCron();
        $this->deleteExpiredFilesCron();
        $this->deleteExpiredLogsCron();
        $this->checkFilesystemCron();
    }

    /**
     * Deletes expired records from history table
     */
    protected function deleteExpiredHistoryCron()
    {
        $this->history->deleteExpired();
    }

    /**
     * Deletes expired log records
     */
    protected function deleteExpiredLogsCron()
    {
        $this->report->deleteExpired();
    }

    /**
     * Deletes old expired files
     */
    protected function deleteExpiredFilesCron()
    {
        foreach (gplcart_file_scan_recursive(GC_DIR_PRIVATE_TEMP) as $file) {
            if (strpos(basename($file), '.') !== 0) { // Ignore hidden files
                gplcart_file_delete_recursive($file);
            }
        }
    }

    /**
     * Checks file system and logs errors
     * @return boolean
     */
    protected function checkFilesystemCron()
    {
        $result = $this->report->checkFilesystem();

        if ($result === true) {
            return true;
        }

        foreach ((array) $result as $message) {
            $this->logger->log('system_status', array('message' => $message), 'warning');
        }

        return false;
    }

    /**
     * Handles PHP shutdown
     */
    public function shutdownHandlerCron()
    {
        $this->config->set('cron_last_run', GC_TIME);
    }

}
