<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\system\core\handlers\validator;

use gplcart\tests\phpunit\support\UnitTest;

/**
 * @coversDefaultClass gplcart\core\handlers\validator\FileType
 */
class FileTypeTest extends UnitTest
{

    /**
     * Object class instance
     * @var \gplcart\core\handlers\validator\FileType
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->getInstance('gplcart\\core\\handlers\\validator\\FileType');

        $this->file->setImage();
        $this->file->setCsv();
        $this->file->setJson();
        $this->file->setZip();
    }

    /**
     * @covers gplcart\core\handlers\validator\FileType::image
     */
    public function testImage()
    {
        foreach ($this->file->getCreated('image') as $file) {
            $this->assertTrue($this->object->image($file));
        }
    }

    /**
     * @covers gplcart\core\handlers\validator\FileType::csv
     * @todo   Implement testCsv().
     */
    public function testCsv()
    {
        foreach ($this->file->getCreated('csv') as $file) {
            $this->assertTrue($this->object->csv($file));
        }
    }

    /**
     * @covers gplcart\core\handlers\validator\FileType::json
     */
    public function testJson()
    {
        foreach ($this->file->getCreated('json') as $file) {
            $this->assertTrue($this->object->json($file));
        }
    }

    /**
     * @covers gplcart\core\handlers\validator\FileType::zip
     */
    public function testZip()
    {
        foreach ($this->file->getCreated('zip') as $file) {
            $this->assertTrue($this->object->zip($file));
        }
    }

}
