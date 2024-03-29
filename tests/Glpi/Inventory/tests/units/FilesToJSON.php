<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 *
 * PHP version 7
 *
 * @category  Inventory
 * @package   Glpi
 * @author    Johan Cwiklinski <jcwiklinski@teclib.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://glpi-project.org
 */

namespace Glpi\Inventory\tests\units;

use PHPUnit\Framework\TestCase;

/**
 * Tests for hardware file conversions
 *
 * @category  Inventory
 * @package   GlpiTests
 * @author    Johan Cwiklinski <jcwiklinski@teclib.com>
 * @copyright 2019 GLPI Team and Contributors
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://glpi-project.org
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
