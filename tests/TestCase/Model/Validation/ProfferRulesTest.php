<?php

namespace Proffer\Tests\Model\Validation;

use Cake\Core\Plugin;
use PHPUnit_Framework_TestCase;
use Proffer\Model\Validation\ProfferRules;

class ProfferRulesTest extends PHPUnit_Framework_TestCase
{

    private $Rules;

    public function setUp()
    {
        $this->Rules = new ProfferRules;
    }
    
    public function providerFilesize()
    {
        return [
            [
                ['size' => 30000],
                35000,
                true
            ],
            [
                ['size' => 35000],
                30000,
                false
            ],
            [
                ['size' => 35000],
                35000,
                true
            ],
        ];
    }
    
    /**
     * @dataProvider providerFilesize
     */
    public function testFilesize($value, $check, $expected)
    {
        $result = $this->Rules->filesize($value, $check);
        $this->assertEquals($expected, $result);
    }
    
    public function providerExtension()
    {
        return [
            [
                ['name' => 'image.jpg'],
                ['jpg', 'jpeg', 'gif', 'png'],
                true
            ],
            [
                ['name' => 'image.gif'],
                ['jpg', 'png'],
                false
            ],
        ];
    }
    
    /**
     * @dataProvider providerExtension
     */
    public function testExtension($value, $extensions, $expected)
    {
        $result = $this->Rules->extension($value, $extensions);
        $this->assertEquals($expected, $result);
    }
    
    public function providerMimeType()
    {
        return [
            [
                ['tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg'],
                ['image/jpeg'],
                true
            ],
            [
                ['tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg'],
                ['image/gif'],
                false
            ],
        ];
    }
    
    /**
     * @dataProvider providerMimeType
     */
    public function testMimeType($value, $types, $expected)
    {
        $result = $this->Rules->mimetype($value, $types);
        $this->assertEquals($expected, $result);
    }
    
    public function providerDimensions()
    {
        return [
            [
                ['tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg'],
                [
                    'min' => ['w' => 100, 'h' => 100],
                    'max' => ['w' => 500, 'h' => 500]
                ],
                false
            ],
            [
                ['tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg'],
                [
                    'min' => ['w' => 700, 'h' => 500],
                    'max' => ['w' => 1000, 'h' => 800]
                ],
                false
            ],
            [
                ['tmp_name' => Plugin::path('Proffer') . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg'],
                [
                    'min' => ['w' => 100, 'h' => 100],
                    'max' => ['w' => 700, 'h' => 700]
                ],
                true
            ],
        ];
    }
    
    /**
     * @dataProvider providerDimensions
     */
    public function testDimensions($value, $dimensions, $expected)
    {
        $result = $this->Rules->dimensions($value, $dimensions);
        $this->assertEquals($expected, $result);
    }
}
