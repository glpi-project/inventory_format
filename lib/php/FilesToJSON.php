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

namespace Glpi\Inventory;

/**
 * Converts old FusionInventory XML format to new JSON schema
 * for automatic inventory.
 *
 * @category  Inventory
 * @package   Glpi
 * @author    Johan Cwiklinski <jcwiklinski@teclib.com>
 * @copyright 2018 GLPI Team and Contributors
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://glpi-project.org
 */
class FilesToJSON
{
    public const TYPE_PCI = 'pciid';
    public const TYPE_USB = 'usbid';
    public const TYPE_OUI = 'ouis';
    public const TYPE_IFTYPE = 'iftype';

    private const SOURCES = [
        self::TYPE_PCI    => 'https://pci-ids.ucw.cz/v2.2/pci.ids',
        self::TYPE_USB    => 'http://www.linux-usb.org/usb.ids',
        self::TYPE_OUI    => 'https://standards-oui.ieee.org/oui/oui.txt',
        self::TYPE_IFTYPE => 'https://www.iana.org/assignments/smi-numbers/smi-numbers-5.csv',
    ];

    /**
     * Path of data files (JSON files).
     * @var string
     */
    private $data_path = __DIR__ . '/../../data';

    /**
     * Path of source files (JSON files).
     * @var string
     */
    private $sources_path = __DIR__ . '/../../source_files';

    /**
     * Get JSON file path
     *
     * @param string $type Type (either 'pci', 'usb' or 'oui')
     *
     * @return string
     */
    public function getJsonFilePath($type)
    {
        return $this->data_path . '/' . $type . '.json';
    }

    /**
     * Download new sources
     *
     * @return void
     */
    public function refreshSources()
    {
        foreach (self::SOURCES as $type => $uri) {
            $contents = $this->callCurl($uri);

            file_put_contents(
                $this->getSourceFilePath($type),
                $contents
            );
        }
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
     * Get source file path
     *
     * @param string $type File type
     *
     * @return string
     */
    public function getSourceFilePath($type)
    {
        if (!array_key_exists($type, self::SOURCES)) {
            throw new \RuntimeException('Unknown type ' . $type);
        }

        return $this->sources_path . '/' . basename(self::SOURCES[$type]);
    }

    /**
     * Get file for type
     *
     * @param string  $type     Type
     * @param boolean $download Whether to download source files from upstream oor use provided ones
     *
     * @return resource
     */
    protected function getSourceFile($type, $download = false)
    {
        $path = $this->getSourceFilePath($type);
        return fopen($path, 'r');
    }

    /**
     * Convert PCI file from IDS to JSON
     *
     * @return int|false
     */
    public function convertPciFile()
    {
        $pciFile = $this->getSourceFile(self::TYPE_PCI);
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
                $pci_ids[$vendorId . '::' . $deviceId] = $stack[2];
            }
        }

        return file_put_contents($this->getJsonFilePath(self::TYPE_PCI), json_encode($pci_ids, JSON_PRETTY_PRINT));
    }

    /**
     * Convert USB file from IDS to JSON
     *
     * @return int|false
     */
    public function convertUsbFile()
    {
        $usbFile = $this->getSourceFile(self::TYPE_USB);
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
                $usb_ids[$vendorId . '::' . $deviceId] = $stack[2];
            }
        }

        return file_put_contents($this->getJsonFilePath(self::TYPE_USB), json_encode($usb_ids, JSON_PRETTY_PRINT));
    }


    /**
     * Convert OUI file from TXT to JSON
     *
     * @return int|false
     */
    public function convertOUIFile()
    {
        $ouiFile = $this->getSourceFile(self::TYPE_OUI);
        $ouis = [];

        while (!feof($ouiFile)) {
            $buffer = fgets($ouiFile, 4096);

            $stack = [];
            if (preg_match("/^(\S+)\s*\(hex\)\t{2}(.+)/i", $buffer, $stack)) {
                $mac = strtr($stack[1], '-', ':');
                $ouis[$mac] = trim($stack[2]);
            }
        }

        return file_put_contents($this->getJsonFilePath(self::TYPE_OUI), json_encode($ouis, JSON_PRETTY_PRINT));
    }

    /**
     * Convert iftype file from CSV to JSON
     *
     * @return int|false
     */
    public function convertIftypeFile()
    {
        $iftypeFile = $this->getSourceFile(self::TYPE_IFTYPE);
        $iftypes = [];

        while (($line = fgetcsv($iftypeFile)) !== false) {
            $iftypes[] = [
                'decimal'     => $line[0],
                'name'        => $line[1],
                'description' => $line[2] ?? '',
                'references'  => $line[3] ?? ''
            ];
        }

        return file_put_contents($this->getJsonFilePath(self::TYPE_IFTYPE), json_encode($iftypes, JSON_PRETTY_PRINT));
    }

    /**
     * Executes a curl call
     *
     * @param string $url   URL to retrieve
     *
     * @return string
     */
    public function callCurl($url): string
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_URL             => $url,
            CURLOPT_USERAGENT       => "GLPI/Inventory format 1.0",
            CURLOPT_RETURNTRANSFER  => 1,
        ];

        curl_setopt_array($ch, $opts);
        $content = curl_exec($ch);
        $curl_error = curl_error($ch) ?: null;
        curl_close($ch);

        if ($curl_error !== null) {
            throw new \RuntimeException($curl_error);
        }

        if (empty($content)) {
            throw new \RuntimeException(sprintf('No data available on %s', $url));
        }

        return $content;
    }
}
