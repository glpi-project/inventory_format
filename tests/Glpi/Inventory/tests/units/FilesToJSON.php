<?php

/**
 * © Teclib' and contributors.
 *
 * This file is part of GLPI inventory format.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glpi\Inventory\tests\units;

use PHPUnit\Framework\TestCase;

/**
 * Tests for hardware file conversions
 *
 * @author    Johan Cwiklinski <jcwiklinski@teclib.com>
 */
class FilesToJSON extends TestCase
{
    private $path = __DIR__ . '/../../../../../data/';

    protected function setUp(): void
    {
        $this->clean();
    }

    protected function tearDown(): void
    {
        $this->clean();
    }

    private function clean()
    {
        $json_files = glob($this->path . '*.json');
        array_map('unlink', $json_files);
    }

    /**
     * Files provider
     *
     * @return array
     */
    public static function filesProvider()
    {
        return [
            [
                'filename' => 'pciid',
                'method'   => 'convertPciFile'
            ], [
                'filename' => 'usbid',
                'method'   => 'convertUsbFile'
            ], [
                'filename' => 'ouis',
                'method'   => 'convertOUIFile'
            ], [
                'filename' => 'iftype',
                'method'   => 'convertIftypeFile'
            ],
        ];
    }

    /**
     * Test file conversion
     *
     * @dataProvider filesProvider
     *
     * @param string $filename Filename without extension
     * @param string $method   Method to call
     *
     * @return void
     */
    public function testConvertFile($filename, $method)
    {
        $instance = new \Glpi\Inventory\FilesToJSON();

        $file = $this->path . '/' . $filename . '.json';

        //checks file exists
        $this->assertFalse(file_exists($file), 'JSON file already exists');

        $method = new \ReflectionMethod($instance, $method);
        $method->setAccessible(true);
        $method->invoke($instance);

        $this->assertTrue(file_exists($file), 'JSON file has not been generated');
    }

    public function testRun()
    {
        $instance = new \Glpi\Inventory\FilesToJSON();

        $types = [
            'pciid'  => $this->path . 'pciid.json',
            'usbid'  => $this->path . 'usbid.json',
            'ouis'   => $this->path . 'ouis.json',
            'iftype' => $this->path . 'iftype.json',
        ];

        // Ensure files are not existing
        foreach ($types as $type => $filepath) {
            $this->assertFalse(file_exists($filepath), sprintf('JSON file "%s" already exists', $type));
        }

        $instance->run();

        // Ensure files are generated
        foreach ($types as $type => $filepath) {
            $this->assertTrue(file_exists($filepath), sprintf('JSON file "%s" has not been generated', $type));
        }
    }
}
