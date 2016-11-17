<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\helpers\Response;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to image cache
 */
class Image extends FrontendController
{

    /**
     * Response class instance
     * @var \core\helpers\Response $response
     */
    protected $response;

    /**
     * Constructor
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        parent::__construct();

        $this->response = $response;
    }

    /**
     * Outputs processed images
     */
    public function cache()
    {
        $path = urldecode(strtok($this->request->urn(), '?'));
        $parts = explode('files/image/cache/', $path);

        if (empty($parts[1])) {
            $this->response->error404(false);
        }

        $parts = explode('/', $parts[1]);

        if (empty($parts[1])) {
            $this->response->error404(false);
        }

        $imagestyle_id = array_shift($parts);

        if ($parts[0] == 'image') {
            unset($parts[0]);
        }

        $image = implode('/', $parts);

        $server_file = GC_FILE_DIR . "/image/$image";

        if (!file_exists($server_file)) {
            $this->response->error404(false);
        }

        $preset_directory = GC_IMAGE_CACHE_DIR . "/$imagestyle_id";

        $image_directory = pathinfo($image, PATHINFO_DIRNAME);

        if (!empty($image_directory)) {
            $preset_directory = GC_IMAGE_CACHE_DIR . "/$imagestyle_id/$image_directory";
        }

        $cached_image = $preset_directory . '/' . basename($image);

        if (file_exists($cached_image)) {
            $this->response->file($cached_image, array('headers' => $this->headers($cached_image)));
        }

        if (!file_exists($preset_directory) && !mkdir($preset_directory, 0755, true)) {
            $this->response->error404(false);
        }

        $actions = $this->image->getStyleActions($imagestyle_id, true);

        if (empty($actions)) {
            $this->response->error404(false);
        }

        $actions['save'] = array('value' => array($cached_image));
        $this->image->modify($server_file, $actions);
        $this->response->file($cached_image, array('headers' => $this->headers($cached_image)));
    }

    /**
     * Returns cache headers
     * @param string $file
     * @return array
     */
    protected function headers($file)
    {
        $timestamp = filemtime($file);
        $expires = (int) $this->config('image_cache_lifetime', 31536000); // 1 year

        $headers[] = array('Last-Modified', gmdate('D, d M Y H:i:s T', $timestamp));
        $headers[] = array('Cache-Control', "public, max-age=$expires");

        return $headers;
    }

}
