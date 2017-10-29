<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to image cache
 */
class Image extends FrontendController
{

    /**
     * A path to the cached image from the current URL
     * @var string
     */
    protected $data_path;

    /**
     * A full server path to the source image file
     * @var string
     */
    protected $data_source_file;

    /**
     * An image style ID from the current URL
     * @var string
     */
    protected $data_imagestyle_id;

    /**
     * A full server path to the cached image
     * @var string
     */
    protected $data_cached_file;

    /**
     * Image style directory
     * @var string
     */
    protected $data_imagestyle_directory;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Outputs processed images
     */
    public function outputCacheImage()
    {
        $this->setUrlPathImage();
        $this->setFileImage();
        $this->setDirectoryImage();

        $this->tryOutputImage();
        $this->checkCacheDirectoryImage();
        $this->applyActionsImage();

        $this->tryOutputImage();
        $this->response->outputError404(false);
    }

    /**
     * Check the image style directory
     */
    protected function checkCacheDirectoryImage()
    {
        if (!file_exists($this->data_imagestyle_directory) && !mkdir($this->data_imagestyle_directory, 0775, true)) {
            $this->response->outputError404(false);
        }
    }

    /**
     * Apply all defined actions to the source image
     */
    protected function applyActionsImage()
    {
        $actions = $this->image->getStyleActions($this->data_imagestyle_id);

        if (empty($actions)) {
            $this->response->outputError404(false);
        } else {
            $this->image->applyActions($actions, $this->data_source_file, $this->data_cached_file);
        }
    }

    /**
     * Try to output existing image
     */
    protected function tryOutputImage()
    {
        if (is_file($this->data_cached_file)) {

            $timestamp = filemtime($this->data_cached_file);
            $expires = (int) $this->config('image_cache_lifetime', 365 * 24 * 60 * 60);

            $this->response->addHeader('Cache-Control', "public, max-age=$expires")
                    ->addHeader('Last-Modified', gmdate('D, d M Y H:i:s T', $timestamp))
                    ->addHeader('Content-Length', filesize($this->data_cached_file))
                    ->addHeader('Content-type', mime_content_type($this->data_cached_file))
                    ->sendHeaders();

            readfile($this->data_cached_file);
            exit;
        }
    }

    /**
     * Parse the current URL path
     */
    protected function setUrlPathImage()
    {
        $path = urldecode(strtok($this->request->urn(), '?'));
        $parts = explode('files/image/cache/', $path);

        if (empty($parts[1])) {
            $this->response->outputError404(false);
        }

        $parts = explode('/', $parts[1]);

        if (empty($parts[1])) {
            $this->response->outputError404(false);
        }

        $this->data_imagestyle_id = array_shift($parts);

        if ($parts[0] == 'image') {
            unset($parts[0]);
        }

        $this->data_path = implode('/', $parts);
    }

    /**
     * Set the current image style directory
     */
    protected function setDirectoryImage()
    {
        $imagestyle_directory = GC_DIR_IMAGE_CACHE . "/$this->data_imagestyle_id";
        $image_directory = pathinfo($this->data_path, PATHINFO_DIRNAME);

        if (!empty($image_directory)) {
            $imagestyle_directory = GC_DIR_IMAGE_CACHE . "/$this->data_imagestyle_id/$image_directory";
        }

        $this->data_imagestyle_directory = $imagestyle_directory;
        $this->data_cached_file = "$imagestyle_directory/" . basename($this->data_path);
    }

    /**
     * Set image file path
     */
    protected function setFileImage()
    {
        $file = gplcart_file_absolute("image/{$this->data_path}");

        if (!is_file($file)) {
            $this->response->outputError404(false);
        }

        $this->data_source_file = $file;
    }

}
