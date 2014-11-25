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
use Cake\ORM\Table;
use PHPUnit_Framework_TestCase;
use Proffer\Event\ImageTransform;
use Proffer\Model\Behavior\ProfferBehavior;

/**
 * Class ProfferTestBehavior
 * Test stub class to allow overloading of certain methods
 *
 * @package Proffer\Tests\Model\Behavior
 */
class ProfferTestBehavior extends ProfferBehavior {
	public function isUploadedFile($file) {
		return true;
	}

	public function buildPath(Table $table, Entity $entity, $field) {
		return [
			'full' => TMP . 'Tests' . DS . 'proffer_test' . DS . 'image_640x480.jpg',
			'parts' => [
				'root' => TMP,
				'table' => 'Tests',
				'seed' => 'proffer_test',
				'name' => 'image_640x480.jpg'
			]
		];
	}

	public function moveUploadedFile($file, $destination) {
		if (!file_exists(TMP . 'Tests' . DS . 'proffer_test' . DS)) {
			mkdir(TMP . 'Tests' . DS . 'proffer_test' . DS, 0777, true);
		}

		return copy($file, $destination);
	}
}

/**
 * Class ProfferBehaviorTest
 *
 * @package Proffer\Tests\Model\Behavior
 */
class ProfferBehaviorTest extends PHPUnit_Framework_TestCase {

	private $Behavior;

	private $config = [
		'photo' => [
			'dir' => 'photo_dir',
			'thumbnailSizes' => [
				'square' => ['w' => 200, 'h' => 200],
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
		$this->Behavior = new ProfferBehavior($table, $this->config);

		$validator = $this->getMock('Cake\Validation\Validator', null);
		$table->validator('test', $validator);

		if ($allowEmpty) {
			$table->validator()->allowEmpty('photo');
		}

		$entity = new Entity($entityData);

		$this->Behavior->beforeValidate($this->getMock('Cake\Event\Event', null, ['beforeValidate']), $entity, new ArrayObject());
		$result = $entity->toArray();

		$this->assertEquals($expected, $result);
	}

/**
 * @throws BadRequestException
 */
	public function testBeforeSaveWithoutUploadingAFile() {
		$table = $this->getMock('Cake\ORM\Table', null);
		$this->Behavior = new ProfferBehavior($table, $this->config);

		$entity = new Entity([
			'photo' => [
				'name' => '',
				'tmp_name' => '',
				'size' => '',
				'error' => ''
			]
		]);

		$this->Behavior->beforeSave($this->getMock('Cake\Event\Event', null, ['beforeSave']), $entity, new ArrayObject());
	}

/*
 * A bit of a unit and integration test as it will still dispatch the events to the listener
 */
	public function testBeforeSaveWithValidFile() {
		$table = $this->getMock('Cake\ORM\Table', null);
		$this->Behavior = new ProfferTestBehavior($table, $this->config);

		$entity = new Entity([
			'photo' => [
				'name' => 'image_640x480.jpg',
				'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
				'size' => '33000',
				'error' => UPLOAD_ERR_OK
			]
		]);

		$this->Behavior->beforeSave($this->getMock('Cake\Event\Event', null, ['beforeSave']), $entity, new ArrayObject());

		$expectedField = 'image_640x480.jpg';
		$expectedSeed = 'proffer_test';

		$this->assertEquals($expectedField, $entity->get('photo'));
		$this->assertEquals($expectedSeed, $entity->get('photo_dir'));

		$this->assertFileExists(TMP . 'Tests' . DS . 'proffer_test' . DS . 'image_640x480.jpg');
		$this->assertFileExists(TMP . 'Tests' . DS . 'proffer_test' . DS . 'portrait_image_640x480.jpg');
		$this->assertFileExists(TMP . 'Tests' . DS . 'proffer_test' . DS . 'square_image_640x480.jpg');
	}
}