<?php
/**
 * Created by PhpStorm.
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Tests\Model\Behavior;

use ArrayObject;
use Cake\Core\Plugin;
use Cake\ORM\Entity;
use PHPUnit_Framework_TestCase;
use Proffer\Event\ImageTransform;
use Proffer\Model\Behavior\ProfferBehavior;
use Proffer\Tests\Stubs\ProfferTestMoveBehavior;
use Proffer\Tests\Stubs\ProfferTestPathBehavior;

/**
 * Class ProfferBehaviorTest
 *
 * @package Proffer\Tests\Model\Behavior
 */
class ProfferBehaviorTest extends PHPUnit_Framework_TestCase {

	private $__config = [
		'photo' => [
			'dir' => 'photo_dir',
			'thumbnailSizes' => [
				'square' => ['w' => 200, 'h' => 200, 'crop' => true],
				'portrait' => ['w' => 100, 'h' => 300],
			]
		]
	];

	public function setUp() {
	}

/**
 * Clear up any generated images after each test
 *
 * @return void
 */
	public function tearDown() {
		$files = glob(TMP . 'Tests' . DS . 'proffer_test' . DS . '*');
		if (!empty($files)) {
			foreach ($files as $file) {
				unlink($file);
			}
		}

		// Sigh, thanks OS X
		if (file_exists(TMP . 'Tests' . DS . 'proffer_test' . DS . '.DS_Store')) {
			unlink(TMP . 'Tests' . DS . 'proffer_test' . DS . '.DS_Store');
		}

		if (file_exists(TMP . 'Tests' . DS . 'proffer_test' . DS)) {
			rmdir(TMP . 'Tests' . DS . 'proffer_test');
		}
		if (file_exists(TMP . 'Tests')) {
			rmdir(TMP . 'Tests');
		}
	}

/**
 * Data provider method for testing validation
 *
 * @return array
 */
	public function beforeValidateProvider() {
		return [
			[
				['photo' => ['error' => UPLOAD_ERR_NO_FILE]],
				true,
				[]
			],
			[
				['photo' => ['error' => UPLOAD_ERR_NO_FILE]],
				false,
				['photo' => ['error' => UPLOAD_ERR_NO_FILE]]
			],
			[
				['photo' => ['error' => UPLOAD_ERR_OK]],
				true,
				['photo' => ['error' => UPLOAD_ERR_OK]]
			],
			[
				['photo' => ['error' => UPLOAD_ERR_OK]],
				false,
				['photo' => ['error' => UPLOAD_ERR_OK]]
			],
		];
	}

/**
 * @dataProvider beforeValidateProvider
 */
	public function testBeforeValidate($entityData, $allowEmpty, $expected) {
		$table = $this->getMock('Cake\ORM\Table', null);
		$Proffer = new ProfferBehavior($table, $this->__config);

		$validator = $this->getMock('Cake\Validation\Validator', null);
		$table->validator('test', $validator);

		if ($allowEmpty) {
			$table->validator()->allowEmpty('photo');
		}

		$entity = new Entity($entityData);

		$Proffer->beforeValidate($this->getMock('Cake\Event\Event', null, ['beforeValidate']), $entity, new ArrayObject());
		$result = $entity->toArray();

		$this->assertEquals($expected, $result);
	}

/**
 * Data provider method for testing valid file uploads
 *
 * @return array
 */
	public function validFileProvider() {
		return [
			[
				[
					'photo' => [
						'name' => 'image_640x480.jpg',
						'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
						'size' => 33000,
						'error' => UPLOAD_ERR_OK
					]
				],
				[
					'filename' => 'image_640x480.jpg',
					'dir' => 'proffer_test'
				]
			],
			[
				[
					'photo' => [
						'name' => 'image_480x640.jpg',
						'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_480x640.jpg',
						'size' => 45704,
						'error' => UPLOAD_ERR_OK
					]
				],
				[
					'filename' => 'image_480x640.jpg',
					'dir' => 'proffer_test'
				]
			],
		];
	}

/**
 * A bit of a unit and integration test as it will still dispatch the events to the listener
 *
 * @dataProvider validFileProvider
 */
	public function testBeforeSaveWithValidFile(array $entityData, array $expected) {
		$table = $this->getMock('Cake\ORM\Table', null);
		$Proffer = new ProfferTestPathBehavior($table, $this->__config);

		$entity = new Entity($entityData);

		$Proffer->beforeSave($this->getMock('Cake\Event\Event', null, ['beforeSave']), $entity, new ArrayObject());

		$this->assertEquals($expected['filename'], $entity->get('photo'));
		$this->assertEquals($expected['dir'], $entity->get('photo_dir'));

		$this->assertFileExists(TMP . 'Tests' . DS . 'proffer_test' . DS . $expected['filename']);
		$this->assertFileExists(TMP . 'Tests' . DS . 'proffer_test' . DS . 'portrait_' . $expected['filename']);
		$this->assertFileExists(TMP . 'Tests' . DS . 'proffer_test' . DS . 'square_' . $expected['filename']);
	}

/**
 * @expectedException Exception
 */
	public function testBeforeSaveWithoutUploadingAFile() {
		$table = $this->getMock('Cake\ORM\Table', null);
		$Proffer = new ProfferBehavior($table, $this->__config);

		$entity = new Entity([
			'photo' => [
				'name' => '',
				'tmp_name' => '',
				'size' => '',
				'error' => UPLOAD_ERR_OK
			]
		]);

		$Proffer->beforeSave($this->getMock('Cake\Event\Event', null, ['beforeSave']), $entity, new ArrayObject());
	}

/**
 *
 */
	public function testBuildPath() {
		$table = $this->getMock('Cake\ORM\Table', ['alias']);
		$table->method('alias')->willReturn('Examples');

		$Proffer = new ProfferBehavior($table, $this->__config);

		$entity = new Entity([
			'photo' => 'image_640x480.jpg',
			'photo_dir' => 'seed_value'
		]);

		$result = $Proffer->getPath($table, $entity, 'photo', 'image_640x480.jpg');
		$expected = [
			'full' => WWW_ROOT . 'files/examples/seed_value/image_640x480.jpg',
			'parts' => [
				'root' => WWW_ROOT . 'files',
				'table' => 'examples',
				'seed' => 'seed_value',
				'name' => 'image_640x480.jpg'
			]
		];

		$this->assertEquals($expected, $result);
	}

/**
 * @expectedException Exception
 */
	public function testFailedToMoveFile() {
		$table = $this->getMock('Cake\ORM\Table', null);
		$Proffer = new ProfferTestMoveBehavior($table, $this->__config);

		$entity = new Entity([
			'photo' => [
				'name' => 'image_640x480.jpg',
				'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
				'size' => 33000,
				'error' => UPLOAD_ERR_OK
			]
		]);

		$Proffer->beforeSave($this->getMock('Cake\Event\Event', null, ['beforeSave']), $entity, new ArrayObject());
	}

	public function testAfterDelete() {
		$this->markTestIncomplete('This test has not been completed yet.');

		$table = $this->getMock('Cake\ORM\Table', null);
		$Proffer = new ProfferBehavior($table, $this->__config);

		$entity = new Entity();

		$Proffer->afterDelete($this->getMock('Cake\Event\Event', null, ['afterDelete']), $entity, new ArrayObject());
	}
}