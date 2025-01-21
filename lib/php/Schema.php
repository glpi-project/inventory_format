<?php

/**
 * © Teclib' and contributors.
 *
 * This file is part of GLPI inventory format.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glpi\Inventory;

use Exception;
use RuntimeException;
use Swaggest\JsonSchema\Context;

/**
 * Handle inventory JSON Schema
 *
 * @author Johan Cwiklinski <jcwiklinski@teclib.com>
 */
class Schema
{
    public const LAST_VERSION = 0.1;

    /** @var array<string, array<int, string>> */
    private array $patterns;
    /** @var array<string, array<string, string>> */
    private array $extra_properties = [];
    /** @var array<string, array<string, array<string, string>>> */
    private array $extra_sub_properties = [];
    /** @var array<string> */
    private array $extra_itemtypes = [];
    /** @var bool */
    private bool $strict_schema = true;

    /**
     * Add new supported item types
     *
     * @param array<string> $itemtypes
     * @return $this
     */
    public function setExtraItemtypes(array $itemtypes): self
    {
        $this->extra_itemtypes = $itemtypes;
        return $this;
    }

    /**
     * Build (extended) JSON schema
     *
     * @return object
     */
    public function build(): object
    {
        $string = file_get_contents($this->getPath());
        if ($string === false) {
            throw new RuntimeException('Unable to read schema file');
        }
        $schema = json_decode($string);

        $known_itemtypes = [];
        preg_match('/\^\((.+)\)\$/', $schema->properties->itemtype->pattern, $known_itemtypes);
        if (isset($known_itemtypes[1])) {
            $known_itemtypes = explode('|', $known_itemtypes[1]);
            foreach ($this->extra_itemtypes as $extra_itemtype) {
                if (!in_array($extra_itemtype, $known_itemtypes)) {
                    $known_itemtypes[] = addslashes($extra_itemtype);
                }
            }
            $schema->properties->itemtype->pattern = sprintf(
                '^(%s)$',
                implode('|', $known_itemtypes)
            );
        }

        $properties = $schema->properties->content->properties;

        foreach ($this->extra_properties as $extra_property => $extra_config) {
            if (!property_exists($properties, $extra_property)) {
                $properties->$extra_property = json_decode((string)json_encode($extra_config));
            } else {
                trigger_error(
                    sprintf('Property %1$s already exists in schema.', $extra_property),
                    E_USER_WARNING
                );
            }
        }

        foreach ($this->extra_sub_properties as $extra_sub_property => $extra_sub_config) {
            if (property_exists($properties, $extra_sub_property)) {
                foreach ($extra_sub_config as $subprop => $subconfig) {
                    $type = $properties->$extra_sub_property->type;
                    switch ($type) {
                        case 'array':
                            if (!property_exists($properties->$extra_sub_property->items->properties, $subprop)) {
                                $properties->$extra_sub_property->items->properties->$subprop =
                                    json_decode((string)json_encode($subconfig));
                            } else {
                                trigger_error(
                                    sprintf('Property %1$s already exists in schema.', $subprop),
                                    E_USER_WARNING
                                );
                            }
                            break;
                        case 'object':
                            if (!property_exists($properties->$extra_sub_property->properties, $subprop)) {
                                $properties->$extra_sub_property->properties->$subprop =
                                    json_decode((string)json_encode($subconfig));
                            } else {
                                trigger_error(
                                    sprintf(
                                        'Property %1$s/%2$s already exists in schema.',
                                        $extra_sub_property,
                                        $subprop
                                    ),
                                    E_USER_WARNING
                                );
                            }
                            break;
                        default:
                            trigger_error('Unknown type ' . $type, E_USER_WARNING);
                    }
                }
            } else {
                trigger_error(
                    sprintf('Property %1$s does not exists in schema.', $extra_sub_property),
                    E_USER_WARNING
                );
            }
        }

        if ($this->strict_schema === false) {
            $this->buildFlexibleSchema($schema->properties->content);
        }

        return $schema;
    }

    /**
     * Get path to schema
     *
     * @return string
     */
    public function getPath(): string
    {
        $schema_path = realpath(__DIR__ . '/../../inventory.schema.json');
        if ($schema_path === false) {
            throw new RuntimeException('Schema file not found!');
        }
        return $schema_path;
    }

    /**
     * Add extra properties to schema
     *
     * @param array<string, array<string, string>> $properties
     * @return $this
     */
    public function setExtraProperties(array $properties): self
    {
        $this->extra_properties = $properties;
        return $this;
    }

    /**
     * Add extra sub-properties to schema
     *
     * @param array<string, array<string, array<string, string>>> $properties
     * @return $this
     */
    public function setExtraSubProperties(array $properties): self
    {
        $this->extra_sub_properties = $properties;
        return $this;
    }

    /**
     * Load schema patterns that will be used to validate
     *
     * @return void
     */
    public function loadPatterns(): void
    {
        $string = file_get_contents($this->getPath());
        if ($string === false) {
            throw new RuntimeException('Unable to read schema file');
        }
        $json = json_decode($string, true);

        $this->patterns['networks_types'] = explode(
            '|',
            str_replace(
                ['^(', ')$'],
                ['', ''],
                $json['properties']['content']['properties']['networks']['items']['properties']['type']['pattern']
            )
        );
    }

    /**
     * Get available schema patterns
     *
     * @return array<string, array<int, string>>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Set schema validation strict (no additional properties allowed anywhere)
     *
     * @return self
     */
    public function setStrict(): self
    {
        $this->strict_schema = true;
        return $this;
    }

    /**
     * Set schema validation strict (no additional properties allowed anywhere)
     *
     * @return self
     */
    public function setFlexible(): self
    {
        $this->strict_schema = false;
        return $this;
    }

    /**
     * Build schema flexible (remove all additionalProperties)
     *
     * @param mixed $schemapart
     *
     * @return void
     */
    private function buildFlexibleSchema(&$schemapart)
    {
        foreach ($schemapart as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $this->buildFlexibleSchema($value);
            } else {
                if ($key == 'additionalProperties') {
                    unset($schemapart->$key);
                }
            }
        }
    }

    /**
     * Do validation (against last schema only!)
     *
     * @param mixed $json Converted data to validate
     *
     * @return boolean
     */
    public function validate($json): bool
    {
        try {
            $schema = \Swaggest\JsonSchema\Schema::import($this->build());

            $context = new Context();
            $context->tolerateStrings = (!defined('TU_USER'));
            $schema->in($json, $context);
            return true;
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf(
                    "JSON does not validate. Violations:\n%1\$s\n",
                    $e->getMessage()
                )
            );
        }
    }
}
