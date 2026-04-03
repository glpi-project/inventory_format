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
    /** @var array<string, array<int, string>> */
    private array $patterns;
    /** @var array<string, array<string, string>> */
    private array $extra_properties = [];
    /** @var array<string, array<string, array<string, string>>> */
    private array $extra_sub_properties = [];
    /** @var array<string> */
    private array $extra_itemtypes = [];

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
     * Do validation (against last schema only!)
     *
     * @param mixed $json Converted data to validate
     *
     * @return boolean
     */
    public function validate($json): bool
    {
        $rawSchema = null;
        try {
            $rawSchema = $this->build();
            $schema = \Swaggest\JsonSchema\Schema::import($rawSchema);

            $context = new Context();
            $context->tolerateStrings = (!defined('TU_USER'));
            $schema->in($json, $context);
            return true;
        } catch (\Swaggest\JsonSchema\InvalidValue $e) {
            throw new RuntimeException(
                sprintf(
                    "JSON does not validate. Violations:\n%1\$s\n",
                    $rawSchema !== null ? $this->improveErrorMessage($e, $rawSchema) : $e->getMessage()
                )
            );
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf(
                    "JSON does not validate. Violations:\n%1\$s\n",
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Translate a swaggest InvalidValue exception into a human-readable message.
     * Handles the case where a property is forbidden by an if/then conditional rule.
     *
     * @param \Swaggest\JsonSchema\InvalidValue $e
     * @param object $rawSchema The raw (decoded) schema object
     * @return string
     */
    private function improveErrorMessage(\Swaggest\JsonSchema\InvalidValue $e, object $rawSchema): string
    {
        $path = $e->path ?? '';
        // Pattern: "->then->properties:PROP->not" means PROP is forbidden by a conditional rule
        if (preg_match('/->then->properties:(\w+)->not$/', $path, $matches)) {
            $property = $matches[1];
            if (isset($rawSchema->{'if'}->properties->action->const)) {
                return sprintf(
                    'Property "%s" is not allowed when action is "%s"',
                    $property,
                    $rawSchema->{'if'}->properties->action->const
                );
            }
            return sprintf('Property "%s" is not allowed in this context', $property);
        }
        return $e->getMessage();
    }

    /**
     * Get current schema version
     *
     * @return float
     */
    public function getVersion(): float
    {
        return $this->build()->version; //@phpstan-ignore-line: version does exist.
    }
}
