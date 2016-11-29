<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\export;

use core\models\Category as ModelsCategory;
use core\handlers\job\export\Base as BaseHandler;

/**
 * Category export handler
 */
class Category extends BaseHandler
{

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Constructor
     * @param ModelsCategory $category
     */
    public function __construct(ModelsCategory $category)
    {
        parent::__construct();

        $this->category = $category;
    }

    /**
     * Processes one job iteration
     * @param array $job
     */
    public function process(array &$job)
    {
        $this->start($job)->export()->finish();
    }

    /**
     * Exports categories to the CSV file
     */
    protected function export()
    {
        $options = $this->job['data']['options'];
        $options += array('limit' => array($this->offset, $this->limit));

        $this->items = $this->category->getList($options);

        foreach ($this->items as $item) {
            $data = $this->getData($item);
            $this->prepare($data, $item);
            $this->write($data);
        }

        return $this;
    }

    /**
     * Returns a total number of categories to be imported
     * @param array $options
     * @return integer
     */
    public function total(array $options)
    {
        $options['count'] = true;
        return $this->category->getList($options);
    }

    /**
     * Prepares data before exporting
     * @param array $data
     * @param array $item
     */
    protected function prepare(array &$data, array $item)
    {
        $this->attachImages($data, $item);
        $this->prepareImages($data, $item);
    }

    /**
     * Attaches category images
     * @param array $data
     * @param array $item
     */
    protected function attachImages(array &$data, array $item)
    {
        $images = $this->image->getList('category_id', $item['category_id']);
        if (!empty($images)) {
            $data['images'] = $images;
        }
    }

}