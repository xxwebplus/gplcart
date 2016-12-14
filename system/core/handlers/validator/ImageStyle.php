<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Image as ImageModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate image style data
 */
class ImageStyle extends BaseValidator
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     * @param ImageModel $image
     */
    public function __construct(ImageModel $image)
    {
        parent::__construct();

        $this->image = $image;
    }

    /**
     * Performs full image style data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function imageStyle(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateImageStyle($options);
        $this->validateName($options);
        $this->validateStatus($options);
        $this->validateActionsImageStyle($options);

        return $this->getResult();
    }

    /**
     * Validates an image style to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateImageStyle(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $imagestyle = $this->image->getStyle($id);

        if (empty($imagestyle)) {
            $vars = array('@name' => $this->language->text('Image style'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setSubmitted('update', $imagestyle);
        return true;
    }

    /**
     * Validates image actions
     * @param array $options
     * @return boolean|null
     */
    public function validateActionsImageStyle(array $options)
    {
        $actions = $this->getSubmitted('actions', $options);

        if ($this->isUpdating() && !isset($actions)) {
            return null;
        }

        if (empty($actions)) {
            $vars = array('@field' => $this->language->text('Actions'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('actions', $error, $options);
            return false;
        }

        $modified = $errors = array();
        foreach ($actions as $line => $action) {

            $parts = array_map('trim', explode(' ', trim($action)));
            $action_id = array_shift($parts);
            $value = array_filter(explode(',', implode('', $parts)));

            if (!$this->validateActionImageStyle($action_id, $value)) {
                $errors[] = $line + 1;
                continue;
            }

            $modified[$action_id] = array(
                'value' => $value,
                'weight' => $line
            );
        }

        if (!empty($errors)) {
            $vars = array('%num' => implode(',', $errors));
            $error = $this->language->text('Error on lines %num', $vars);
            $this->setError('actions', $error, $options);
        }

        if ($this->isError()) {
            return false;
        }

        $this->setSubmitted('actions', $modified, $options);
        return true;
    }

    /**
     * Calls an appropriate validator method for the given action ID
     * @param string $action_id
     * @param array $value
     * @return boolean
     */
    protected function validateActionImageStyle($action_id, array &$value)
    {
        $type1 = array('flip', 'rotate', 'brightness', 'contrast', 'smooth',
            'fill', 'colorize', 'crop', 'overlay', 'text');

        $type2 = array('fit_to_width', 'fit_to_height', 'pixelate', 'opacity');
        $type3 = array('resize', 'thumbnail', 'best_fit');
        $type4 = array('auto_orient', 'desaturate', 'invert', 'edges', 'emboss',
            'mean_remove', 'blur', 'sketch', 'sepia');

        if (in_array($action_id, $type1)) {
            return $this->{"validateAction{$action_id}ImageStyle"}($value);
        }

        if (in_array($action_id, $type2)) {
            return $this->validateActionOpacityImageStyle($value);
        }

        if (in_array($action_id, $type3)) {
            return $this->validateActionThumbnailImageStyle($value);
        }

        if (in_array($action_id, $type4)) {
            return empty($value);
        }

        return false;
    }

    /**
     * Validates "Flip" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionFlipImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && in_array($value[0], array('x', 'y'), true));
    }

    /**
     * Validates "Rotate" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionRotateImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (0 <= (int) $value[0])//
                && ((int) $value[0] <= 360));
    }

    /**
     * Validates "Brightness" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionBrightnessImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (-255 <= (int) $value[0])//
                && ((int) $value[0] <= 255));
    }

    /**
     * Validates "Contrast" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionContrastImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (-100 <= (int) $value[0])//
                && ((int) $value[0] <= 100));
    }

    /**
     * Validates "Smooth" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionSmoothImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (-10 <= (int) $value[0])//
                && ((int) $value[0] <= 10));
    }

    /**
     * Validates "Fill" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionFillImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0]) === 1);
    }

    /**
     * Validates "Colorize" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionColorizeImageStyle(array $value)
    {
        return ((count($value) == 2)//
                && (preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0]) === 1)//
                && is_numeric($value[1]));
    }

    /**
     * Validates "Crop" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionCropImageStyle(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 4), 'is_numeric')) == 4);
    }

    /**
     * Validates "Overlay" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionOverlayImageStyle(array $value)
    {
        return ((count($value) == 5) && is_numeric($value[2])//
                && is_numeric($value[3]) && is_numeric($value[4]));
    }

    /**
     * Validates "Text" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionTextImageStyle(array $value)
    {
        return ((count($value) == 7)//
                && is_numeric($value[2])//
                && (preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[3]) === 1)//
                && is_numeric($value[5])//
                && is_numeric($value[6]));
    }

    /**
     * Validates "Opacity" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionOpacityImageStyle(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]));
    }

    /**
     * Validates "Thumbnail" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionThumbnailImageStyle(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 2), 'is_numeric')) == 2);
    }

}
