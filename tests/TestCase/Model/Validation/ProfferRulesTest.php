<?php
declare(strict_types=1);

namespace Proffer\Tests\Model\Validation;

use Cake\TestSuite\TestCase;
use Laminas\Diactoros\UploadedFile;
use Proffer\Model\Validation\ProfferRules;

class ProfferRulesTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadPlugins(['Proffer' => ['path' => ROOT]]);
    }

    public function providerDimensions()
    {
        return [
            [
                new UploadedFile(
                    ROOT . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                    45704,
                    UPLOAD_ERR_OK
                ),
                [
                    'min' => ['w' => 100, 'h' => 100],
                    'max' => ['w' => 500, 'h' => 500],
                ],
                false,
            ],
            [
                new UploadedFile(
                    ROOT . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                    45704,
                    UPLOAD_ERR_OK
                ),
                [
                    'min' => ['w' => 700, 'h' => 500],
                    'max' => ['w' => 1000, 'h' => 800],
                ],
                false,
            ],
            [
                new UploadedFile(
                    ROOT . 'tests' . DS . 'Fixture' . DS . 'image_640x480.jpg',
                    45704,
                    UPLOAD_ERR_OK
                ),
                [
                    'min' => ['w' => 100, 'h' => 100],
                    'max' => ['w' => 700, 'h' => 700],
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerDimensions
     */
    public function testDimensions($value, $dimensions, $expected)
    {
        $result = ProfferRules::dimensions($value, $dimensions);
        $this->assertEquals($expected, $result);
    }
}
