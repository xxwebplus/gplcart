<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\ImageStyle as ImageStyleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to images
 */
class ImageStyle extends BackendController
{

    /**
     * Image style model class instance
     * @var \gplcart\core\models\ImageStyle $image_style
     */
    protected $image_style;

    /**
     * An array of image style data
     * @var array
     */
    protected $data_imagestyle = array('actions' => array());

    /**
     * @param ImageStyleModel $image_style
     */
    public function __construct(ImageStyleModel $image_style)
    {
        parent::__construct();

        $this->image_style = $image_style;
    }

    /**
     * Displays the image style overview page
     */
    public function listImageStyle()
    {
        $this->clearCacheImageStyle();

        $this->setTitleListImageStyle();
        $this->setBreadcrumbListImageStyle();

        $this->setData('styles', $this->getListImageStyle());
        $this->outputListImageStyle();
    }

    /**
     * Returns an array of image styles
     * @return array
     */
    protected function getListImageStyle()
    {
        return $this->image_style->getList();
    }

    /**
     * Clear cached images
     */
    protected function clearCacheImageStyle()
    {
        $this->controlToken('clear');
        $style_id = $this->getQuery('clear');

        if (!empty($style_id)) {
            if ($this->image_style->clearCache($style_id)) {
                $this->redirect('', $this->text('Cache has been deleted'), 'success');
            }
            $this->redirect('', $this->text('Cache has not been deleted'), 'warning');
        }
    }

    /**
     * Sets titles on the imagestyle overview page
     */
    protected function setTitleListImageStyle()
    {
        $this->setTitle($this->text('Image styles'));
    }

    /**
     * Sets breadcrumbs on the image style overview page
     */
    protected function setBreadcrumbListImageStyle()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the image style page
     */
    protected function outputListImageStyle()
    {
        $this->output('settings/image/list');
    }

    /**
     * Displays the imagestyle edit form
     * @param integer|null $style_id
     */
    public function editImageStyle($style_id = null)
    {
        $this->setImageStyle($style_id);
        $this->setTitleEditImageStyle();
        $this->setBreadcrumbEditImageStyle();

        $this->setData('imagestyle', $this->data_imagestyle);
        $this->setData('actions', $this->getActionsImageStyle());
        $this->setData('can_delete', $this->canDeleteImageStyle());

        $this->submitEditImageStyle();
        $this->setDataEditImageStyle();

        $this->outputEditImageStyle();
    }

    /**
     * Returns an array of image style actions
     * @return array
     */
    protected function getActionsImageStyle()
    {
        return $this->image_style->getActionHandlers();
    }

    /**
     * Whether an image style can be deleted
     * @return bool
     */
    public function canDeleteImageStyle()
    {
        return isset($this->data_imagestyle['imagestyle_id'])//
                && $this->access('image_style_delete')//
                && $this->image_style->canDelete($this->data_imagestyle['imagestyle_id']);
    }

    /**
     * Sets an image style data
     * @param integer $style_id
     */
    protected function setImageStyle($style_id)
    {
        if (is_numeric($style_id)) {
            $this->data_imagestyle = $this->image_style->get($style_id);
            if (empty($this->data_imagestyle)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted data
     */
    protected function submitEditImageStyle()
    {
        if ($this->isPosted('delete')) {
            $this->deleteImageStyle();
        } else if ($this->isPosted('save') && $this->validateEditImageStyle()) {
            if (isset($this->data_imagestyle['imagestyle_id'])) {
                $this->updateImageStyle();
            } else {
                $this->addImageStyle();
            }
        }
    }

    /**
     * Deletes an image style
     */
    protected function deleteImageStyle()
    {
        if ($this->canDeleteImageStyle() && $this->image_style->delete($this->data_imagestyle['imagestyle_id'])) {
            $this->image_style->clearCache($this->data_imagestyle['imagestyle_id']);
            $this->redirect('admin/settings/imagestyle', $this->text('Image style has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Image style has not been deleted'), 'warning');
    }

    /**
     * Validates a submitted image style
     * @return bool
     */
    protected function validateEditImageStyle()
    {
        $this->setSubmitted('imagestyle');
        $this->setSubmittedBool('status');
        $this->setSubmittedArray('actions');
        $this->setSubmitted('update', $this->data_imagestyle);

        $this->validateComponent('image_style');

        return !$this->hasErrors();
    }

    /**
     * Updates an image style
     */
    protected function updateImageStyle()
    {
        $this->controlAccess('image_style_edit');

        if ($this->image_style->update($this->data_imagestyle['imagestyle_id'], $this->getSubmitted())) {
            $this->image_style->clearCache($this->data_imagestyle['imagestyle_id']);
            $this->redirect('admin/settings/imagestyle', $this->text('Image style has been updated'), 'success');
        }

        $this->redirect('', $this->text('Image style has not been updated'), 'warning');
    }

    /**
     * Adds a new image style
     */
    protected function addImageStyle()
    {
        $this->controlAccess('image_style_add');

        if ($this->image_style->add($this->getSubmitted())) {
            $this->redirect('admin/settings/imagestyle', $this->text('Image style has been added'), 'success');
        }

        $this->redirect('', $this->text('Image style has not been added'), 'warning');
    }

    /**
     * Sets template data on the edit image style page
     */
    protected function setDataEditImageStyle()
    {
        $actions = $this->getData('imagestyle.actions');

        if (!$this->isError()) {
            gplcart_array_sort($actions);
        }

        $modified = array();
        foreach ($actions as $action_id => $info) {

            if (is_string($info)) {
                $modified[] = $info;
                continue;
            }

            $action = $action_id;

            if (!empty($info['value'])) {
                $action .= ' ' . implode(',', $info['value']);
            }

            $modified[] = $action;
        }

        $this->setData('imagestyle.actions', implode("\n", $modified));
    }

    /**
     * Sets title on the edit image style page
     */
    protected function setTitleEditImageStyle()
    {
        if (isset($this->data_imagestyle['imagestyle_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_imagestyle['name']));
        } else {
            $title = $this->text('Add image style');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit image style page
     */
    protected function setBreadcrumbEditImageStyle()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/imagestyle'),
            'text' => $this->text('Image styles')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the image style edit page
     */
    protected function outputEditImageStyle()
    {
        $this->output('settings/image/edit');
    }

}
