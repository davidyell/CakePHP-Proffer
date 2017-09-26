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
	public function setUp() 
	{
		parent::setUp();
		$View = new View();
		$this->Proffer = new ProfferHelper($View);
	}

	public function testGetUploadUrl()
	{
		$entityData = ['id' => 1, 'photo' => 'test.png', 'photo_dir' => 'testDir'];
		$entity = new Entity($entityData);
		$entity->setSource('table');

		// TODO: Refatorar em pequenos testes
		$uploadUrl = $this->Proffer->getUploadUrl($entity, 'photo');

		$fullBaseUrl = Configure::read('App.fullBaseUrl');

		$this->assertEquals($fullBaseUrl . '/files/table/photo/testDir/test.png', $uploadUrl);

		/* Test params */
		$uploadUrl = $this->Proffer->getUploadUrl($entity, 'photo', [
		    'folder' => 'test_folder',
            'thumb' => 'thumbnail_prefix',
        ]);

		$this->assertEquals($fullBaseUrl . '/test_folder/table/photo/testDir/thumbnail_prefix_test.png', $uploadUrl);

        /* Test fullUrl */
        $uploadUrl = $this->Proffer->getUploadUrl($entity, 'photo', [
            'fullUrl' => false
        ]);

        $this->assertEquals('/files/table/photo/testDir/test.png', $uploadUrl);

        $entity['test_dir'] = 'test';

        $uploadUrl = $this->Proffer->getUploadUrl($entity, 'photo', [
            'dir' => 'test_dir',
        ]);

        $this->assertEquals($fullBaseUrl . '/files/table/photo/test/test.png', $uploadUrl);
	}
}