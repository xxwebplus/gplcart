<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\File as FileModel;
use core\models\Field as FieldModel;
use core\models\FieldValue as FieldValueModel;
use core\helpers\Request as RequestHelper;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate field data
 */
class FieldValue extends BaseValidator
{

    /**
     * Path for uploaded field value files that is relative to main file directory
     */
    const UPLOAD_PATH = 'image/upload/field_value';

    /**
     * Request class instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Field value model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Constructor
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     * @param FileModel $file
     * @param RequestHelper $request
     */
    public function __construct(FieldModel $field, FieldValueModel $field_value,
            FileModel $file, RequestHelper $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->field = $field;
        $this->request = $request;
        $this->field_value = $field_value;
    }

    /**
     * Performs full field value data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function fieldValue(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateFieldValue($options);
        $this->validateTitle($options);
        $this->validateWeight($options);
        $this->validateTranslation($options);
        $this->validateFieldFieldValue($options);
        $this->validateColorFieldValue($options);
        $this->validateFileFieldValue($options);

        return $this->getResult();
    }

    /**
     * Validates a field value to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateFieldValue(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->field_value->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Field value'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a field id
     * @param array $options
     * @return boolean|null
     */
    protected function validateFieldFieldValue(array $options)
    {
        $field_id = $this->getSubmitted('field_id', $options);

        if ($this->isUpdating() && !isset($field_id)) {
            return null;
        }

        if (empty($field_id)) {
            $vars = array('@field' => $this->language->text('Field'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('field_id', $error, $options);
            return false;
        }

        if (!is_numeric($field_id)) {
            $vars = array('@field' => $this->language->text('Field'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('field_id', $error, $options);
            return false;
        }

        $field = $this->field->get($field_id);

        if (empty($field['field_id'])) {
            $vars = array('@name' => $this->language->text('Field'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('field_id', $error, $options);
            return false;
        }

        $this->setSubmitted('field', $field);
        return true;
    }

    /**
     * Validates a color code
     * @param array $options
     * @return boolean|null
     */
    protected function validateColorFieldValue(array $options)
    {
        $color = $this->getSubmitted('color', $options);

        if ($this->isUpdating() && !isset($color)) {
            return null;
        }

        $field = $this->getSubmitted('field');

        if (isset($field['widget']) && $field['widget'] != 'color') {
            $this->setSubmitted('color', '', $options);
            return true;
        }

        if (empty($color)) {
            $vars = array('@field' => $this->language->text('Color'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('color', $error, $options);
            return false;
        }

        if (preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $color) !== 1) {
            $vars = array('@field' => $this->language->text('Color'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('color', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates uploaded image
     * @param array $options
     * @return boolean|null
     */
    protected function validateFileFieldValue(array $options)
    {
        // Do not upload if an error has occurred before
        if ($this->isError()) {
            return null;
        }

        $file = $this->request->file('file');
        $path = $this->getSubmitted('path', $options);

        if ($this->isUpdating() && (!isset($path) && empty($file))) {
            return null;
        }

        //Validate an existing file if the path is provided
        if (isset($path)) {
            if (is_readable(GC_FILE_DIR . "/$path")) {
                return true;
            }
            $vars = array('@name' => $this->language->text('File'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('file', $error, $options);
            return false;
        }

        if (empty($file)) {
            return true;
        }

        $result = $this->file->setUploadPath(self::UPLOAD_PATH)
                ->setHandler('image')
                ->upload($file);

        if ($result !== true) {
            $this->setError('file', $result, $options);
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $this->setSubmitted('path', $uploaded);
        return true;
    }

}
