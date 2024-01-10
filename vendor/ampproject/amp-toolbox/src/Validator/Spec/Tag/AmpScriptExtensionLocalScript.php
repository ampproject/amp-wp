<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpScriptExtensionLocalScript.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array $cdata
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpScriptExtensionLocalScript extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-script extension local script';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'amp-script extension local script',
        SpecRule::ATTRS => [
            Attribute::ID => [
                SpecRule::MANDATORY => true,
                SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)(__amp_\S*|__count__|__defineGetter__|__defineSetter__|__lookupGetter__|__lookupSetter__|__noSuchMethod__|__parent__|__proto__|__AMP_\S*|\$p|\$proxy|acceptCharset|addEventListener|appendChild|assignedSlot|attachShadow|AMP|baseURI|checkValidity|childElementCount|childNodes|classList|className|clientHeight|clientLeft|clientTop|clientWidth|compareDocumentPosition|computedName|computedRole|contentEditable|createShadowRoot|enqueAction|firstChild|firstElementChild|getAnimations|getAttribute|getAttributeNS|getAttributeNode|getAttributeNodeNS|getBoundingClientRect|getClientRects|getDestinationInsertionPoints|getElementsByClassName|getElementsByTagName|getElementsByTagNameNS|getRootNode|hasAttribute|hasAttributeNS|hasAttributes|hasChildNodes|hasPointerCapture|i-amphtml-\S*|innerHTML|innerText|inputMode|insertAdjacentElement|insertAdjacentHTML|insertAdjacentText|isContentEditable|isDefaultNamespace|isEqualNode|isSameNode|lastChild|lastElementChild|lookupNamespaceURI|namespaceURI|nextElementSibling|nextSibling|nodeName|nodeType|nodeValue|offsetHeight|offsetLeft|offsetParent|offsetTop|offsetWidth|outerHTML|outerText|ownerDocument|parentElement|parentNode|previousElementSibling|previousSibling|querySelector|querySelectorAll|releasePointerCapture|removeAttribute|removeAttributeNS|removeAttributeNode|removeChild|removeEventListener|replaceChild|reportValidity|requestPointerLock|scrollHeight|scrollIntoView|scrollIntoViewIfNeeded|scrollLeft|scrollWidth|setAttribute|setAttributeNS|setAttributeNode|setAttributeNodeNS|setPointerCapture|shadowRoot|styleMap|tabIndex|tagName|textContent|toString|valueOf|(webkit|ms|moz|o)dropzone|(webkit|moz|ms|o)MatchesSelector|(webkit|moz|ms|o)RequestFullScreen|(webkit|moz|ms|o)RequestFullscreen)(\s|$)',
                SpecRule::ADD_VALUE_TO_SET => 'AMP_SCRIPT_IDS',
            ],
            Attribute::TARGET => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'amp-script',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
            Attribute::TYPE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_CASEI => [
                    'text/plain',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\MandatoryIdAttr::ID,
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-script/',
        SpecRule::CDATA => [
            SpecRule::MAX_BYTES => 10000,
            SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/components/amp-script/#faq',
            SpecRule::DISALLOWED_CDATA_REGEX => [
                [
                    SpecRule::REGEX => '<!--',
                    SpecRule::ERROR_MESSAGE => 'html comments',
                ],
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::SCRIPT,
        ],
    ];
}
