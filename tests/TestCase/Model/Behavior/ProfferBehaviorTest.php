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
 * Recursively remove files and folders
 *
 * @param $dir
 */
	private function __rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir . "/" . $object) == "dir") {
						$this->__rrmdir($dir . "/" . $object);
					} else {
						unlink($dir . "/" . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

/**
 * Clear up any generated images after each test
 *
 * @return void
 */
	public function tearDown() {
		$this->__rrmdir(WWW_ROOT . 'files' . DS . 'proffertest' . DS);
		$this->__rrmdir(TMP . 'Tests' . DS);
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
		$table = $this->getMock('Cake\ORM\Table', ['alias']);
		$table->method('alias')
			->willReturn('ProfferTest');

		$Proffer = new ProfferBehavior($table, $this->__config, ['fanny' => 'flaps']);

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
					],
					'photo_dir' => 'proffer_test'
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
					],
					'photo_dir' => 'proffer_test'
				],
				[
					'filename' => 'image_480x640.jpg',
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
					],
					'photo_dir' => 'proffer_test'
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
		$table = $this->getMock('Cake\ORM\Table', ['alias']);
		$table->method('alias')
			->willReturn('ProfferTest');

		$Proffer = new ProfferTestPathBehavior($table, $this->__config);

		$entity = new Entity($entityData);

		$Proffer->beforeSave($this->getMock('Cake\Event\Event', null, ['beforeSave']), $entity, new ArrayObject());

		$this->assertEquals($expected['filename'], $entity->get('photo'));
		$this->assertEquals($expected['dir'], $entity->get('photo_dir'));

		$testUploadPath = WWW_ROOT . 'files' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' . DS;

		$this->assertFileExists($testUploadPath . $expected['filename']);
		$this->assertFileExists($testUploadPath . 'portrait_' . $expected['filename']);
		$this->assertFileExists($testUploadPath . 'square_' . $expected['filename']);
	}

/**
 * @expectedException Exception
 */
	public function testBeforeSaveWithoutUploadingAFile() {
		$table = $this->getMock('Cake\ORM\Table', ['alias']);
		$table->method('alias')
			->willReturn('ProfferTest');

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
 * @expectedException Exception
 */
	public function testFailedToMoveFile() {
		$table = $this->getMock('Cake\ORM\Table', ['alias']);
		$table->method('alias')
			->willReturn('ProfferTest');

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
		$table = $this->getMock('Cake\ORM\Table', ['alias']);
		$table->method('alias')
			->willReturn('ProfferTest');

		$Proffer = new ProfferBehavior($table, $this->__config);

		$entity = new Entity([
			'photo' => 'image_640x480.jpg',
			'photo_dir' => 'proffer_test'
		]);

		$testUploadPath = WWW_ROOT . 'files' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' . DS;

		if (!file_exists($testUploadPath)) {
			mkdir($testUploadPath, 0777, true);
		}

		copy(Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg', $testUploadPath . 'image_640x480.jpg');
		copy(Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg', $testUploadPath . 'square_image_640x480.jpg');
		copy(Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg', $testUploadPath . 'portrait_image_640x480.jpg');

		$Proffer->afterDelete($this->getMock('Cake\Event\Event', null, ['afterDelete']), $entity, new ArrayObject());

		$this->assertFileNotExists($testUploadPath . 'image_640x480.jpg');
		$this->assertFileNotExists($testUploadPath . 'square_image_640x480.jpg');
		$this->assertFileNotExists($testUploadPath . 'portrait_image_640x480.jpg');
	}
}