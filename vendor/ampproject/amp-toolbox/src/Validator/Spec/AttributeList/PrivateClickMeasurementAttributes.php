<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;

/**
 * Attribute list class PrivateClickMeasurementAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $attributiondestination
 * @property-read array $attributionexpiry
 * @property-read array $attributionreportto
 * @property-read array $attributionsourceeventid
 * @property-read array $attributionsourceid
 * @property-read array $conversiondestination
 * @property-read array $impressiondata
 * @property-read array $impressionexpiry
 * @property-read array $reportingorigin
 */
final class PrivateClickMeasurementAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'private-click-measurement-attributes';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ATTRIBUTIONDESTINATION => [],
        Attribute::ATTRIBUTIONEXPIRY => [],
        Attribute::ATTRIBUTIONREPORTTO => [],
        Attribute::ATTRIBUTIONSOURCEEVENTID => [],
        Attribute::ATTRIBUTIONSOURCEID => [],
        Attribute::CONVERSIONDESTINATION => [],
        Attribute::IMPRESSIONDATA => [],
        Attribute::IMPRESSIONEXPIRY => [],
        Attribute::REPORTINGORIGIN => [],
    ];
}
