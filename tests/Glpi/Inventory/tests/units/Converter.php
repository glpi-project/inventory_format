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
*/

namespace Glpi\Inventory\tests\units;

class Converter extends \atoum {

    /**
     * Test constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $this
            ->given($this->newTestedInstance())
            ->then
                ->float($this->testedInstance->getTargetVersion())
                    ->isIdenticalTo($this->getTestedClassName()::LAST_VERSION);

        $ver = 156.2;
        $this
            ->given($this->newTestedInstance($ver))
            ->then
                ->float($this->testedInstance->getTargetVersion())
                    ->isIdenticalTo($ver);

        $this->exception(
            function () {
                new \Glpi\Inventory\Converter('abcde');
            }
        )
            ->isInstanceOf('\UnexpectedValueException')
            ->hasMessage('Version must be a double!');
    }

    /**
     * Test debug mode activation
     *
     * @return void
     */
    public function testDebug()
    {
        $this
            ->given($this->newTestedInstance())
            ->then
                ->boolean($this->testedInstance->isDebug())->isFalse();

        $this->object($this->testedInstance->setDebug(true))->isInstanceOf($this->getTestedClassName());
        $this->boolean($this->testedInstance->isDebug())->isTrue();
    }

    /**
     * Test schema path
     *
     * @return void
     */
    public function testSchemaPath()
    {
        $expected = realpath(TU_DIR . '/../inventory.schema.json');
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($this->testedInstance->getSchemaPath())->isIdenticalTo($expected);
    }

    /**
     * Test conversion methods list
     *
     * @return void
     */
    public function testGetMethods()
    {
        $expected = ['convertTo01'];
        $this
            ->given($this->newTestedInstance(0.1))
            ->then
                ->array($this->testedInstance->getMethods())->isIdenticalTo($expected);
    }

    /**
     * Values to cast provider
     *
     * @return array
     */
    protected function valuesToCastProvider()
    {
        return [
            //true real values
            [true, 'boolean', true],
            ['true', 'boolean', true],
            [1, 'boolean', true],
            ['1', 'boolean', true],
            //false real values
            [false, 'boolean', false],
            [0, 'boolean', false],
            ['0', 'boolean', false],
            //true from cast values
            ['false', 'boolean', true],
            ['abcde', 'boolean', true],
            //integers
            [10, 'integer', 10],
            ['10', 'integer', 10],
            ['abcde', 'integer', 0]
        ];
    }

    /**
     * Test cast value
     *
     * @dataProvider valuesToCastProvider
     *
     * @param mixed  $value    Value to cast
     * @param string $cast     Type to cast to
     * @param mixed  $expected Expected casted value
     *
     * @return void
     */
    public function testGetCastedValue($value, $cast, $expected)
    {
        $this
            ->given($this->newTestedInstance())
            ->then
                ->variable($this->testedInstance->getCastedValue($value, $cast))
                    ->isIdenticalTo($expected);
    }

    /**
     * Test cast value exception
     *
     * @return void
     */
    public function testGetCastedValueWE()
    {
        $this->exception(
            function () {
                $this
                    ->given($this->newTestedInstance())
                    ->then
                        ->variable($this->testedInstance->getCastedValue(0, 'blah'));
            }
        )
            ->isInstanceOf('\UnexpectedValueException')
            ->hasMessage('Type blah not known.');
    }

    /**
     * Array case change provider
     *
     * @return array
     */
    protected function arrayForCaseProvider()
    {
        return [
            [
                ['a' => 1, 'b' => 2], null
            ], [
                ['A' => 1, 'b' => 2], ['a' => 1, 'b' => 2]
            ], [
                ['A' => ['D' => 4], 'B' => 3, 'C' => ['EfG' => 5]],
                ['a' => ['d' => 4], 'b' => 3, 'c' => ['efg' => 5]]
            ]
        ];
    }

    /**
     * Test change array keys case recursively
     *
     * @dataProvider arrayForCaseProvider
     *
     * @param array $orig     Original array
     * @param array $expected Expected result
     *
     * @return void
     */
    public function testArrayChangeKeyCaseRecursive($orig, $expected)
    {
        if ($expected === null) {
            $expected = $orig;
        }
        $this
            ->given($this->newTestedInstance())
            ->then
                ->array($this->testedInstance->arrayChangeKeyCaseRecursive($orig))
                    ->isIdenticalTo($expected);
    }

    /**
     * Date to convert provider
     *
     * @return array
     */
    protected function datesToConvertProvider()
    {
        return [
            ['2018-01-12', 'Y-m-d', '2018-01-12'],
            ['01/12/2018', 'Y-m-d', '2018-12-01'],
            ['01/15/2018', 'Y-m-d', '2019-03-01'],
            ['N/A', 'Y-m-d', null],
            ['n/a', 'Y-m-d', null],
            ['', 'Y-m-d', null],
            ['20201207', 'Y-m-d', '2020-12-07'],
            ['03.04.2020', 'Y-m-d', '2020-04-03'],
            ['BOOT_TIME', 'Y-m-d', null],
            ['2014-05-13T00:00:00Z', 'Y-m-d', '2014-05-13']
        ];
    }

    /**
     * Test dates conversion
     *
     * @dataProvider datesToConvertProvider
     *
     * @param string $orig     Original date
     * @param string $format   Format to apply
     * @param string $expected Expected formatted date
     *
     * @return void
     */
    public function testConvertDate($orig, $format, $expected)
    {
        $this
            ->given($this->newTestedInstance())
            ->then
                ->variable($this->testedInstance->convertDate($orig, $format))
                    ->isIdenticalTo($expected);
    }

    public function testConvertTypes()
    {
        $this->newTestedInstance();
        $this->testedInstance->setConvertTypes([
            'boolean'  => [
                'cpus/enabled'
            ],
            'integer'   => [
                'one/two'
            ]
        ]);

        $data = [
            'content'   => [
                'cpus'  => [
                    0   => [
                        'enabled'   => 1
                    ],
                    1   => [
                        'enabled'   => 'y'
                    ],
                    2   => [
                        'enabled'   => 0
                    ],
                    3   => [
                        'enabled'   => '0'
                    ]
                ],
                'one'   => [
                    'two'  => '42'
                ]
            ]
        ];

        $this->testedInstance->convertTypes($data);
        $this->array($data)->isIdenticalTo([
            'content'   => [
                'cpus'  => [
                    0   => [
                        'enabled'   => true
                    ],
                    1   => [
                        'enabled'   => true
                    ],
                    2   => [
                        'enabled'   => false
                    ],
                    3   => [
                        'enabled'   => false
                    ]
                ],
                'one'   => [
                    'two'  => 42
                ]
            ]
        ]);
    }

    /**
     * Batteries capacities convert provider
     *
     * @return array
     */
    protected function batteryCapasToConvertProvider()
    {
        return [
            ['43.7456 Wh', 43746],
            ['512584', 512584],
            ['43746 mWh', 43746],
            ['43.746 mWh', false],
            ['43 7456 Wh', false],
            ['43,7456 Wh', false],
            ['43 Wh', 43000],
            ['2100.0', 2100]
        ];
    }

    /**
     * Test battery capacity conversion
     *
     * @dataProvider batteryCapasToConvertProvider
     *
     * @param string $orig     Original data
     * @param string $expected Expected result
     *
     * @return void
     */
    public function testConvertBatteryCapacity($orig, $expected)
    {
        if ($expected == false) {
            $this
                ->given($this->newTestedInstance())
                ->then
                    ->boolean($this->testedInstance->convertBatteryPower($orig))
                        ->isIdenticalTo($expected);
        } else {
            $this
                ->given($this->newTestedInstance())
                ->then
                    ->integer($this->testedInstance->convertBatteryPower($orig))
                    ->isIdenticalTo($expected);
        }
    }

    /**
     * Batteries voltages convert provider
     *
     * @return array
     */
    protected function batteryVoltsToConvertProvider()
    {
        return [
            ['8 V', 8000],
            ['8.2 V', 8200],
            ['4365 mV', 4365],
            ['8.2 mV', false],
            ['8 2 V', false],
            ['8,2 V', false],
            ['4.255V', 4255]
        ];
    }

    /**
     * Test battery voltage conversion
     *
     * @dataProvider batteryVoltsToConvertProvider
     *
     * @param string $orig     Original data
     * @param string $expected Expected result
     *
     * @return void
     */
    public function testConvertBatteryVoltage($orig, $expected)
    {
        if ($expected == false) {
            $this
                ->given($this->newTestedInstance())
                ->then
                    ->boolean($this->testedInstance->convertBatteryVoltage($orig))
                        ->isIdenticalTo($expected);
        } else {
            $this
                ->given($this->newTestedInstance())
                ->then
                    ->integer($this->testedInstance->convertBatteryVoltage($orig))
                    ->isIdenticalTo($expected);
        }
    }

    /**
     * Test a full conversion
     *
     * @return void
     */
    public function testConvert()
    {
        $this->string($xml_path = realpath(TU_DIR . '/data/4.xml'));
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($json_str = $this->testedInstance->convert(file_get_contents($xml_path)))
                ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('iMac-de-Marie.local-2017-06-12-09-24-14');
        $this->string($json->itemtype)->isIdenticalTo('Computer');

        $expected = [
            'capacity'     => 43746,
            'chemistry'    => 'lithium-polymer',
            'date'         => '2015-11-10',
            'manufacturer' => 'SMP',
            'name'         => 'DELL JHXPY53',
            'serial'       => '3701',
            'voltage'      => 8614

        ];
        $this->array((array)$json->content->batteries[0])->isIdenticalTo($expected);
    }

    /**
     * Test simcards and firmwares conversions
     *
     * @return void
     */
    public function testFwAndSimcards()
    {
        $this->string($xml_path = realpath(TU_DIR . '/data/5.xml'));
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($json_str = $this->testedInstance->convert(file_get_contents($xml_path)))
                ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('foo');
        $this->integer($json->jobid)->isIdenticalTo(1);
        $this->string($json->action)->isIdenticalTo('netinventory');
        $this->string($json->itemtype)->isIdenticalTo('NetworkEquipment');

        $device = $json->content->network_device;
        $this->array((array)$device)->isIdenticalTo([
            'contact' => 'test@glpi-project.org',
            'firmware' => '5.2.17.12',
            'ips' => [
                '172.21.255.102'
            ],
            'location' => 'FR-WR21',
            'mac' => '00:04:2d:07:6b:ae',
            'manufacturer' => 'Digi',
            'model' => 'WR11 XT',
            'name' => 'WR21',
            'serial' => '486280',
            'type' => 'Networking',
            'uptime' => '(12078) 0:02:00.78'
        ]);
        $this->array($json->content->network_ports)->hasSize(18);
        //$this->array($json->content->network_components)->hasSize(66);
        $this->array($json->content->firmwares)->hasSize(1);
        $this->boolean(property_exists($json->content, 'simcards'))->isFalse();

        //reload with simcards real infos
        $this->string($xml_path = realpath(TU_DIR . '/data/5-good.xml'));
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($json_str = $this->testedInstance->convert(file_get_contents($xml_path)))
                ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('foo');
        $this->integer($json->jobid)->isIdenticalTo(1);
        $this->string($json->action)->isIdenticalTo('netinventory');
        $this->string($json->itemtype)->isIdenticalTo('NetworkEquipment');

        $device = $json->content->network_device;
        $this->array((array)$device)->isIdenticalTo([
            'contact' => 'test@glpi-project.org',
            'firmware' => '5.2.17.12',
            'ips' => [
                '172.21.255.102'
            ],
            'location' => 'FR-WR21',
            'mac' => '00:04:2d:07:6b:ae',
            'manufacturer' => 'Digi',
            'model' => 'WR11 XT',
            'name' => 'WR21',
            'serial' => '486280',
            'type' => 'Networking',
            'uptime' => '(12078) 0:02:00.78'
        ]);
        $this->array($json->content->network_ports)->hasSize(18);
        //$this->array($json->content->network_components)->hasSize(66);
        $this->array($json->content->firmwares)->hasSize(1);
        $this->boolean(property_exists($json->content, 'simcards'))->isTrue();
    }

    /**
     * Test a full network equipment conversion
     *
     * @return void
     */
    public function testNetEConvert()
    {
        $this->string($xml_path = realpath(TU_DIR . '/data/6.xml'));
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($json_str = $this->testedInstance->convert(file_get_contents($xml_path)))
                ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('foo');
        $this->integer($json->jobid)->isIdenticalTo(1);
        $this->string($json->action)->isIdenticalTo('netinventory');
        $this->string($json->itemtype)->isIdenticalTo('NetworkEquipment');

        $device = $json->content->network_device;
        $this->array((array)$device)->isIdenticalTo([
            'contact' => "noc@glpi-project.org",
            'cpu' => 4,
            'firmware' => "5.0(3)N2(4.02b)",
            'location' => "paris.pa3",
            'mac' => "8c:60:4f:8d:ae:fc",
            'manufacturer' => "Cisco",
            'model' => "UCS 6248UP 48-Port",
            'name' => "ucs6248up-cluster-pa3-B",
            'serial' => "SSI1912014B",
            'type' => "Networking",
            'uptime' => "482 days, 05:42:18.50"
        ]);
        $this->array($json->content->network_ports)->hasSize(183);
        $this->array($json->content->network_components)->hasSize(66);
    }

    /**
     * Test one port equipment
     *
     * @return void
     */
    public function testOnePort()
    {
        $this->string($xml_path = realpath(TU_DIR . '/data/7.xml'));
        $xml = file_get_contents($xml_path);
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($json_str = $this->testedInstance->convert($xml))
                ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('foo');
        $this->integer($json->jobid)->isIdenticalTo(1);
        $this->string($json->action)->isIdenticalTo('netinventory');
        $this->string($json->itemtype)->isIdenticalTo('NetworkEquipment');

        $device = $json->content->network_device;
        $this->array((array)$device)->isIdenticalTo([
            'contact' => "noc@glpi-project.org",
            'cpu' => 4,
            'firmware' => "5.0(3)N2(4.02b)",
            'location' => "paris.pa3",
            'mac' => "8c:60:4f:8d:ae:fc",
            'manufacturer' => "Cisco",
            'model' => "UCS 6248UP 48-Port",
            'name' => "ucs6248up-cluster-pa3-B",
            'serial' => "SSI1912014B",
            'type' => "Networking",
            'uptime' => "482 days, 05:42:18.50"
        ]);
        $this->array($json->content->network_ports)->hasSize(1);
    }

    public function testNetdisco() {
        $this->string($xml_path = realpath(TU_DIR . '/data/9.xml'));
        $this
            ->given($this->newTestedInstance())
            ->then
            ->string($json_str = $this->testedInstance->convert(file_get_contents($xml_path)))
            ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('johanxps-2020-08-19-14-29-10');
        $this->integer($json->jobid)->isIdenticalTo(29);
        $this->string($json->action)->isIdenticalTo('netdiscovery');
        $device = $json->content->network_device;
        $this->string($device->name)->isIdenticalTo('homeassistant');
        $this->string($device->type)->isIdenticalTo('Computer');
        $this->string($json->itemtype)->isIdenticalTo('Computer');

        //example from old specs documentation
        $this->string($xml_path = realpath(TU_DIR . '/data/10.xml'));
        $this
            ->given($this->newTestedInstance())
            ->then
            ->string($json_str = $this->testedInstance->convert(file_get_contents($xml_path)))
            ->isNotEmpty();

        $this->object($json = json_decode($json_str));
        $this->string($json->deviceid)->isIdenticalTo('qlf-sesi-inventory.glpi-project.org-2013-11-14-17-47-17');
        $this->integer($json->jobid)->isIdenticalTo(1);
        $this->string($json->action)->isIdenticalTo('netdiscovery');
        $device = $json->content->network_device;
        $this->string($device->name)->isIdenticalTo('swdc-07-01-dc1');
        $this->string($json->itemtype)->isIdenticalTo('NetworkEquipment');

        $device = $json->content->network_device;
        $this->array((array)$device)->isEqualTo([
            'type' => 'Networking',
            'contact' => 'anyone@glpi-project.org',
            'description' => 'Cisco NX-OS(tm) n5000, Software (n5000-uk9), Version 5.2(1)N1(5), RELEASE SOFTWARE Copyright (c) 2002-2011 by Cisco Systems, Inc. Device Manager Version 6.1(1),  Compiled 6/27/2013 16:00:00',
            'firmware' => 'CW_VERSION$5.2(1)N1(5)$',
            'ips' => [
                '192.168.0.8',
            ],
            'location' => 'dc1 salle 07',
            'mac' => '00:23:04:ee:be:02',
            'manufacturer' => 'Cisco',
            'model' => 'Cisco Nexus 5596',
            'name' => 'swdc-07-01-dc1',
            'uptime' => '175 days, 11:33:37.48',
            'credentials' => 1
        ]);
    }

    public function testValidate() {
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $this
            ->given($this->newTestedInstance())
            ->then
            ->boolean($this->testedInstance->validate($json))->isTrue();

        //required "versionclient" is missing
        $this->exception(
            function () {
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['hardware' => ['name' => 'my inventory']]]));
                $this
                    ->given($this->newTestedInstance())
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isFalse();
            }
        )
            ->isInstanceOf('\RuntimeException')
            ->message->contains('Required property missing: versionclient');

        //extra "plugin_node" is unknown
        $this->exception(
            function () {
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'plugin_node' => 'plugin node']]));
                $this
                    ->given($this->newTestedInstance())
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isFalse();
            }
        )
            ->isInstanceOf('\RuntimeException')
            ->message->contains('Additional properties not allowed: plugin_node');

        //add extra "plugin_node" as string
        $extra_prop = ['plugin_node' => ['type' => 'string']];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'plugin_node' => 'plugin node']]));
        $this
            ->given($this->newTestedInstance())
            ->if($this->testedInstance->setExtraProperties($extra_prop))
            ->then
            ->boolean($this->testedInstance->validate($json))->isTrue();

        //extra "hardware/hw_plugin_node" is unknown
        $this->exception(
            function () {
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['hw_plugin_node' => 'plugin node']]]));
                $this
                    ->given($this->newTestedInstance())
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isFalse();
            }
        )
            ->isInstanceOf('\RuntimeException')
            ->message->contains('Additional properties not allowed: hw_plugin_node');

        //add extra "hardware/hw_plugin_node" as string
        $extra_sub_prop = ['hardware' => ['hw_plugin_node' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['hw_plugin_node' => 'plugin node']]]));
        $this
            ->given($this->newTestedInstance())
            ->if($this->testedInstance->setExtraSubProperties($extra_sub_prop))
            ->then
            ->boolean($this->testedInstance->validate($json))->isTrue();

        //extra "virtualmachines/vm_plugin_node" is unknown
        $this->exception(
            function () {
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'virtualmachines' => [['name' => 'My VM', 'vmtype' => 'libvirt', 'vm_plugin_node' => 'plugin node']]]]));
                $this
                    ->given($this->newTestedInstance())
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isFalse();
            }
        )
            ->isInstanceOf('\RuntimeException')
            ->message->contains('Additional properties not allowed: vm_plugin_node');

        //add extra "virtualmachines/vm_plugin_node" as string
        $extra_sub_prop = ['virtualmachines' => ['vm_plugin_node' => ['type' => 'string']]];
        $json = json_decode(json_encode([
            'deviceid' => 'myid',
            'content' => [
                'versionclient' => 'GLPI-Agent_v1.0',
                'virtualmachines' => [
                    [
                        'name' => 'My VM',
                        'vmtype' => 'libvirt',
                        'vm_plugin_node' => 'plugin node'
                    ]
                ]
            ]
        ]));
        $this
            ->given($this->newTestedInstance())
            ->if($this->testedInstance->setExtraSubProperties($extra_sub_prop))
            ->then
            ->boolean($this->testedInstance->validate($json))->isTrue();

        //try add extra node already existing
        $this->when(
            function () {
                $extra_prop = ['accesslog' => ['type' => 'string']];
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
                $this
                    ->given($this->newTestedInstance())
                    ->if($this->testedInstance->setExtraProperties($extra_prop))
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isTrue();
            }
        )
            ->error()
            ->withType(E_USER_WARNING)
            ->withMessage('Property accesslog already exists in schema.')
            ->exists();

        //try add extra sub node already existing
        $this->when(
            function () {
                $extra_sub_prop = ['hardware' => ['chassis_type' => ['type' => 'string']]];
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
                $this
                    ->given($this->newTestedInstance())
                    ->if($this->testedInstance->setExtraSubProperties($extra_sub_prop))
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isTrue();
            }
        )
            ->error()
            ->withType(E_USER_WARNING)
            ->withMessage('Property hardware/chassis_type already exists in schema.')
            ->exists();

        //try add extra sub node with missing parent
        $this->when(
            function () {
                $extra_sub_prop = ['unknown' => ['chassis_type' => ['type' => 'string']]];
                $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
                $this
                    ->given($this->newTestedInstance())
                    ->if($this->testedInstance->setExtraSubProperties($extra_sub_prop))
                    ->then
                    ->boolean($this->testedInstance->validate($json))->isTrue();
            }
        )
            ->error()
            ->withType(E_USER_WARNING)
            ->withMessage('Property unknown does not exists in schema.')
            ->exists();
    }
}
