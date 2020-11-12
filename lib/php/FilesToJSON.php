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
 * @author    Johan Cwiklinski <jcwiklinski@telcib.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://glpi-project.org
 */

namespace Glpi\Inventory;

/**
 * Converts old FusionInventory XML format to new JSON schema
 * for automatic inventory.
 *
 * @category  Inventory
 * @package   Glpi
 * @author    Johan Cwiklinski <jcwiklinski@telcib.com>
 * @copyright 2018 GLPI Team and Contributors
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://glpi-project.org
 */
class FilesToJSON
{
    private $type_pci = 'pciid';
    private $type_usb = 'usbid';
    private $type_oui = 'ouis';
    private $type_iftype = 'iftype';
    private $path = __DIR__ . '/../../data';

    /**
     * Get JSON file path
     *
     * @param string $type Type (either 'pci', 'usb' or 'oui')
     *
     * @return string
     */
    public function getPathFor($type)
    {
        $type = 'type_' . $type;
        return $this->path . '/' . $this->$type . '.json';
    }

    /**
     * Runs all conversions
     *
     * @return void
     */
    public function run()
    {
        $pci = $this->convertPciFile();
        if ($pci === false) {
            throw new \RuntimeException('PCI JSON file has not been written!');
        }

        $usb = $this->convertUsbFile();
        if ($usb === false) {
            throw new \RuntimeException('USB JSON file has not been written!');
        }

        $oui = $this->convertOUIFile();
        if ($oui === false) {
            throw new \RuntimeException('OUI JSON file has not been written!');
        }

        $iftype = $this->convertIftypeFile();
        if ($iftype === false) {
            throw new \RuntimeException('IFtype JSON file has not been written!');
        }
    }

    /**
     * Get file for type
     *
     * @param string $type Type
     *
     * @return resource
     */
    protected function getSourceFile($type)
    {
        $path = $this->path . '/';
        $uri = null;

        switch ($type) {
            case 'pci':
                $path .= 'pci.ids';
                $uri = 'http://pciids.sourceforge.net/pci.ids';
                break;
            case 'usb':
                $path .= 'usb.ids';
                $uri = 'http://www.linux-usb.org/usb.ids';
                break;
            case 'oui':
                $path .= 'oui.txt';
                $uri = 'http://standards-oui.ieee.org/oui/oui.txt';
                break;
            case 'iftype':
                ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');
                $path .= 'iftype.csv';
                $uri = 'https://www.iana.org/assignments/smi-numbers/smi-numbers-5.csv';
                break;
            default:
                throw new \RuntimeException('Unknown type ' . $type);
        }

        $interval = strtotime('-1 week');
        if (!file_exists($path) || filemtime($path) <= $interval) {
            file_put_contents(
                $path,
                file_get_contents($uri)
            );
        }
        return fopen($path, 'r');
    }
    /**
     * Convert PCI file from IDS to JSON
     *
     * @return int|false
     */
    public function convertPciFile()
    {
        $pciFile = $this->getSourceFile('pci');
        $pci_ids = [];

        while (!feof($pciFile)) {
            $buffer = fgets($pciFile, 4096);

            $stack = [];
            if (preg_match("/^(\w+)\s*(.+)/i", $buffer, $stack)) {
                $vendorId = $stack[1];
                $pci_ids[$vendorId] = $stack[2];
            }

            $stack = [];
            if (preg_match("/^\t(\w+)\s*(.+)/i", $buffer, $stack)) {
                $deviceId = $stack[1];
                $pci_ids[$vendorId.'::'.$deviceId] = $stack[2];
            }
        }

        return file_put_contents($this->getPathFor('pci'), json_encode($pci_ids, JSON_PRETTY_PRINT));
    }

    /**
     * Convert USB file from IDS to JSON
     *
     * @return int|false
     */
    public function convertUsbFile()
    {
        $usbFile = $this->getSourceFile('usb');
        $usb_ids = [];

        while (!feof($usbFile)) {
            $buffer = fgets($usbFile, 4096);

            $stack = [];
            if (preg_match("/^(\w+)\s*(.+)/i", $buffer, $stack)) {
                $vendorId = $stack[1];
                $usb_ids[$vendorId] = $stack[2];
            }

            $stack = [];
            if (preg_match("/^\t(\w+)\s*(.+)/i", $buffer, $stack)) {
                $deviceId = $stack[1];
                $usb_ids[$vendorId.'::'.$deviceId] = $stack[2];
            }
        }

        return file_put_contents($this->getPathFor('usb'), json_encode($usb_ids, JSON_PRETTY_PRINT));
    }


    /**
     * Convert OUI file from TXT to JSON
     *
     * @return int|false
     */
    public function convertOUIFile()
    {
        $ouiFile = $this->getSourceFile('oui');
        $ouis = [];

        while (!feof($ouiFile)) {
            $buffer = fgets($ouiFile, 4096);

            $stack = [];
            if (preg_match("/^(\S+)\s*\(hex\)\t{2}(.+)/i", $buffer, $stack)) {
                $mac = strtr($stack[1], '-', ':');
                $ouis[$mac] = trim($stack[2]);
            }
        }

        return file_put_contents($this->getPathFor('oui'), json_encode($ouis, JSON_PRETTY_PRINT));
    }

    /**
     * Convert iftype file from CSV to JSON
     *
     * @return int|false
     */
    public function convertIftypeFile()
    {
        $iftypeFile = $this->getSourceFile('iftype');
        $iftypes = [];

        while (($line = fgetcsv($iftypeFile)) !== false) {
            $iftypes[] = [
                'decimal'     => $line[0],
                'name'        => $line[1],
                'description' => $line[2] ?? '',
                'references'  => $line[3] ?? ''
            ];
        }

        return file_put_contents($this->getPathFor('iftype'), json_encode($iftypes, JSON_PRETTY_PRINT));
    }
}
