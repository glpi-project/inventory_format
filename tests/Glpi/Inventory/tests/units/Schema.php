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

class Schema extends TestCase
{
    /**
     * Test schema path
     *
     * @return void
     */
    public function testSchemaPath(): void
    {
        $expected = realpath(TU_DIR . '/../inventory.schema.json');
        $instance = new \Glpi\Inventory\Schema();
        $this->assertSame($expected, $instance->getPath());
    }

    public function testValidateOK(): void
    {
        $json = json_decode(json_encode(['deviceid' => 'myid', 'tag' => 'mytag', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateVersionClient(): void
    {
        //required "versionclient" is missing
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Schema();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Required property missing: versionclient');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateUnknownItemtype(): void
    {
        //itemtype \Glpi\Custom\Asset\Mine is unknown
        $itemtype = '\Glpi\Custom\Asset\Mine';
        $json = json_decode(json_encode(['deviceid' => 'myid', 'itemtype' => $itemtype, 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Schema();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('\\\\Glpi\\\\Custom\\\\Asset\\\\Mine" does not match to ^(Unmanaged|Computer|Phone|NetworkEquipment|Printer)$');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateNewItemtype(): void
    {
        //itemtype \Glpi\Custom\Asset\Mine is unknown
        $itemtype = '\Glpi\Custom\Asset\Mine';
        $json = json_decode(json_encode(['deviceid' => 'myid', 'itemtype' => $itemtype, 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['name' => 'my inventory']]]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraItemtypes([$itemtype]));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateExtraPlugin_node(): void
    {
        //add extra "plugin_node" as string
        $extra_prop = ['plugin_node' => ['type' => 'string']];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'plugin_node' => 'plugin node']]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraProperties($extra_prop));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateHwPlugin_node(): void
    {
        //add extra "hardware/hw_plugin_node" as string
        $extra_sub_prop = ['hardware' => ['hw_plugin_node' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'hardware' => ['hw_plugin_node' => 'plugin node']]]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->assertTrue($instance->validate($json));
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
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->assertTrue($instance->validate($json));
    }

    public function testValidateAlreadyExistingExtraNode(): void
    {
        //try add extra node already existing
        $extra_prop = ['accesslog' => ['type' => 'string']];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraProperties($extra_prop));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property accesslog already exists in schema.');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateUAlreadyExistingExtraSubNode(): void
    {
        //try add extra sub node already existing
        $extra_sub_prop = ['hardware' => ['chassis_type' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property hardware/chassis_type already exists in schema.');
        $this->assertFalse($instance->validate($json));
    }

    public function testValidateMissingParentSubNode(): void
    {
        //try add extra sub node with missing parent
        $extra_sub_prop = ['unknown' => ['chassis_type' => ['type' => 'string']]];
        $json = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0']]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertInstanceOf(\Glpi\Inventory\Schema::class, $instance->setExtraSubProperties($extra_sub_prop));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property unknown does not exists in schema.');
        $this->assertFalse($instance->validate($json));
    }

    public function testFlexibleSchema(): void
    {
        $json_additionnal = json_decode(json_encode(['deviceid' => 'myid', 'content' => ['versionclient' => 'GLPI-Agent_v1.0', 'additional' => ['name' => 'my extra data']]]));
        $instance = new \Glpi\Inventory\Schema();
        $this->assertTrue($instance->validate($json_additionnal));
    }

    public function testVersion(): void
    {
        $instance = new \Glpi\Inventory\Schema();
        $this->assertIsFloat($instance->getVersion());
    }

    public function testNoTagOnNetdiscoveryRequests(): void
    {
        $json = json_decode(json_encode([
            'content' => [
                'hardware' => [
                    'workgroup' => 'WORKGROUP',
                ],
                'versionclient' => '5.1',
                'network_device' => [
                    'type' => 'Unmanaged',
                    'mac' => '4c:cc:6a:02:13:a9',
                    'name' => 'DESKTOP-A3J16LF',
                    'ips' => ['192.168.1.20'],
                ],
            ],
            'deviceid' => 'asus-desktop-2022-09-20-16-43-09',
            'action' => 'netdiscovery',
            'jobid' => 189,
            'itemtype' => 'Unmanaged',
            'tag' => 'tag_is_not_allowed_in_netdiscovery',
        ]));
        $instance = new \Glpi\Inventory\Schema();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property "tag" is not allowed when action is "netdiscovery"');
        $instance->validate($json);
    }
}
