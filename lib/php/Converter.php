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
class Converter
{
    const LAST_VERSION = 0.1;

    private $target_version;
    private $debug = false;
    /** XML a different steps. Used for debug only */
    private $steps;
    private $mapping = [
        '01'   => 0.1
    ];
    private $schema_patterns;

    /**
     * @var array
     *
     * A two dimensions array with types as key,
     * and nodes names as values.
     *
     * Node name is expected to be defined with its parent
     * separated with a "/":
     * $convert_types = [
     *     'integer' => [
     *         'drives/free'
     *     ]
     * ];
     *
     * With above example, 'drives/free' will replace all
     * entries found as $drives['free'] and $drives[$i]['free']
     * with their value casted to integer.
     *
     * @see Converter::getCastedValue() for supported types
     * @see Converter::convertTypes() for usage
     */
    private $convert_types;

    /**
     * Instanciate converte
     *
     * @param double|null $target_version JSON schema based version to target. Use last version if null.
     */
    public function __construct($target_version = null)
    {
        if ($target_version === null) {
            $target_version = self::LAST_VERSION;
        }

        if (!is_double($target_version)) {
            throw new \UnexpectedValueException('Version must be a double!');
        }

        $this->target_version = $target_version;
    }

    /**
     * Get target version
     *
     * @return double
     */
    public function getTargetVersion()
    {
        return $this->target_version;
    }

    /**
     * Set debug on/off
     *
     * @param boolean $debug Debug active or not
     *
     * @return Converter
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
        return $this;
    }

    /**
     * Is debug mode on?
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Get path to schema
     *
     * @return string
     */
    public function getSchemaPath()
    {
        return realpath(__DIR__ . '/../../inventory.schema.json');
    }

    /**
     * Do validation (against last schema only!)
     *
     * @param array $json Converted data to validate
     *
     * @return boolean
     */
    public function validate($json)
    {
        $validator = new \JsonSchema\Validator();
        $json = json_decode($json);
        $validator->validate(
            $json,
            (object)['$ref' => 'file://' . $this->getSchemaPath()]
        );

        if (!$validator->isValid()) {
            $errmsg = "JSON does not validate. Violations:\n";
            foreach ($validator->getErrors() as $error) {
                $errmsg .= sprintf(
                    "[%s] %s\n",
                    $error['property'],
                    $error['message']
                );
            }
            throw new \RuntimeException($errmsg);
        }
    }

    /**
     * Do conversion
     *
     * @param string $xml Original XML string
     *
     * @return array
     */
    public function convert($xml)
    {
        libxml_use_internal_errors(true);
        $sxml = simplexml_load_string($xml);
        if ($sxml === false) {
            $errmsg = 'XML string seems invalid.';
            foreach (libxml_get_errors() as $error) {
                $errmsg .= "\n" . $error->message;
            }
            throw new \RuntimeException($errmsg);
        }

        //remove empty nodes
        $removes = $sxml->xpath('//*[not(text()) or normalize-space(.) = ""]');
        for ($i = count($removes) -1; $i >= 0; --$i) {
            unset($removes[$i][0]);
        }

        //convert SimpleXML object to array, recursively.
        $data = json_decode(
            json_encode((array)$sxml),
            1
        );
        $this->loadSchemaPatterns();

        $methods = $this->getMethods();
        foreach ($methods as $method) {
            //reset values to convert for each conversion step
            if ($this->debug === true) {
                $this->steps[] = $data;
            }

            if (!$data = $this->$method($data)) {
                throw new \RuntimeException('Conversion has failed at ' . $method);
            }
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Get methods names we'll have to call in order to convert
     *
     * @return string[]
     */
    public function getMethods()
    {
        $methods = [];

        foreach ($this->mapping as $name => $version) {
            if ($version <= $this->target_version) {
                $methods[] = 'convertTo' . $name;
            }
        }

        return $methods;
    }

    /**
     * Converts to inventory format 0.1
     *
     * @param array $data Contents
     *
     * @return array
     */
    private function convertTo01(array $data)
    {
        //all keys are now lowercase
        $data = $this->arrayChangeKeyCaseRecursive($data);

        //replace bad typed values...
        $this->setConvertTypes([
            'boolean'   => [
                'antivirus/enabled',
                'antivirus/enabled',
                'antivirus/uptodate',
                'drives/systemdrive',
                'networks/virtualdev',
                'printers/network',
                'printers/shared',
                'networks/management',
                'softwares/no_remove',
                'licenseinfos/trial'
            ],
            'integer'   => [
                'cpus/core',
                'cpus/speed',
                'cpus/stepping',
                'cpus/thread',
                'cpus/external_clock',
                'drives/free',
                'drives/total',
                'hardware/etime',
                'hardware/memory',
                'hardware/swap',
                'storages/disksize',
                'physical_volumes/free',
                'physical_volumes/pe_size',
                'physical_volumes/pv_pe_count',
                'physical_volumes/size',
                'volume_groups/lv_count',
                'volume_groups/pv_count',
                'volume_groups/free',
                'volume_groups/size',
                'logical_volumes/seg_count',
                'logical_volumes/size',
                'memories/capacity',
                'memories/numslots',
                'processes/pid',
                'processes/virtualmemory',
                'monitors/port',
                'networks/mtu',
                'softwares/filesize',
                'virtualmachines/memory',
                'virtualmachines/vcpu',
                'videos/memory'
            ]
        ]);
        $this->convertTypes($data);

        //replace arrays...
        $arrays = [
            'cpus',
            'local_users',
            'local_groups',
            'ports',
            'sounds',
            'usbdevices',
            'batteries',
            'firewall',
            'monitors',
            'printers',
            'storages',
            'slots',
            'modems',
            'licenseinfos',
            'antivirus',
            'drives',
            'users',
            'networks',
            'controllers',
            'envs',
            'inputs',
            'logical_volumes',
            'physical_volumes',
            'volume_groups',
            'memories',
            'processes',
            'softwares',
            'virtualmachines',
            'firmwares',
            'simcards',
            'sensors',
            'powersupplies',
            'videos',
            'remote_mgmt'
        ];

        foreach ($arrays as $array) {
            if (isset($data['content'][$array]) && !isset($data['content'][$array][0])) {
                $data['content'][$array] = [$data['content'][$array]];
            }
        }

        $sub_arrays = [
            'local_groups/member',
            'versionprovider/comments'
        ];

        foreach ($sub_arrays as $array) {
            $splitted = explode('/', $array);
            if (isset($data['content'][$splitted[0]])) {
                foreach ($data['content'][$splitted[0]] as $key => &$parray) {
                    if ($key == $splitted[1] && !is_array($parray)) {
                        $parray = [$parray];
                    } elseif (isset($parray[$splitted[1]]) && !is_array($parray[$splitted[1]])) {
                        $parray[$splitted[1]] = [$parray[$splitted[1]]];
                    }
                }
            }
        }

        //lowercase...
        if (isset($data['content']['networks'])) {
            foreach ($data['content']['networks'] as &$network) {
                if (isset($network['status'])) {
                    $network['status'] = strtolower($network['status']);
                }
                if (isset($network['type'])) {
                    $network['type'] = strtolower($network['type']);
                    if ($network['type'] == 'local') {
                        $network['type'] = 'loopback';
                    }
                    if (!in_array($network['type'], $this->schema_patterns['networks_types'])) {
                        unset($network['type']);
                    }
                }
            }
        }

        //plurals
        if (isset($data['content']['firewall'])) {
            $data['content']['firewalls'] = $data['content']['firewall'];
            unset($data['content']['firewall']);
        }

        if (isset($data['content']['local_groups'])) {
            foreach ($data['content']['local_groups'] as &$group) {
                if (isset($group['member'])) {
                    $group['members'] = $group['member'];
                    unset($group['member']);
                }
            }
        }

        $ns_pattern = '/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}(:[0-9]{1,2})?$/';
        //processes dates misses seconds!
        if (isset($data['content']['processes'])) {
            foreach ($data['content']['processes'] as &$process) {
                if (isset($process['started'])) {
                    if (preg_match($ns_pattern, $process['started'])) {
                        try {
                            $started = new \DateTime($process['started']);
                            $process['started'] = $started->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            //not valid, drop.
                            unset($process['started']);
                        }
                    } else {
                        //not valid, drop.
                        unset($process['started']);
                    }
                }
            }
        }

        //rename installdate to install_date; change date format
        if (isset($data['content']['softwares'])) {
            foreach ($data['content']['softwares'] as &$soft) {
                if (isset($soft['installdate'])) {
                    $soft['install_date'] = $soft['installdate'];
                    unset($soft['installdate']);
                    $soft['install_date'] = $this->convertDate(
                        $soft['install_date'],
                        'Y-m-d'
                    );
                }
            }
        }

        //change dates formats
        if (isset($data['content']['batteries'])) {
            foreach ($data['content']['batteries'] as &$battery) {
                if (isset($battery['date'])) {
                    $battery['date'] = $this->convertDate(
                        $battery['date'],
                        'Y-m-d'
                    );
                }
            }
        }

        if (isset($data['content']['bios'])) {
            if (isset($data['content']['bios']['bdate'])) {
                $data['content']['bios']['date'] = $this->convertDate(
                    $data['content']['bios']['bdate'],
                    'Y-m-d'
                );
                unset($data['content']['bios']['bdate']);
            }
        }

        if (isset($data['content']['antivirus'])) {
            foreach ($data['content']['antivirus'] as &$av) {
                if (isset($av['expiration'])) {
                    $av['expiration'] = $this->convertDate(
                        $av['expiration'],
                        'Y-m-d'
                    );
                }
            }
        }

        //storages serial-ata with several cases
        if (isset($data['content']['storages'])) {
            foreach ($data['content']['storages'] as &$storage) {
                if (isset($storage['interface']) && strtolower($storage['interface']) == 'serial-ata') {
                    $storage['interface'] = 'SATA';
                }
            }
        }

        //some envs may have an empty value, dropped along with all empty nodes
        if (isset($data['content']['envs'])) {
            foreach ($data['content']['envs'] as &$env) {
                if (!isset($env['val'])) {
                    $env['val'] = '';
                }
            }
        }

        //slot status in old versions
        if (isset($data['content']['slots'])) {
            foreach ($data['content']['slots'] as &$slot) {
                if (isset($slot['status'])) {
                    switch (strtolower($slot['status'])) {
                        case 'in use':
                            $slot['status'] = 'used';
                            break;
                        case 'available':
                            $slot['status'] = 'free';
                            break;
                        case 'unknown':
                        default:
                            unset($slot['status']);
                            break;
                    }
                }
            }
        }

        //vm status in old versions
        if (isset($data['content']['virtualmachines'])) {
            foreach ($data['content']['virtualmachines'] as &$vm) {
                if (isset($vm['vmtype'])) {
                    $vm['vmtype'] = strtolower($vm['vmtype']);
                    switch (strtolower($vm['vmtype'])) {
                        case 'hyper-v':
                            $vm['vmtype'] = 'hyperv';
                            break;
                        case 'solaris zones':
                        case 'solaris zone':
                            $vm['vmtype'] = 'solariszone';
                            break;
                    }
                }
                if (isset($vm['status'])) {
                    switch (strtolower($vm['status'])) {
                        case 'pause':
                            $vm['status'] = 'paused';
                            break;
                        case 'stopped':
                            $vm['status'] = 'off';
                            break;
                    }
                }
            }
        }

        if (!isset($data['content']['versionclient']) && isset($data['content']['hardware']['versionclient'])) {
            $data['content']['versionclient'] = $data['content']['hardware']['versionclient'];
        }

        return $data;
    }

    /**
     * Set convert types
     *
     * @param array $convert_types COnvert types cnfiguration
     *
     * @return Converter
     */
    public function setConvertTypes(array $convert_types)
    {
        $this->convert_types = $convert_types;
        return $this;
    }

    /**
     * Converts values for all entries of name to requested type
     *
     * Method must populate $convert_types array.
     * @see Converter::convert_types parameter
     *
     * @param array $data Input data, will be modified
     *
     * @return void
     */
    public function convertTypes(&$data)
    {
        $types = $this->convert_types;
        foreach ($types as $type => $names) {
            foreach ($names as $name) {
                $keys = explode('/', $name);
                if (count($keys) != 2) {
                    throw new \RuntimeException('Not suported!');
                }
                if (isset($data['content'][$keys[0]])) {
                    if (is_array($data['content'][$keys[0]])) {
                        foreach ($data['content'][$keys[0]] as $key => $value) {
                            if (isset($data['content'][$keys[0]][$key][$keys[1]])) {
                                $data['content'][$keys[0]][$key][$keys[1]] =
                                    $this->getCastedValue(
                                        $data['content'][$keys[0]][$key][$keys[1]],
                                        $type
                                    );
                            }
                        }
                    } else {
                        if (isset($data['content'][$keys[0]][$keys[1]])) {
                            $data['content'][$keys[0]][$keys[1]] =
                                $this->getCastedValue(
                                    $data['content'][$keys[0]][$keys[1]],
                                    $type
                                );
                        }
                    }
                }
                if (isset($data['content'][$keys[0]]) && isset($data['content'][$keys[0]][$keys[1]])) {
                    $data['content'][$keys[0]][$keys[1]] = $this->getCastedValue(
                        $data['content'][$keys[0]][$keys[1]],
                        $type
                    );
                }
            }
        }
    }


    /**
     * Get value casted
     *
     * @param string $value Original value
     * @param string $type  Requested type
     *
     * @return mixed
     */
    public function getCastedValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            default:
                throw new \UnexpectedValueException('Type ' . $type . ' not known.');
        }
    }

    /**
     * Change array keys case recursively
     *
     * @param array $array Input array
     *
     * @return array
     */
    public function arrayChangeKeyCaseRecursive(array $array)
    {
        return array_map(
            function ($item) {
                if (is_array($item)) {
                    $item = $this->arrayChangeKeyCaseRecursive($item);
                }
                return $item;
            },
            array_change_key_case($array)
        );
    }

    /**
     * Convert a date
     *
     * @param string $value  Current value
     * @param string $format Format for output
     *
     * @return string
     */
    public function convertDate($value, $format)
    {
        $formats = [
            'D M d H:i:s Y', //Thu Mar 14 15:05:41 2013
            'd/m/Y H:i:s',
            'Y-m-d H:i:s',
            'd/m/Y H:i',
            'Y-m-d H:i',
            'd/m/Y',
            'm/d/Y',
            'Y-m-d'
        ];
        try {
            while ($current = array_shift($formats)) {
                $d = \DateTime::createFromFormat($current, $value);
                if ($d !== false) {
                    break;
                }
            }

            if ($d !== false) {
                return $d->format($format);
            }
            return $value;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Load schema patterns that will be used to validate
     *
     * @return void
     */
    public function loadSchemaPatterns()
    {
        $string = file_get_contents($this->getSchemaPath());
        $json = json_decode($string, true);

        $this->schema_patterns['networks_types'] = explode(
            '|',
            str_replace(
                ['^(', ')$'],
                ['', ''],
                $json['properties']['content']['properties']['networks']['items']['properties']['type']['pattern']
            )
        );
    }
}
