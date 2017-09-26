<?php
/**
 *
 * @author Gus Antoniassi (scripts.gus@gmail.com)
 */

namespace Proffer\Tests\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Http\ServerRequest;
use Cake\Network\Session;
use Cake\TestSuite\TestCase;
use Cake\Controller\Controller;
use Cake\ORM\Entity;
use Cake\Core\Configure;
use Proffer\Controller\Component\ProfferComponent;

class ProfferHelperTest extends TestCase
{
    private $testEntity;
    private $fullBaseUrl;

    public function setUp()
    {
        parent::setUp();
        $Controller = new Controller(new ServerRequest(['session' => new Session()]));
        $ComponentRegistry = new ComponentRegistry($Controller);
        $this->Proffer = new ProfferComponent($ComponentRegistry);

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
}