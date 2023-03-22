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

    /**
     * @var string
     */
    private $path = __DIR__ . '/../../data';

    /**
     * Get JSON file path
     *
     * @param string $type Type (either 'pci', 'usb' or 'oui')
     *
     * @return string
     */
    public function getJsonFilePath($type)
    {
        return $this->path . '/' . $type . '.json';
    }

    /**
     * Clean all files
     *
     * @return void
     */
    public function cleanFiles()
    {
        $types = [
            self::TYPE_PCI,
            self::TYPE_USB,
            self::TYPE_OUI,
            self::TYPE_IFTYPE
        ];
        foreach ($types as $type) {
            @unlink($this->getJsonFilePath($type));
            @unlink($this->getSourceFilePath($type));
        }
    }

    /**
     * Download new sources
     *
     * @return void
     */
    public function downloadSources()
    {
        $this->getSourceFile(self::TYPE_PCI, true);
        $this->getSourceFile(self::TYPE_USB, true);
        $this->getSourceFile(self::TYPE_OUI, true);
        $this->getSourceFile(self::TYPE_IFTYPE, true);
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
        $path = $this->path . '/';

        switch ($type) {
            case self::TYPE_PCI:
                $path .= 'pci.ids';
                break;
            case self::TYPE_USB:
                $path .= 'usb.ids';
                break;
            case self::TYPE_OUI:
                $path .= 'oui.txt';
                break;
            case self::TYPE_IFTYPE:
                $path .= 'iftype.csv';
                break;
            default:
                throw new \RuntimeException('Unknown type ' . $type);
        }

        return $path;
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
        $uri = null;

        switch ($type) {
            case self::TYPE_PCI:
                $uri = 'https://pci-ids.ucw.cz/v2.2/pci.ids';
                break;
            case self::TYPE_USB:
                $uri = 'http://www.linux-usb.org/usb.ids';
                break;
            case self::TYPE_OUI:
                $uri = 'https://standards-oui.ieee.org/oui/oui.txt';
                break;
            case self::TYPE_IFTYPE:
                $uri = 'https://www.iana.org/assignments/smi-numbers/smi-numbers-5.csv';
                break;
            default:
                throw new \RuntimeException('Unknown type ' . $type);
        }

        $interval = strtotime('-1 week');
        if (!file_exists($path) || filemtime($path) <= $interval) {
            if ($download === true) {
                $contents = $this->callCurl($uri);
            } else {
                $contents = file_get_contents(__DIR__ . '/../../source_files/' . basename($uri));
            }

            if ($contents == '') {
                throw new \RuntimeException('Empty content');
            }

            file_put_contents(
                $path,
                $contents
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
     * @param string $url        URL to retrieve
     * @param array  $eopts      Extra curl opts
     * @param string $msgerr     human readable error string on error or empty content
     * @param string $curl_error will contains original curl error string if an error occurs
     *
     * @return string
     */
    public function callCurl($url, array $eopts = [], &$msgerr = null, &$curl_error = null)
    {
        $content = '';
        $taburl  = parse_url($url);

        $defaultport = 80;

        // Manage standard HTTPS port : scheme detection or port 443
        if (
            (isset($taburl["scheme"]) && $taburl["scheme"] == 'https')
            || (isset($taburl["port"]) && $taburl["port"] == '443')
        ) {
            $defaultport = 443;
        }

        $ch = curl_init($url);
        $opts = [
            CURLOPT_URL             => $url,
            CURLOPT_USERAGENT       => "GLPI/Inventory format 1.0",
            CURLOPT_RETURNTRANSFER  => 1,
        ] + $eopts;

        curl_setopt_array($ch, $opts);
        $content = curl_exec($ch);
        $curl_error = curl_error($ch) ?: null;
        curl_close($ch);

        if ($curl_error !== null) {
            $content = '';
        }

        if (empty($content)) {
            $msgerr = sprintf(
                'No data available on %s',
                $url
            );
        }

        if (!empty($msgerr)) {
            throw new \RuntimeException($msgerr);
        }

        return $content;
    }
}
