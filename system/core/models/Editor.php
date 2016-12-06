<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\helpers\File as FileHelper;
use core\models\Backup as BackupModel;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to theme editor
 */
class Editor extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Backup model instance
     * @var \core\models\Backup $backup
     */
    protected $backup;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param BackupModel $backup
     */
    public function __construct(LanguageModel $language, BackupModel $backup)
    {
        parent::__construct();

        $this->backup = $backup;
        $this->language = $language;
    }

    /**
     * Returns an array of editable files
     * @param array $module
     * @return array
     */
    public function getList(array $module)
    {
        $list = array();
        foreach (array('templates', 'css', 'js') as $folder) {
            $pattern = "{$module['directory']}/$folder/*";
            $files = FileHelper::scanRecursive($pattern);
            sort($files);
            $list[$folder] = $files;
        }

        return $list;
    }

    /**
     * Saves an edited file
     * @param array $data
     * @return boolean
     */
    public function save($data)
    {
        $this->hook->fire('save.editor.before', $data);

        if (empty($data)) {
            return false;
        }

        $has_backup = true;
        if (!$this->hasBackup($data['module'])) {
            $has_backup = $this->backup($data);
        }

        if ($has_backup !== true) {
            return false;
        }

        $result = $this->write($data['content'], $data['path']);

        $this->hook->fire('save.editor.after', $data, $result);
        return $result;
    }

    /**
     * Writes a content to a file
     * @param string $content
     * @param string $file
     * @return boolean
     */
    protected function write($content, $file)
    {
        if (!file_exists($file)) {
            return false; // Do not create a new file
        }

        $result = file_put_contents($file, $content);
        return ($result !== false);
    }

    /**
     * Whether a module ID has a backup
     * @param array $module
     * @return boolean
     */
    public function hasBackup(array $module)
    {
        $conditions = array('module_id' => $module['id']);
        $existing = $this->backup->getList($conditions);
        return !empty($existing);
    }

    /**
     * Creates a module backup
     * @param array $data
     * @return boolean|string
     */
    protected function backup(array $data)
    {
        $vars = array('@name' => $data['module']['name'], '@date' => date("D M j G:i:s"));
        $name = $this->language->text('Theme @name. Automatically backed up on @date', $vars);

        $backup = array(
            'name' => $name,
            'module' => $data['module'],
            'user_id' => $data['user_id']
        );

        $result = $this->backup->backup('module', $backup);

        // On success the result must contain a numeric ID of inserted database record
        return (is_numeric($result) && $result > 0);
    }

}