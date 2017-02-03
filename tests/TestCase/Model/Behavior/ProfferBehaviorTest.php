<?php
/**
 * Created by PhpStorm.
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Tests\Model\Behavior;

use ArrayObject;
use Cake\Core\Plugin;
use Cake\Database\Schema\Table as TableSchema;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use PHPUnit_Framework_TestCase;
use Proffer\Lib\ProfferPath;
use Proffer\Model\Behavior\ProfferBehavior;
use Proffer\Tests\Stubs\TestPath;

/**
 * Class ProfferBehaviorTest
 *
 * @package Proffer\Tests\Model\Behavior
 */
class ProfferBehaviorTest extends PHPUnit_Framework_TestCase
{

    private $config = [
        'photo' => [
            'dir' => 'photo_dir',
            'thumbnailSizes' => [
                'square' => ['w' => 200, 'h' => 200, 'crop' => true],
                'portrait' => ['w' => 100, 'h' => 300],
                'large' => ['w' => 1200, 'h' => 900, 'orientate' => true],
            ]
        ]
    ];

    /**
     * Adjust the default root so that it doesn't overwrite and user files
     */
    public function setUp()
    {
        $this->config['photo']['root'] = TMP . 'ProfferTests' . DS;
    }

    /**
     * Recursively remove files and folders
     *
     * @param $dir
     */
    protected function _rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->_rrmdir($dir . "/" . $object);
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
    public function tearDown()
    {
        $this->_rrmdir(TMP . 'ProfferTests' . DS);
    }

    /**
     * Generate a mock of the ProfferPath class with various set returns to ensure that the path is always consistent
     *
     * @param Table $table Instance of the table
     * @param Entity $entity Instance of the entity
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getProfferPathMock(Table $table, Entity $entity)
    {
        $path = $this->getMockBuilder(ProfferPath::class)
            ->setConstructorArgs([$table, $entity, 'photo', $this->config['photo']])
            ->setMethods(['fullPath', 'getFolder'])
            ->getMock();

        $path->expects($this->any())
            ->method('fullPath')
            ->with($this->logicalOr(
                $this->equalTo(null),
                $this->equalTo('square'),
                $this->equalTo('portrait'),
                $this->equalTo('large')
            ))
            ->will($this->returnCallback(
                function ($param) use ($table, $entity) {
                    $filename = '';
                    if ($param !== null) {
                        $filename = $param . '_';
                    }

                    $entityFieldData = $entity->get('photo');

                    if (is_array($entityFieldData)) {
                        $filename .= $entityFieldData['name'];
                    } else {
                        $filename .= $entityFieldData;
                    }

                    return TMP . 'ProfferTests' . DS . $table->alias() .
                        DS . 'photo' . DS . 'proffer_test' . DS . $filename;
                }
            ));

        $path->expects($this->any())
            ->method('getFolder')
            ->willReturn(TMP . 'ProfferTests' . DS . $table->alias() . DS . 'photo' . DS . 'proffer_test' . DS);

        return $path;
    }

    /**
     * Data provider method for testing validation
     *
     * @return array
     */
    public function beforeMarshalProvider()
    {
        return [
            [
                ['photo' => ['error' => UPLOAD_ERR_NO_FILE]],
                true,
                ['photo' => ['error' => UPLOAD_ERR_NO_FILE]]
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
     * @dataProvider beforeMarshalProvider
     *
     * @param array $data
     * @param bool $allowEmpty
     * @param array $expected
     */
    public function testBeforeMarshal(array $data, $allowEmpty, array $expected)
    {
        $schema = $this->createMock(TableSchema::class, null, ['examples']);
        $table = $this->createMock(Table::class, ['alias'], [['schema' => $schema]]);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);

        $proffer = new ProfferBehavior($table, $this->config);

        $validator = $this->createMock(Validator::class);
        $table->validator('test', $validator);

        $table->method('validator')
            ->willReturn($validator);

        if ($allowEmpty) {
            $table->validator()->allowEmpty('photo');
        }

        $arrayObject = new ArrayObject($data);

        $proffer->beforeMarshal(
            $this->createMock(Event::class),
            $arrayObject,
            new ArrayObject()
        );
        $result = $arrayObject;

        $this->assertEquals(new ArrayObject($expected), $result);
    }

    /**
     * Data provider method for testing valid file uploads
     *
     * @return array
     */
    public function validFileProvider()
    {
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
     *
     * @param array $entityData
     * @param array $expected
     */
    public function testBeforeSaveWithValidFile(array $entityData, array $expected)
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $entity = new Entity($entityData);
        $path = $this->_getProfferPathMock($table, $entity, 'photo');

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $this->config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->willReturnCallback(function ($source, $destination) {
                if (!file_exists(pathinfo($destination, PATHINFO_DIRNAME))) {
                    mkdir(pathinfo($destination, PATHINFO_DIRNAME), 0777, true);
                }

                return copy($source, $destination);
            });

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );

        $this->assertEquals($expected['filename'], $entity->get('photo'));
        $this->assertEquals($expected['dir'], $entity->get('photo_dir'));

        $testUploadPath = $path->getFolder();

        $this->assertFileExists($testUploadPath . $expected['filename']);
        $this->assertFileExists($testUploadPath . 'portrait_' . $expected['filename']);
        $this->assertFileExists($testUploadPath . 'square_' . $expected['filename']);

        $portraitSizes = getimagesize($testUploadPath . 'portrait_' . $expected['filename']);
        $this->assertEquals(100, $portraitSizes[0]);

        $squareSizes = getimagesize($testUploadPath . 'square_' . $expected['filename']);
        $this->assertEquals(200, $squareSizes[0]);
        $this->assertEquals(200, $squareSizes[1]);
    }

    /**
     * @expectedException \Proffer\Exception\CannotUploadFileException
     */
    public function testBeforeSaveWithoutUploadingAFile()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $path = $this->_getProfferPathMock(
            $table,
            new Entity(['photo' => 'image_640x480.jpg', 'photo_dir' => 'proffer_test']),
            'photo'
        );

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $this->config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->willReturn(false);

        $entity = new Entity([
            'photo' => [
                'name' => '',
                'tmp_name' => '',
                'size' => '',
                'error' => UPLOAD_ERR_OK
            ]
        ]);

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );
    }

    /**
     * @expectedException \Proffer\Exception\CannotUploadFileException
     */
    public function testFailedToMoveFile()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $this->config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->willReturn(false);

        $entity = new Entity([
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ]
        ]);

        $path = $this->_getProfferPathMock($table, $entity, 'photo');

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );
    }

    /**
     * Test afterDelete
     */
    public function testAfterDelete()
    {
        $schema = $this->createMock(TableSchema::class);
        $eventManager = $this->createMock(EventManager::class);
        $table = $this->createMock(Table::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $proffer = new ProfferBehavior($table, $this->config);

        $entity = new Entity([
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ]);

        $path = $this->_getProfferPathMock($table, $entity, 'photo');
        $testUploadPath = $path->getFolder();

        if (!file_exists($testUploadPath)) {
            mkdir($testUploadPath, 0777, true);
        }

        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
            $testUploadPath . 'image_640x480.jpg'
        );
        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
            $testUploadPath . 'square_image_640x480.jpg'
        );
        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
            $testUploadPath . 'portrait_image_640x480.jpg'
        );

        $event = new Event('Proffer.beforeDeleteFolder', $entity, ['path' => $path]);
        $eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with($this->equalTo($event));

        $proffer->afterDelete(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );

        $this->assertFileNotExists($testUploadPath . 'image_640x480.jpg');
        $this->assertFileNotExists($testUploadPath . 'square_image_640x480.jpg');
        $this->assertFileNotExists($testUploadPath . 'portrait_image_640x480.jpg');
    }

    public function testAfterDeleteWithMissingFiles()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $proffer = new ProfferBehavior($table, $this->config);

        $entity = new Entity([
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ]);

        $path = $this->_getProfferPathMock($table, $entity, 'photo');
        $testUploadPath = $path->getFolder();

        if (!file_exists($testUploadPath)) {
            mkdir($testUploadPath, 0777, true);
        }

        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
            $testUploadPath . 'image_640x480.jpg'
        );

        $proffer->afterDelete(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );

        $this->assertFileNotExists($testUploadPath . 'image_640x480.jpg');
        $this->assertFileNotExists($testUploadPath . 'square_image_640x480.jpg');
        $this->assertFileNotExists($testUploadPath . 'portrait_image_640x480.jpg');
    }

    public function testEventsForBeforeSave()
    {
        $entityData = [
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ],
            'photo_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);

        $eventManager = $this->createMock(EventManager::class);

        $schema = $this->createMock(TableSchema::class);
        $table = $this->getMockBuilder(Table::class)
            ->setConstructorArgs([['eventManager' => $eventManager, 'schema' => $schema]])
            ->setMethods(['alias'])
            ->getMock();

        $table->method('alias')
            ->willReturn('ProfferTest');

        $path = $this->_getProfferPathMock($table, $entity, 'photo');

        $eventAfterPath = new Event('Proffer.afterPath', $entity, ['path' => $path]);

        $eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with($this->equalTo($eventAfterPath));

        $images = [
            $path->getFolder() . 'image_640x480.jpg',
            $path->getFolder() . 'square_image_640x480.jpg',
            $path->getFolder() . 'portrait_image_640x480.jpg',
            $path->getFolder() . 'large_image_640x480.jpg',
        ];
        $eventAfterCreateImage = new Event('Proffer.afterCreateImage', $entity, ['path' => $path, 'images' => $images]);

        $eventManager->expects($this->at(1))
            ->method('dispatch')
            ->with($this->equalTo($eventAfterCreateImage));

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $this->config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->will($this->returnCallback(
                function ($param) use ($entity, $path) {
                    return copy($entity->get('photo')['tmp_name'], $path->fullPath());
                }
            ));

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );
    }

    public function testThumbsNotCreatedWhenNoSizes()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $config = $this->config;
        unset($config['photo']['thumbnailSizes']);

        $entityData = [
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ],
            'photo_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);
        $path = $this->_getProfferPathMock($table, $entity, 'photo');

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->willReturnCallback(function ($source, $destination) {
                if (!file_exists(pathinfo($destination, PATHINFO_DIRNAME))) {
                    mkdir(pathinfo($destination, PATHINFO_DIRNAME), 0777, true);
                }

                return copy($source, $destination);
            });

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );

        $this->assertEquals('image_640x480.jpg', $entity->get('photo'));
        $this->assertEquals('proffer_test', $entity->get('photo_dir'));

        $testUploadPath = $path->getFolder();

        $this->assertFileExists($testUploadPath . 'image_640x480.jpg');
        $this->assertFileNotExists($testUploadPath . 'portrait_image_640x480.jpg');
        $this->assertFileNotExists($testUploadPath . 'square_image_640x480.jpg');
    }

    public function providerPathEvents()
    {
        return [
            [
                [
                    'table' => 'proffer_path_event_test',
                    'seed' => 'proffer_event_test',
                    'filename' => 'event_image_640x480.jpg'
                ],
                TMP . 'ProfferTests' . DS . 'proffer_path_event_test' . DS . 'photo' . DS . 'proffer_event_test' .
                DS . 'event_image_640x480.jpg'
            ],
            [
                [
                    'table' => null,
                    'seed' => 'proffer_event_test',
                    'filename' => 'event_image_640x480.jpg'
                ],
                TMP . 'ProfferTests' . DS . 'photo' . DS . 'proffer_event_test' . DS . 'event_image_640x480.jpg'
            ],
            [
                [
                    'table' => '',
                    'seed' => 'proffer_event_test',
                    'filename' => 'event_image_640x480.jpg'
                ],
                TMP . 'ProfferTests' . DS . 'photo' . DS . 'proffer_event_test' . DS . 'event_image_640x480.jpg'
            ],
            [
                [
                    'table' => '',
                    'seed' => '',
                    'filename' => 'event_image_640x480.jpg'
                ],
                TMP . 'ProfferTests' . DS . 'photo' . DS . 'event_image_640x480.jpg'
            ],
            [
                [
                    'table' => 'proffer_path_event_test',
                    'seed' => '',
                    'filename' => 'event_image_640x480.jpg'
                ],
                TMP . 'ProfferTests' . DS . 'proffer_path_event_test' . DS . 'photo' . DS . 'event_image_640x480.jpg'
            ],
        ];
    }

    /**
     * @param array $pathData An array of data to pass into the path customisation
     * @param string $expected
     *
     * @dataProvider providerPathEvents
     */
    public function testChangingThePathUsingEvents(array $pathData, $expected)
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = new EventManager();
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $listener = $this->getMockBuilder(EventListenerInterface::class)
            ->setMethods(['implementedEvents', 'filename'])
            ->getMock();

        $listener->expects($this->once())
            ->method('implementedEvents')
            ->willReturn(['Proffer.afterPath' => 'filename']);

        $listener->expects($this->once())
            ->method('filename')
            ->willReturnCallback(function ($event, $path) use ($pathData) {
                $path->setTable($pathData['table']);
                $path->setSeed($pathData['seed']);
                $path->setFilename($pathData['filename']);

                $event->subject()['photo']['name'] = $pathData['filename'];

                return $path;
            });

        $table->eventManager()->on($listener);

        $entityData = [
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ],
            'photo_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);

        $this->config['photo']['root'] = TMP . 'ProfferTests';
        $path = new ProfferPath($table, $entity, 'photo', $this->config['photo']);

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $this->config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->willReturnCallback(function ($source, $destination) {
                if (!file_exists(pathinfo($destination, PATHINFO_DIRNAME))) {
                    mkdir(pathinfo($destination, PATHINFO_DIRNAME), 0777, true);
                }

                return copy($source, $destination);
            });

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );

        $this->assertEquals($pathData['filename'], $entity->get('photo'));
        $this->assertEquals($pathData['seed'], $entity->get('photo_dir'));

        $this->assertFileExists($path->fullPath());
        $this->assertEquals($expected, $path->fullPath());
    }

    public function testDeletingARecordWithNoThumbnailConfig()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);

        $config = $this->config;
        unset($config['photo']['thumbnailSizes']);

        $entityData = [
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);
        $path = $this->_getProfferPathMock($table, $entity, 'photo');

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $config])
            ->setMethods(['afterDelete'])
            ->getMock();

        $proffer->expects($this->once())
            ->method('afterDelete');

        $proffer->afterDelete(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject(),
            $path
        );
    }

    public function testReplacingComponents()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $config = [
            'photo' => [
                'dir' => 'photo_dir',
                'thumbnailSizes' => [
                    'square' => ['w' => 200, 'h' => 200, 'crop' => true],
                    'portrait' => ['w' => 100, 'h' => 300],
                ],
                'pathClass' => '\Proffer\Tests\Stubs\TestPath',
                'transformClass' => '\Proffer\Tests\Stubs\TestTransform'
            ]
        ];

        $entityData = [
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ],
            'photo_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $path = new TestPath($table, $entity, 'photo', $config['photo']);

        $proffer->expects($this->once())
            ->method('moveUploadedFile')
            ->willReturnCallback(function ($source, $destination) {
                if (!file_exists(pathinfo($destination, PATHINFO_DIRNAME))) {
                    mkdir(pathinfo($destination, PATHINFO_DIRNAME), 0777, true);
                }

                return copy($source, $destination);
            });

        $proffer->beforeSave(
            $this->createMock('Cake\Event\Event', null, ['beforeSave']),
            $entity,
            new ArrayObject()
        );

        $this->assertEquals('image_640x480.jpg', $entity->get('photo'));
        $this->assertEquals('proffer_test', $entity->get('photo_dir'));

        $testUploadPath = $path->getFolder();

        $this->assertFileExists($testUploadPath . 'image_640x480.jpg');
        $this->assertFileExists($testUploadPath . 'portrait_' . 'image_640x480.jpg');
        $this->assertFileExists($testUploadPath . 'square_' . 'image_640x480.jpg');

        $portraitSizes = getimagesize($testUploadPath . 'portrait_' . 'image_640x480.jpg');
        $this->assertEquals(100, $portraitSizes[0]);

        $squareSizes = getimagesize($testUploadPath . 'square_' . 'image_640x480.jpg');
        $this->assertEquals(200, $squareSizes[0]);
        $this->assertEquals(200, $squareSizes[1]);
    }

    /**
     * @expectedException \Proffer\Exception\InvalidClassException
     */
    public function testReplacingComponentsWithNoInterface()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $config = [
            'photo' => [
                'dir' => 'photo_dir',
                'thumbnailSizes' => [
                    'square' => ['w' => 200, 'h' => 200, 'crop' => true],
                    'portrait' => ['w' => 100, 'h' => 300],
                ],
                'pathClass' => \Proffer\Tests\Stubs\BadPath::class,
            ]
        ];

        $entityData = [
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ],
            'photo_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $path = new TestPath($table, $entity, 'photo', $config['photo']);

        $proffer->expects($this->never())
            ->method('moveUploadedFile');

        $proffer->beforeSave(
            $this->createMock('Cake\Event\Event', null, ['beforeSave']),
            $entity,
            new ArrayObject()
        );

        $this->assertEquals('image_640x480.jpg', $entity->get('photo'));
        $this->assertEquals('proffer_test', $entity->get('photo_dir'));

        $testUploadPath = $path->getFolder();

        $this->assertFileExists($testUploadPath . 'image_640x480.jpg');
        $this->assertFileExists($testUploadPath . 'portrait_' . 'image_640x480.jpg');
        $this->assertFileExists($testUploadPath . 'square_' . 'image_640x480.jpg');

        $portraitSizes = getimagesize($testUploadPath . 'portrait_' . 'image_640x480.jpg');
        $this->assertEquals(100, $portraitSizes[0]);

        $squareSizes = getimagesize($testUploadPath . 'square_' . 'image_640x480.jpg');
        $this->assertEquals(200, $squareSizes[0]);
        $this->assertEquals(200, $squareSizes[1]);
    }

    public function testMultipleFieldUpload()
    {
        $schema = $this->createMock(TableSchema::class);
        $table = $this->createMock(Table::class);
        $eventManager = $this->createMock(EventManager::class);
        $table->method('alias')
            ->willReturn('ProfferTest');
        $table->method('schema')
            ->willReturn($schema);
        $table->method('eventManager')
            ->willReturn($eventManager);

        $entityData = [
            'photo' => [
                'name' => 'image_640x480.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                'size' => 33000,
                'error' => UPLOAD_ERR_OK
            ],
            'photo_dir' => 'proffer_test',
            'avatar' => [
                'name' => 'image_480x640.jpg',
                'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_480x640.jpg',
                'size' => 45704,
                'error' => UPLOAD_ERR_OK
            ],
            'avatar_dir' => 'proffer_test'
        ];
        $entity = new Entity($entityData);

        $config = [
            'photo' => [
                'dir' => 'photo_dir',
                'thumbnailSizes' => [
                    'square' => ['w' => 200, 'h' => 200, 'crop' => true],
                ],
                'pathClass' => '\Proffer\Tests\Stubs\TestPath'
            ],
            'avatar' => [
                'dir' => 'avatar_dir',
                'thumbnailSizes' => [
                    'square' => ['w' => 200, 'h' => 200, 'crop' => true],
                ],
                'pathClass' => '\Proffer\Tests\Stubs\TestPath'
            ]
        ];

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$table, $config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->exactly(2))
            ->method('moveUploadedFile')
            ->willReturnCallback(function ($source, $destination) {
                if (!file_exists(pathinfo($destination, PATHINFO_DIRNAME))) {
                    mkdir(pathinfo($destination, PATHINFO_DIRNAME), 0777, true);
                }

                return copy($source, $destination);
            });

        $proffer->beforeSave(
            $this->createMock(Event::class),
            $entity,
            new ArrayObject()
        );

        $this->assertFileExists(TMP . 'ProfferTests' . DS . 'proffertest' . DS . 'photo' .
            DS . 'proffer_test' . DS . 'image_640x480.jpg');
        $this->assertFileExists(TMP . 'ProfferTests' . DS . 'proffertest' . DS . 'avatar' .
            DS . 'proffer_test' . DS . 'image_480x640.jpg');

        $this->assertEquals('image_640x480.jpg', $entity->get('photo'));
        $this->assertEquals('proffer_test', $entity->get('photo_dir'));

        $this->assertEquals('image_480x640.jpg', $entity->get('avatar'));
        $this->assertEquals('proffer_test', $entity->get('avatar_dir'));
    }

    /**
     * Test that uploads are processed correctly when the upload is it's own entity. For when users associate many
     * uploads with a single parent item. Such as Posts hasMany Uploads
     *
     * @return void
     */
    public function testMultipleAssociatedUploads()
    {
        $eventManager = $this->createMock(EventManager::class);

        $uploadsSchema = $this->getMockBuilder(TableSchema::class)
            ->setConstructorArgs([
                'uploads',
                [
                    'photo' => 'string',
                    'photo_dir' => 'string'
                ]
            ])
            ->getMock();

        $uploadsTable = $this->getMockBuilder(Table::class)
            ->setConstructorArgs([
                ['schema' => $uploadsSchema]
            ])
            ->getMock();

        $uploadsTable->method('entityClass')->willReturn(Entity::class);
        $uploadsTable->method('alias')->willReturn('Uploads');
        $uploadsTable->method('schema')->willReturn($uploadsSchema);
        $uploadsTable->method('eventManager')->willReturn($eventManager);

        $config = [
            'photo' => [
                'dir' => 'photo_dir',
                'thumbnailSizes' => [
                    'square' => ['w' => 200, 'h' => 200, 'crop' => true],
                ],
                'pathClass' => '\Proffer\Tests\Stubs\TestPath'
            ],
        ];

        $proffer = $this->getMockBuilder(ProfferBehavior::class)
            ->setConstructorArgs([$uploadsTable, $config])
            ->setMethods(['moveUploadedFile'])
            ->getMock();

        $proffer->expects($this->exactly(1))
            ->method('moveUploadedFile')
            ->willReturnCallback(function ($source, $destination) {
                if (!file_exists(pathinfo($destination, PATHINFO_DIRNAME))) {
                    mkdir(pathinfo($destination, PATHINFO_DIRNAME), 0777, true);
                }

                return copy($source, $destination);
            });

        $entity = new Entity([
            'name' => 'image_640x480.jpg',
            'tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
            'size' => 33000,
            'error' => UPLOAD_ERR_OK
        ]);

        $proffer->beforeSave(
            $this->getMockBuilder(Event::class)
                ->setConstructorArgs(['Model.beforeSave'])
                ->getMock(),
            $entity,
            new ArrayObject()
        );

        $this->assertFileExists(TMP . 'ProfferTests' . DS . 'uploads' . DS . 'photo' . DS . 'proffer_test' . DS . 'image_640x480.jpg');

        $this->assertEquals('image_640x480.jpg', $entity->get('photo'));
        $this->assertEquals('proffer_test', $entity->get('photo_dir'));
    }
}
