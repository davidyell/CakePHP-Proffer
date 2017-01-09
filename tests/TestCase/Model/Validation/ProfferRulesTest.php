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
