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
            ['2018-01-12', 'Y-m-d', null],
            ['01/12/2018', 'Y-m-d', '2018-12-01'],
            ['01/15/2018', 'Y-m-d', '2019-03-01'],
            ['', 'Y-m-d', '']
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
        if ($expected === null) {
            $expected = $orig;
        }
        $this
            ->given($this->newTestedInstance())
            ->then
                ->string($this->testedInstance->convertDate($orig, $format))
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
            ['43 Wh', 43000]
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
                    ->boolean($this->testedInstance->convertBatteryCapacity($orig))
                        ->isIdenticalTo($expected);
        } else {
            $this
                ->given($this->newTestedInstance())
                ->then
                    ->integer($this->testedInstance->convertBatteryCapacity($orig))
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
            ['8,2 V', false]
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
}
