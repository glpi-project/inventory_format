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

class Converter extends TestCase
{
    /**
     * Test constructor
     *
     * @return void
     */
    public function testConstructor(): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($instance::LAST_VERSION, $instance->getTargetVersion());

        $ver = 156.2;
        $instance = new \Glpi\Inventory\Converter($ver);
        $this->assertSame($ver, $instance->getTargetVersion());

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Version must be a double!');
        new \Glpi\Inventory\Converter('abcde'); //@phpstan-ignore-line
    }

    /**
     * Test debug mode activation
     *
     * @return void
     */
    public function testDebug(): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertFalse($instance->isDebug());

        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setDebug(true));
        $this->assertTrue($instance->isDebug());
    }

    /**
     * Test schema path
     *
     * @return void
     */
    public function testSchemaPath(): void
    {
        $expected = realpath(TU_DIR . '/../inventory.schema.json');
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->getSchemaPath());
    }

    /**
     * Test conversion methods list
     *
     * @return void
     */
    public function testGetMethods(): void
    {
        $expected = ['convertTo01'];
        $instance = new \Glpi\Inventory\Converter(0.1);
        $this->assertSame($expected, $instance->getMethods());
    }

    /**
     * Values to cast provider
     *
     * @return array<int, array<string|int|float|bool|null>>
     */
    public static function valuesToCastProvider(): array
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
            ['abcde', 'integer', null],
            ['8c:60:4f:8d:ae:a1', 'integer', null],
            [42.42, 'integer', null],
        ];
    }

    /**
     * Test cast value
     *
     * @dataProvider valuesToCastProvider
     *
     * @param mixed  $value    Value to cast
     * @param string $cast     Type to cast to
     * @param mixed  $expected Expected cast value
     *
     * @return void
     */
    public function testGetCastedValue($value, $cast, $expected): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->getCastedValue($value, $cast));
    }

    /**
     * Test cast value exception
     *
     * @return void
     */
    public function testGetCastedValueWE(): void
    {
        $instance = new \Glpi\Inventory\Converter();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Type blah not known.');
        $instance->getCastedValue(0, 'blah');
    }

    /**
     * Array case change provider
     *
     * @return array<int, array<int, array<string, array<string, int>|int>|null>>
     */
    public static function arrayForCaseProvider(): array
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
     * @param array  $orig     Original array
     * @param ?array $expected Expected result
     *
     * @return void
     */
    public function testArrayChangeKeyCaseRecursive(array $orig, ?array $expected): void
    {
        if ($expected === null) {
            $expected = $orig;
        }

        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->arrayChangeKeyCaseRecursive($orig));
    }

    /**
     * Date to convert provider
     *
     * @return array<int, array<int, ?string>>
     */
    public static function datesToConvertProvider(): array
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
     * @param string  $orig     Original date
     * @param string  $format   Format to apply
     * @param ?string $expected Expected formatted date
     *
     * @return void
     */
    public function testConvertDate(string $orig, string $format, ?string $expected): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->convertDate($orig, $format));
    }

    public function testConvertTypes(): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $instance->setConvertTypes([
            'boolean'  => [
                'cpus/enabled'
            ],
            'integer'   => [
                'one/two',
                'one/three'
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
                    'two'  => '42',
                    'three' => '8c:60:4f:8d:ae:a1'
                ]
            ]
        ];

        $instance->convertTypes($data);
        $this->assertSame(
            [
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
                        'two'  => 42,
                        'three' => null
                    ]
                ]
            ],
            $data
        );
    }

    /**
     * Batteries capacities convert provider
     *
     * @return array<int, array<string|int|bool>>
     */
    public static function batteryCapasToConvertProvider(): array
    {
        return [
            ['43.7456 Wh', 43746],
            ['512584', 512584],
            ['43746 mWh', 43746],
            ['43.746 mWh', false],
            ['43 7456 Wh', false],
            ['43,7456 Wh', false],
            ['43 Wh', 43000],
            ['2100.0', 2100],
            [42, 42]
        ];
    }

    /**
     * Test battery capacity conversion
     *
     * @dataProvider batteryCapasToConvertProvider
     *
     * @param string|int $orig     Original data
     * @param int|false $expected Expected result
     *
     * @return void
     */
    public function testConvertBatteryCapacity($orig, $expected): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->convertBatteryPower($orig));
    }

    /**
     * Batteries voltages convert provider
     *
     * @return array<int, array<string|int|bool>>
     */
    public static function batteryVoltsToConvertProvider(): array
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
     * @param string    $orig     Original data
     * @param int|false $expected Expected result
     *
     * @return void
     */
    public function testConvertBatteryVoltage(string $orig, $expected): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->convertBatteryVoltage($orig));
    }

    /**
     * Test a full conversion
     *
     * @return void
     */
    public function testConvert(): void
    {
        $xml_path = realpath(TU_DIR . '/data/4.xml');
        $this->assertNotEmpty($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('iMac-de-Marie.local-2017-06-12-09-24-14', $json->deviceid);
        $this->assertSame('Computer', $json->itemtype);

        $expected = [
            'capacity'     => 43746,
            'chemistry'    => 'lithium-polymer',
            'date'         => '2015-11-10',
            'manufacturer' => 'SMP',
            'name'         => 'DELL JHXPY53',
            'serial'       => '3701',
            'voltage'      => 8614

        ];
        $this->assertSame($expected, (array)$json->content->batteries[0]);
    }

    /**
     * Test a full conversion and check boot time
     *
     * @return void
     */
    public function testFrBootTimeDateConvert(): void
    {
        $xml_path = realpath(TU_DIR . '/data/13.xml');
        $this->assertNotEmpty($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('android-5a30d8711bbadc9d-2022-10-19-08-08-46', $json->deviceid);
        $this->assertSame('Computer', $json->itemtype);

        $expected = "2022-09-21 05:21:23";
        $this->assertSame($expected, $json->content->operatingsystem->boot_time);
    }

    /**
     * Test a full conversion and check simcard
     *
     * @return void
     */
    public function testSimCard(): void
    {
        $xml_path = realpath(TU_DIR . '/data/18.xml');
        $this->assertNotEmpty($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('android-5a30d8711bbadc9d-2023-09-06-10-28-47', $json->deviceid);
        $this->assertSame('Computer', $json->itemtype);


        $expected = [
            'country'           => 'fr',
            'operator_code'     => '20810',
            'operator_name'     => 'F SFR',
            'state'             => 'SIM_STATE_READY',
            'subscriber_id'     => '1'

        ];
        $this->assertSame($expected, (array)$json->content->simcards[0]);

        $expected = [
            'country'           => 'fr',
            'operator_code'     => '20810',
            'operator_name'     => 'F SFR',
            'state'             => 'SIM_STATE_READY',
            'subscriber_id'     => '2'

        ];
        $this->assertSame($expected, (array)$json->content->simcards[1]);
    }

    /**
     * Test a full conversion and check boot time
     *
     * @return void
     */
    public function testEnBootTimeDateConvert(): void
    {
        $xml_path = realpath(TU_DIR . '/data/14.xml');
        $this->assertNotEmpty($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('android-5a30d8711bbadc9d-2022-10-19-08-08-46', $json->deviceid);
        $this->assertSame('Computer', $json->itemtype);

        $expected = "2022-10-04 05:21:23";
        $this->assertSame($expected, $json->content->operatingsystem->boot_time);
    }

    /**
     * Test simcards and firmwares conversions
     *
     * @return void
     */
    public function testFwAndSimcards(): void
    {
        $xml_path = realpath(TU_DIR . '/data/5.xml');
        $this->assertNotEmpty($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('foo', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netinventory', $json->action);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
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
                'uptime' => '(12078) 0:02:00.78',
                'description' => "
Digi TransPort WR11-L700-DE1-XW Ser#:486280
Software Build Ver5.2.17.12.  Mar  8 2017 13:55:20  1W
ARM Bios Ver 7.59u v46 454MHz B987-M995-F80-O0,0 MAC:00042d076b88"
            ],
            (array)$device
        );
        $this->assertCount(18, $json->content->network_ports);
        $this->assertCount(1, $json->content->firmwares);
        $this->assertFalse(property_exists($json->content, 'simcards'));

        //reload with simcards real infos
        $xml_path = realpath(TU_DIR . '/data/5-good.xml');
        $this->assertNotEmpty($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('foo', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netinventory', $json->action);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
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
                'uptime' => '(12078) 0:02:00.78',
                'description' => "
Digi TransPort WR11-L700-DE1-XW Ser#:486280
Software Build Ver5.2.17.12.  Mar  8 2017 13:55:20  1W
ARM Bios Ver 7.59u v46 454MHz B987-M995-F80-O0,0 MAC:00042d076b88"
            ],
            (array)$device
        );
        $this->assertCount(18, $json->content->network_ports);
        $this->assertCount(1, $json->content->firmwares);
        $this->assertTrue(property_exists($json->content, 'simcards'));
    }

    /**
     * Test a full network equipment conversion
     *
     * @return void
     */
    public function testNetEConvert(): void
    {
        $xml_path = realpath(TU_DIR . '/data/6.xml');
        $this->assertIsString($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('foo', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netinventory', $json->action);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
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
                'uptime' => "482 days, 05:42:18.50",
                'description' => "Cisco NX-OS(tm) ucs, Software (ucs-6100-k9-system), Version 5.0(3)N2(4.02b), RELEASE SOFTWARE Copyright (c) 2002-2013 by Cisco Systems, Inc.   Compiled 1/16/2019 18:00:00"
            ],
            (array)$device
        );
        $this->assertCount(183, $json->content->network_ports);
        $this->assertCount(66, $json->content->network_components);
    }

    /**
     * Test one port equipment
     *
     * @return void
     */
    public function testOnePort(): void
    {
        $xml_path = realpath(TU_DIR . '/data/7.xml');
        $this->assertIsString($xml_path);

        $xml = file_get_contents($xml_path);
        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert($xml);
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('foo', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netinventory', $json->action);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
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
                'uptime' => "482 days, 05:42:18.50",
                'description' => "Cisco NX-OS(tm) ucs, Software (ucs-6100-k9-system), Version 5.0(3)N2(4.02b), RELEASE SOFTWARE Copyright (c) 2002-2013 by Cisco Systems, Inc.   Compiled 1/16/2019 18:00:00"
            ],
            (array)$device
        );
        $this->assertCount(1, $json->content->network_ports);
    }

    public function testNetdisco(): void
    {
        $xml_path = realpath(TU_DIR . '/data/9.xml');
        $this->assertIsString($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('johanxps-2020-08-19-14-29-10', $json->deviceid);
        $this->assertSame(29, $json->jobid);
        $this->assertSame('netdiscovery', $json->action);
        $device = $json->content->network_device;
        $this->assertSame('homeassistant', $device->name);
        $this->assertSame('Unmanaged', $device->type);
        $this->assertSame('Unmanaged', $json->itemtype);

        //example from old specs documentation
        $xml_path = realpath(TU_DIR . '/data/10.xml');
        $this->assertIsString($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert(file_get_contents($xml_path));
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('qlf-sesi-inventory.glpi-project.org-2013-11-14-17-47-17', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netdiscovery', $json->action);
        $device = $json->content->network_device;
        $this->assertSame('swdc-07-01-dc1', $device->name);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
                'type' => 'Networking',
                'credentials' => 1,
                'contact' => 'anyone@glpi-project.org',
                'description' => 'Cisco NX-OS(tm) n5000, Software (n5000-uk9), Version 5.2(1)N1(5), RELEASE SOFTWARE Copyright (c) 2002-2011 by Cisco Systems, Inc. Device Manager Version 6.1(1),  Compiled 6/27/2013 16:00:00',
                'firmware' => 'CW_VERSION$5.2(1)N1(5)$',
                'location' => 'dc1 salle 07',
                'mac' => '00:23:04:ee:be:02',
                'manufacturer' => 'Cisco',
                'model' => 'Cisco Nexus 5596',
                'name' => 'swdc-07-01-dc1',
                'uptime' => '175 days, 11:33:37.48',
                'ips' => [
                    '192.168.0.8',
                ]
            ],
            (array)$device
        );
    }

    public function testValidateOK(): void
    {
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateVersionClient(): void
    {
        //required "versionclient" is missing
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Required property missing: versionclient');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateUnknownItemtype(): void
    {
        //itemtype \Glpi\Custom\Asset\Mine is unknown
        $itemtype = '\Glpi\Custom\Asset\Mine';
        $json = json_decode(json_encode(['deviceid' => 'myid', 'itemtype' => $itemtype, 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('\\\\Glpi\\\\Custom\\\\Asset\\\\Mine" does not match to ^(Unmanaged|Computer|Phone|NetworkEquipment|Printer)$');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateNewItemtype(): void
    {
        //itemtype \Glpi\Custom\Asset\Mine is unknown
        $itemtype = '\Glpi\Custom\Asset\Mine';
        $json = json_decode(json_encode(['deviceid' => 'myid', 'itemtype' => $itemtype, 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraItemtypes([$itemtype]));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateUnknownExtraPlugin_node(): void
    {
        //extra "plugin_node" is unknown
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'plugin_node' => 'plugin node']]));
        $instance = new \Glpi\Inventory\Converter();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Additional properties not allowed: plugin_node');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateExtraPlugin_node(): void
    {
        //add extra "plugin_node" as string
        $extra_prop = ['plugin_node' => ['type' => 'string']];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'plugin_node' => 'plugin node']]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraProperties($extra_prop));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateUnknownHwPlugin_node(): void
    {
        //extra "hardware/hw_plugin_node" is unknown
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['hw_plugin_node' => 'plugin node']]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Additional properties not allowed: hw_plugin_node');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateHwPlugin_node(): void
    {
        //add extra "hardware/hw_plugin_node" as string
        $extra_sub_prop = ['hardware' => ['hw_plugin_node' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['hw_plugin_node' => 'plugin node']]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateUnknownVmPlugin_node(): void
    {
        //extra "virtualmachines/vm_plugin_node" is unknown
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'virtualmachines' => [['name' => 'My VM', 'vmtype' => 'libvirt', 'vm_plugin_node' => 'plugin node']]]]));
        $instance = new \Glpi\Inventory\Converter();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Additional properties not allowed: vm_plugin_node');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateVmPlugin_node(): void
    {
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
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateAlreadyExistingExtraNode(): void
    {
        //try add extra node already existing
        $extra_prop = ['accesslog' => ['type' => 'string']];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraProperties($extra_prop));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property accesslog already exists in schema.');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateUAlreadyExistingExtraSubNode(): void
    {
        //try add extra sub node already existing
        $extra_sub_prop = ['hardware' => ['chassis_type' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property hardware/chassis_type already exists in schema.');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateMissingParentSubNode(): void
    {
        //try add extra sub node with missing parent
        $extra_sub_prop = ['unknown' => ['chassis_type' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
        $instance = new \Glpi\Inventory\Converter();
        $this->assertInstanceOf(\Glpi\Inventory\Converter::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property unknown does not exists in schema.');
        $this->assertFalse($instance->validate($json));
    }

    public function testAssetTagFromDiscovery(): void
    {
        $xml_path = realpath(TU_DIR . '/data/16.xml');
        $this->assertNotEmpty($xml_path);
        $xml = file_get_contents($xml_path);

        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert($xml);
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('foo', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netdiscovery', $json->action);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
                "type" => "Networking" ,
                "credentials" => 1,
                "contact" => "Teclib",
                "description" => "HP 1810-24G, PL.2.10, eCos-3.0, 1_12_8-customized-h",
                "firmware" => "1_12_8-customized-h",
                "ips" => [
                    "192.168.1.9"
                ],
                "location" => "Agence Teclib",
                "mac" => "d5:c9:gh:81:cf:80",
                "manufacturer" => "Hewlett-Packard",
                "model" => "1810-24G",
                "serial" => "cn3afrt37n",
                "name" => "J9803A",
                "assettag" => "assettag",
                "uptime" => "78 days, 14:36:51.25",
            ],
            (array)$device
        );
    }

    public function testAssetTagFromInventory(): void
    {
        $xml_path = realpath(TU_DIR . '/data/17.xml');
        $this->assertNotEmpty($xml_path);

        $xml = file_get_contents($xml_path);
        $instance = new \Glpi\Inventory\Converter();
        $json_str = $instance->convert($xml);
        $this->assertNotEmpty($json_str);

        $json = json_decode($json_str);
        $this->assertIsObject($json);
        $this->assertSame('foo', $json->deviceid);
        $this->assertSame(1, $json->jobid);
        $this->assertSame('netinventory', $json->action);
        $this->assertSame('NetworkEquipment', $json->itemtype);

        $device = $json->content->network_device;
        $this->assertSame(
            [
                "contact" => "Teclib",
                "firmware" => "1_12_8-customized-h",
                "ips" => [
                    "192.168.1.9"
                ],
                "location" => "Agence Teclib",
                "mac" => "d5:c9:gh:81:cf:80",
                "manufacturer" => "Hewlett-Packard",
                "assettag" => "asset",
                "model" => "1810-24G",
                "name" => "J9803A",
                "serial" => "cn3afrt37n",
                "type" => "Networking" ,
                "uptime" => "78 days, 14:56:50.69",
                "description" => "HP 1810-24G, PL.2.10, eCos-3.0, 1_12_8-customized-h",
            ],
            (array)$device
        );
        $this->assertCount(1, $json->content->network_ports);
    }

    /**
     * Memory capacities convert provider
     *
     * @return array<int, array<int, string|int>>
     */
    public static function memoryCapasToConvertProvider(): array
    {
        return [
            ['2048', 2048],
            ['2048Mb', 2048],
            ['2048MB', 2048],
            ['2048 Mb', 2048],
            ['2097152Kb', 2048],
            ['2147483648b', 2048],
            ['2.0Gb', 2048],
            ['5Tb', 5242880],
            ['3Pb', 3221225472]
        ];
    }

    /**
     * Test memory capacity conversion
     *
     * @dataProvider memoryCapasToConvertProvider
     *
     * @param string $orig     Original data
     * @param int    $expected Expected result
     *
     * @return void
     */
    public function testConvertMemoryCapacity(string $orig, int $expected): void
    {
        $instance = new \Glpi\Inventory\Converter();
        $this->assertSame($expected, $instance->convertMemory($orig));
    }

    public function testFlexibleSchema(): void
    {
        $json_additionnal = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'additional' => ['name' => 'my extra data']]]));
        $instance = new \Glpi\Inventory\Converter();
        $instance->setFlexibleSchema();
        $this->assertTrue($instance->validate($json_additionnal));

        //tests same JSON fails with strict schema
        $instance->setStrictSchema();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Additional properties not allowed: additional');
        $this->assertTrue($instance->validate($json_additionnal));
    }
}
