<?php
/**
 *
 * @author Gus Antoniassi (scripts.gus@gmail.com)
 */

namespace Proffer\Tests\View\Helper;

use Proffer\View\Helper\ProfferHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Cake\ORM\Entity;
use Cake\Core\Configure;

class ProfferHelperTest extends TestCase 
{
    private $testEntity;
    private $fullBaseUrl;

	public function setUp() 
	{
		parent::setUp();
		$View = new View();
		$this->Proffer = new ProfferHelper($View);

		$entityData = ['id' => 1, 'photo' => 'test.png', 'photo_dir' => 'testDir'];
		$this->testEntity = new Entity($entityData);
		$this->testEntity->setSource('table');

		$this->fullBaseUrl = Configure::read('App.fullBaseUrl');
	}

	public function testGetUploadUrlDefault()
    {
        $uploadUrl = $this->Proffer->getUploadUrl($this->testEntity, 'photo');
        $this->assertEquals($this->fullBaseUrl . '/files/table/photo/testDir/test.png', $uploadUrl);
    }

    public function testGetUploadUrlConfigFolder()
    {
        $uploadUrl = $this->Proffer->getUploadUrl($this->testEntity, 'photo', [
            'folder' => 'test_folder',
        ]);

        $this->assertEquals($this->fullBaseUrl . '/test_folder/table/photo/testDir/test.png', $uploadUrl);
    }

    public function testGetUploadUrlConfigThumb()
    {
        $uploadUrl = $this->Proffer->getUploadUrl($this->testEntity, 'photo', [
            'thumb' => 'thumbnail_prefix',
        ]);

        $this->assertEquals($this->fullBaseUrl . '/files/table/photo/testDir/thumbnail_prefix_test.png', $uploadUrl);
    }

    public function testGetUploadUrlConfigFullUrl()
    {
        /* Test fullUrl */
        $uploadUrl = $this->Proffer->getUploadUrl($this->testEntity, 'photo', [
            'fullUrl' => false
        ]);

        $this->assertEquals('/files/table/photo/testDir/test.png', $uploadUrl);
    }

    public function testGetUploadUrlConfigDir() {
        $this->testEntity['test_dir'] = 'test';

        $uploadUrl = $this->Proffer->getUploadUrl($this->testEntity, 'photo', [
            'dir' => 'test_dir',
        ]);

        $this->assertEquals($this->fullBaseUrl . '/files/table/photo/test/test.png', $uploadUrl);
	}

	public function testGetUploadLink() {
	    $expectedHtml = '<a href="' . $this->fullBaseUrl . '/files/table/photo/testDir/test.png">Test Title</a>';
	    $uploadLink = $this->Proffer->getUploadLink('Test Title', $this->testEntity, 'photo');

	    $this->assertEquals($expectedHtml, $uploadLink);
    }

    public function testGetUploadImage() {
	    $expectedHtml = '<img src="' . $this->fullBaseUrl . '/files/table/photo/testDir/test.png" alt=""/>';
	    $uploadImage = $this->Proffer->getUploadImage($this->testEntity, 'photo');

	    $this->assertEquals($expectedHtml, $uploadImage);
    }
}