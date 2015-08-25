<?php
/**
 * Created by PhpStorm.
 *
 * @author David Yell <neon1024@gmail.com>
 */
namespace Proffer\Tests\Lib;

use Cake\Core\Plugin;
use Cake\ORM\Entity;
use PHPUnit_Framework_TestCase;
use Proffer\Lib\ProfferPath;

class ProfferPathTest extends PHPUnit_Framework_TestCase
{

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

    public function pathDataProvider()
    {
        return [
            [
                [
                    'field' => 'photo',
                    'entity' => [
                        'photo' => 'image_640x480.jpg',
                        'photo_dir' => 'proffer_test'
                    ],
                    'settings' => [
                        'photo' => [
                            'root' => TMP . 'ProfferTest',
                            'dir' => 'photo_dir',
                            'thumbnailSizes' => [
                                'square' => ['w' => 100, 'h' => 100],
                                'squareCrop' => ['w' => 100, 'h' => 100, 'crop' => true]
                            ]
                        ]
                    ]
                ],
                [
                    TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' .
                    DS . 'image_640x480.jpg',
                    TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' .
                    DS . 'square_image_640x480.jpg',
                    TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' .
                    DS . 'squareCrop_image_640x480.jpg'
                ]
            ],
            [
                [
                    'field' => 'profile_picture_image',
                    'entity' => [
                        'profile_picture_image' => 'image_640x480.jpg',
                        'profile_pictures_dir' => 'proffer_test'
                    ],
                    'settings' => [
                        'profile_picture_image' => [
                            'root' => TMP . 'ProfferTest',
                            'dir' => 'profile_pictures_dir',
                            'thumbnailSizes' => [
                                'portrait' => ['w' => 300, 'h' => 100],
                                'portraitCropped' => ['w' => 350, 'h' => 120, 'crop' => true]
                            ]
                        ]
                    ]
                ],
                [
                    TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'profile_picture_image' . DS . 'proffer_test' .
                    DS . 'image_640x480.jpg',
                    TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'profile_picture_image' . DS . 'proffer_test' .
                    DS . 'portrait_image_640x480.jpg',
                    TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'profile_picture_image' . DS . 'proffer_test' .
                    DS . 'portraitCropped_image_640x480.jpg'
                ]
            ],
        ];
    }

    /**
     * @dataProvider pathDataProvider
     * @param array $data Set of data for the test
     * @param array $expected Expected set of results
     */
    public function testConstructedFullPath($data, $expected)
    {
        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->setMethods(['alias'])
            ->getMock();
        $table->method('alias')
            ->willReturn('ProfferTest');

        $entity = new Entity($data['entity']);

        $path = new ProfferPath($table, $entity, $data['field'], $data['settings'][$data['field']]);

        $i = 1;
        foreach ($data['settings'][$data['field']]['thumbnailSizes'] as $prefix => $dimensions) {
            $this->assertEquals($expected[$i], $path->fullPath($prefix));
            $i++;
        }

        $this->assertEquals($expected[0], $path->fullPath());
    }

    public function testGetFolder()
    {
        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->setMethods(['alias'])
            ->getMock();
        $table->method('alias')
            ->willReturn('ProfferTest');

        $entity = new Entity([
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ]);

        $settings = [
            'root' => TMP . 'ProfferTest',
            'dir' => 'photo_dir',
            'thumbnailSizes' => [
                'square' => ['w' => 100, 'h' => 100],
                'squareCrop' => ['w' => 100, 'h' => 100, 'crop' => true]
            ]
        ];

        $path = new ProfferPath($table, $entity, 'photo', $settings);
        $result = $path->getFolder();
        $expected = TMP . 'ProfferTest' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' . DS;

        $this->assertEquals($result, $expected);
    }

    public function testPrefixes()
    {
        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->setMethods(['alias'])
            ->getMock();
        $table->method('alias')
            ->willReturn('ProfferTest');

        $entity = new Entity([
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ]);

        $settings = [
            'root' => TMP . 'ProfferTest',
            'dir' => 'photo_dir',
            'thumbnailSizes' => [
                'square' => ['w' => 100, 'h' => 100],
                'squareCrop' => ['w' => 100, 'h' => 100, 'crop' => true]
            ]
        ];
        $expected = ['square', 'squareCrop'];

        $path = new ProfferPath($table, $entity, 'photo', $settings);
        $result = $path->getPrefixes();

        $this->assertEquals($expected, $result);
    }

    public function testDeleteFiles()
    {
        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->setMethods(['alias'])
            ->getMock();
        $table->method('alias')
            ->willReturn('ProfferTest');

        $entity = new Entity([
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ]);

        $settings = [
            'root' => TMP . 'ProfferTests',
            'dir' => 'photo_dir',
            'thumbnailSizes' => [
                'square' => ['w' => 100, 'h' => 100],
                'squareCrop' => ['w' => 100, 'h' => 100, 'crop' => true]
            ]
        ];

        $path = $this->getMockBuilder('Proffer\Lib\ProfferPath')
            ->setConstructorArgs([$table, $entity, 'photo', $settings])
            ->setMethods(['getFolder'])
            ->getMock();

        $path->expects($this->any())
            ->method('getFolder')
            ->willReturn(TMP . 'ProfferTests' . DS . $table->alias() . DS . 'photo' . DS . 'proffer_test' . DS);

        $path = new ProfferPath($table, $entity, 'photo', $settings);

        if (!file_exists($path->getFolder())) {
            mkdir($path->getFolder(), 0777, true);
        }

        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' .
            DS . 'image_640x480.jpg',
            $path->getFolder() . 'image_640x480.jpg'
        );
        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' .
            DS . 'image_640x480.jpg',
            $path->getFolder() . 'square_image_640x480.jpg'
        );
        copy(
            Plugin::path('Proffer') . 'tests' . DS . 'Fixture' .
            DS . 'image_640x480.jpg',
            $path->getFolder() . 'portrait_image_640x480.jpg'
        );

        $path->deleteFiles($path->getFolder());

        $this->assertFileNotExists($path->getFolder() . 'image_640x480.jpg');
        $this->assertFileNotExists($path->getFolder() . 'square_image_640x480.jpg');
        $this->assertFileNotExists($path->getFolder() . 'portrait_image_640x480.jpg');

        $path->deleteFiles($path->getFolder(), true);

        $this->assertFileNotExists($path->getFolder());
    }

    public function testCreatingPathFolderWhichExists()
    {
        $table = $this->getMockBuilder('Cake\ORM\Table')
            ->setMethods(['alias'])
            ->getMock();
        $table->method('alias')
            ->willReturn('ProfferTest');

        $entity = new Entity([
            'photo' => 'image_640x480.jpg',
            'photo_dir' => 'proffer_test'
        ]);

        $settings = [
            'root' => TMP . 'ProfferTests',
            'dir' => 'photo_dir',
            'thumbnailSizes' => [
                'square' => ['w' => 100, 'h' => 100],
                'squareCrop' => ['w' => 100, 'h' => 100, 'crop' => true]
            ]
        ];

        $path = new ProfferPath($table, $entity, 'photo', $settings);

        mkdir(TMP . 'ProfferTests' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' . DS, 0777, true);

        $result = $path->createPathFolder();
        $this->assertEquals(true, $result);
    }
}
