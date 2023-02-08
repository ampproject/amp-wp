<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class GlobalAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $itemid
 * @property-read array $itemprop
 * @property-read array $itemref
 * @property-read array $itemscope
 * @property-read array $itemtype
 * @property-read array $about
 * @property-read array $content
 * @property-read array $datatype
 * @property-read array $inlist
 * @property-read array $prefix
 * @property-read array $property
 * @property-read array<string> $rel
 * @property-read array $resource
 * @property-read array $rev
 * @property-read array<bool> $style
 * @property-read array $typeof
 * @property-read array $vocab
 * @property-read array $accesskey
 * @property-read array $class
 * @property-read array $dir
 * @property-read array $draggable
 * @property-read array<array<string>> $hidden
 * @property-read array<string> $id
 * @property-read array $lang
 * @property-read array $slot
 * @property-read array $tabindex
 * @property-read array $title
 * @property-read array $translate
 * @property-read array $ariaActivedescendant
 * @property-read array $ariaAtomic
 * @property-read array $ariaAutocomplete
 * @property-read array $ariaBusy
 * @property-read array $ariaChecked
 * @property-read array $ariaControls
 * @property-read array $ariaCurrent
 * @property-read array $ariaDescribedby
 * @property-read array $ariaDisabled
 * @property-read array $ariaDropeffect
 * @property-read array $ariaExpanded
 * @property-read array $ariaFlowto
 * @property-read array $ariaGrabbed
 * @property-read array $ariaHaspopup
 * @property-read array $ariaHidden
 * @property-read array $ariaInvalid
 * @property-read array $ariaLabel
 * @property-read array $ariaLabelledby
 * @property-read array $ariaLevel
 * @property-read array $ariaLive
 * @property-read array $ariaModal
 * @property-read array $ariaMultiline
 * @property-read array $ariaMultiselectable
 * @property-read array $ariaOrientation
 * @property-read array $ariaOwns
 * @property-read array $ariaPosinset
 * @property-read array $ariaPressed
 * @property-read array $ariaReadonly
 * @property-read array $ariaRelevant
 * @property-read array $ariaRequired
 * @property-read array $ariaSelected
 * @property-read array $ariaSetsize
 * @property-read array $ariaSort
 * @property-read array $ariaValuemax
 * @property-read array $ariaValuemin
 * @property-read array $ariaValuenow
 * @property-read array $ariaValuetext
 * @property-read array<array> $on
 * @property-read array $role
 * @property-read array<array<string>> $placeholder
 * @property-read array<array<string>> $fallback
 * @property-read array $overflow
 * @property-read array $ampAccess
 * @property-read array $ampAccessBehavior
 * @property-read array $ampAccessHide
 * @property-read array $ampAccessId
 * @property-read array $ampAccessLoader
 * @property-read array $ampAccessLoading
 * @property-read array $ampAccessOff
 * @property-read array $ampAccessOn
 * @property-read array $ampAccessShow
 * @property-read array $ampAccessStyle
 * @property-read array $ampAccessTemplate
 * @property-read array $iAmpAccessId
 * @property-read array<array<array<string>>> $validationFor
 * @property-read array $visibleWhenInvalid
 * @property-read array $ampFx
 * @property-read array<array<string>> $subscriptionsAction
 * @property-read array<array<string>> $subscriptionsActions
 * @property-read array<array<string>> $subscriptionsDecorate
 * @property-read array<array<string>> $subscriptionsDialog
 * @property-read array<array<string>> $subscriptionsDisplay
 * @property-read array<array<string>> $subscriptionsLang
 * @property-read array<array<string>> $subscriptionsSection
 * @property-read array<array<string>> $subscriptionsService
 * @property-read array<array<string>> $subscriptionsGoogleRtc
 * @property-read array<array<string>> $nextPageHide
 * @property-read array<array<string>> $nextPageReplace
 * @property-read array $ariaActivedescendant_binding
 * @property-read array $ariaAtomic_binding
 * @property-read array $ariaAutocomplete_binding
 * @property-read array $ariaBusy_binding
 * @property-read array $ariaChecked_binding
 * @property-read array $ariaControls_binding
 * @property-read array $ariaDescribedby_binding
 * @property-read array $ariaDisabled_binding
 * @property-read array $ariaDropeffect_binding
 * @property-read array $ariaExpanded_binding
 * @property-read array $ariaFlowto_binding
 * @property-read array $ariaGrabbed_binding
 * @property-read array $ariaHaspopup_binding
 * @property-read array $ariaHidden_binding
 * @property-read array $ariaInvalid_binding
 * @property-read array $ariaLabel_binding
 * @property-read array $ariaLabelledby_binding
 * @property-read array $ariaLevel_binding
 * @property-read array $ariaLive_binding
 * @property-read array $ariaMultiline_binding
 * @property-read array $ariaMultiselectable_binding
 * @property-read array $ariaOrientation_binding
 * @property-read array $ariaOwns_binding
 * @property-read array $ariaPosinset_binding
 * @property-read array $ariaPressed_binding
 * @property-read array $ariaReadonly_binding
 * @property-read array $ariaRelevant_binding
 * @property-read array $ariaRequired_binding
 * @property-read array $ariaSelected_binding
 * @property-read array $ariaSetsize_binding
 * @property-read array $ariaSort_binding
 * @property-read array $ariaValuemax_binding
 * @property-read array $ariaValuemin_binding
 * @property-read array $ariaValuenow_binding
 * @property-read array $ariaValuetext_binding
 * @property-read array $class_binding
 * @property-read array $hidden_binding
 * @property-read array $text_binding
 * @property-read array<array<string>> $iAmphtmlBinding
 * @property-read array<array<array<string>>> $autoscroll
 */
final class GlobalAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = '$GLOBAL_ATTRS';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ITEMID => [],
        Attribute::ITEMPROP => [],
        Attribute::ITEMREF => [],
        Attribute::ITEMSCOPE => [],
        Attribute::ITEMTYPE => [],
        Attribute::ABOUT => [],
        Attribute::CONTENT => [],
        Attribute::DATATYPE => [],
        Attribute::INLIST => [],
        Attribute::PREFIX => [],
        Attribute::PROPERTY => [],
        Attribute::REL => [
            SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)(canonical|components|dns-prefetch|import|manifest|preconnect|preload|prerender|serviceworker|stylesheet|subresource)(\s|$)',
        ],
        Attribute::RESOURCE => [],
        Attribute::REV => [],
        Attribute::STYLE => [
            SpecRule::VALUE_DOC_CSS => true,
        ],
        Attribute::TYPEOF => [],
        Attribute::VOCAB => [],
        Attribute::ACCESSKEY => [],
        Attribute::CLASS_ => [],
        Attribute::DIR => [],
        Attribute::DRAGGABLE => [],
        Attribute::HIDDEN => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::ID => [
            SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)(__amp_\S*|__count__|__defineGetter__|__defineSetter__|__lookupGetter__|__lookupSetter__|__noSuchMethod__|__parent__|__proto__|__AMP_\S*|\$p|\$proxy|acceptCharset|addEventListener|appendChild|assignedSlot|attachShadow|AMP|baseURI|checkValidity|childElementCount|childNodes|classList|className|clientHeight|clientLeft|clientTop|clientWidth|compareDocumentPosition|computedName|computedRole|contentEditable|createShadowRoot|enqueAction|firstChild|firstElementChild|getAnimations|getAttribute|getAttributeNS|getAttributeNode|getAttributeNodeNS|getBoundingClientRect|getClientRects|getDestinationInsertionPoints|getElementsByClassName|getElementsByTagName|getElementsByTagNameNS|getRootNode|hasAttribute|hasAttributeNS|hasAttributes|hasChildNodes|hasPointerCapture|i-amphtml-\S*|innerHTML|innerText|inputMode|insertAdjacentElement|insertAdjacentHTML|insertAdjacentText|isContentEditable|isDefaultNamespace|isEqualNode|isSameNode|lastChild|lastElementChild|lookupNamespaceURI|namespaceURI|nextElementSibling|nextSibling|nodeName|nodeType|nodeValue|offsetHeight|offsetLeft|offsetParent|offsetTop|offsetWidth|outerHTML|outerText|ownerDocument|parentElement|parentNode|previousElementSibling|previousSibling|querySelector|querySelectorAll|releasePointerCapture|removeAttribute|removeAttributeNS|removeAttributeNode|removeChild|removeEventListener|replaceChild|reportValidity|requestPointerLock|scrollHeight|scrollIntoView|scrollIntoViewIfNeeded|scrollLeft|scrollWidth|setAttribute|setAttributeNS|setAttributeNode|setAttributeNodeNS|setPointerCapture|shadowRoot|styleMap|tabIndex|tagName|textContent|toString|valueOf|(webkit|ms|moz|o)dropzone|(webkit|moz|ms|o)MatchesSelector|(webkit|moz|ms|o)RequestFullScreen|(webkit|moz|ms|o)RequestFullscreen)(\s|$)',
        ],
        Attribute::LANG => [],
        Attribute::SLOT => [],
        Attribute::TABINDEX => [],
        Attribute::TITLE => [],
        Attribute::TRANSLATE => [],
        Attribute::ARIA_ACTIVEDESCENDANT => [],
        Attribute::ARIA_ATOMIC => [],
        Attribute::ARIA_AUTOCOMPLETE => [],
        Attribute::ARIA_BUSY => [],
        Attribute::ARIA_CHECKED => [],
        Attribute::ARIA_CONTROLS => [],
        Attribute::ARIA_CURRENT => [],
        Attribute::ARIA_DESCRIBEDBY => [],
        Attribute::ARIA_DISABLED => [],
        Attribute::ARIA_DROPEFFECT => [],
        Attribute::ARIA_EXPANDED => [],
        Attribute::ARIA_FLOWTO => [],
        Attribute::ARIA_GRABBED => [],
        Attribute::ARIA_HASPOPUP => [],
        Attribute::ARIA_HIDDEN => [],
        Attribute::ARIA_INVALID => [],
        Attribute::ARIA_LABEL => [],
        Attribute::ARIA_LABELLEDBY => [],
        Attribute::ARIA_LEVEL => [],
        Attribute::ARIA_LIVE => [],
        Attribute::ARIA_MODAL => [],
        Attribute::ARIA_MULTILINE => [],
        Attribute::ARIA_MULTISELECTABLE => [],
        Attribute::ARIA_ORIENTATION => [],
        Attribute::ARIA_OWNS => [],
        Attribute::ARIA_POSINSET => [],
        Attribute::ARIA_PRESSED => [],
        Attribute::ARIA_READONLY => [],
        Attribute::ARIA_RELEVANT => [],
        Attribute::ARIA_REQUIRED => [],
        Attribute::ARIA_SELECTED => [],
        Attribute::ARIA_SETSIZE => [],
        Attribute::ARIA_SORT => [],
        Attribute::ARIA_VALUEMAX => [],
        Attribute::ARIA_VALUEMIN => [],
        Attribute::ARIA_VALUENOW => [],
        Attribute::ARIA_VALUETEXT => [],
        Attribute::ON => [
            SpecRule::TRIGGER => [
                SpecRule::IF_VALUE_REGEX => 'tap:.*',
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::ROLE,
                    Attribute::TABINDEX,
                ],
            ],
        ],
        Attribute::ROLE => [],
        Attribute::PLACEHOLDER => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::FALLBACK => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::OVERFLOW => [],
        Attribute::AMP_ACCESS => [],
        Attribute::AMP_ACCESS_BEHAVIOR => [],
        Attribute::AMP_ACCESS_HIDE => [],
        Attribute::AMP_ACCESS_ID => [],
        Attribute::AMP_ACCESS_LOADER => [],
        Attribute::AMP_ACCESS_LOADING => [],
        Attribute::AMP_ACCESS_OFF => [],
        Attribute::AMP_ACCESS_ON => [],
        Attribute::AMP_ACCESS_SHOW => [],
        Attribute::AMP_ACCESS_STYLE => [],
        Attribute::AMP_ACCESS_TEMPLATE => [],
        Attribute::I_AMP_ACCESS_ID => [],
        Attribute::VALIDATION_FOR => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::VISIBLE_WHEN_INVALID,
                ],
            ],
        ],
        Attribute::VISIBLE_WHEN_INVALID => [
            SpecRule::VALUE => [
                'badInput',
                'customError',
                'patternMismatch',
                'rangeOverflow',
                'rangeUnderflow',
                'stepMismatch',
                'tooLong',
                'tooShort',
                'typeMismatch',
                'valueMissing',
            ],
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::VALIDATION_FOR,
                ],
            ],
        ],
        Attribute::AMP_FX => [
            SpecRule::VALUE_REGEX_CASEI => '(fade-in|fade-in-scroll|float-in-bottom|float-in-top|fly-in-bottom|fly-in-left|fly-in-right|fly-in-top|parallax)(\s|fade-in|fade-in-scroll|float-in-bottom|float-in-top|fly-in-bottom|fly-in-left|fly-in-right|fly-in-top|parallax)*',
            SpecRule::REQUIRES_EXTENSION => [
                Extension::FX_COLLECTION,
            ],
        ],
        Attribute::SUBSCRIPTIONS_ACTION => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_ACTIONS => [
            SpecRule::VALUE => [
                '',
            ],
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_DECORATE => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_DIALOG => [
            SpecRule::VALUE => [
                '',
            ],
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_DISPLAY => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_LANG => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_SECTION => [
            SpecRule::VALUE_CASEI => [
                'actions',
                'content',
                'content-not-granted',
                'loading',
            ],
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_SERVICE => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS,
            ],
        ],
        Attribute::SUBSCRIPTIONS_GOOGLE_RTC => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::SUBSCRIPTIONS_GOOGLE,
            ],
        ],
        Attribute::NEXT_PAGE_HIDE => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::NEXT_PAGE,
            ],
        ],
        Attribute::NEXT_PAGE_REPLACE => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::NEXT_PAGE,
            ],
        ],
        '[ARIA_ACTIVEDESCENDANT]' => [],
        '[ARIA_ATOMIC]' => [],
        '[ARIA_AUTOCOMPLETE]' => [],
        '[ARIA_BUSY]' => [],
        '[ARIA_CHECKED]' => [],
        '[ARIA_CONTROLS]' => [],
        '[ARIA_DESCRIBEDBY]' => [],
        '[ARIA_DISABLED]' => [],
        '[ARIA_DROPEFFECT]' => [],
        '[ARIA_EXPANDED]' => [],
        '[ARIA_FLOWTO]' => [],
        '[ARIA_GRABBED]' => [],
        '[ARIA_HASPOPUP]' => [],
        '[ARIA_HIDDEN]' => [],
        '[ARIA_INVALID]' => [],
        '[ARIA_LABEL]' => [],
        '[ARIA_LABELLEDBY]' => [],
        '[ARIA_LEVEL]' => [],
        '[ARIA_LIVE]' => [],
        '[ARIA_MULTILINE]' => [],
        '[ARIA_MULTISELECTABLE]' => [],
        '[ARIA_ORIENTATION]' => [],
        '[ARIA_OWNS]' => [],
        '[ARIA_POSINSET]' => [],
        '[ARIA_PRESSED]' => [],
        '[ARIA_READONLY]' => [],
        '[ARIA_RELEVANT]' => [],
        '[ARIA_REQUIRED]' => [],
        '[ARIA_SELECTED]' => [],
        '[ARIA_SETSIZE]' => [],
        '[ARIA_SORT]' => [],
        '[ARIA_VALUEMAX]' => [],
        '[ARIA_VALUEMIN]' => [],
        '[ARIA_VALUENOW]' => [],
        '[ARIA_VALUETEXT]' => [],
        '[CLASS]' => [],
        '[HIDDEN]' => [],
        '[TEXT]' => [],
        Attribute::I_AMPHTML_BINDING => [
            SpecRule::VALUE => [
                '',
            ],
            SpecRule::ENABLED_BY => [
                Attribute::TRANSFORMED,
            ],
        ],
        Attribute::AUTOSCROLL => [
            SpecRule::REQUIRES_ANCESTOR => [
                SpecRule::MARKER => [
                    'AUTOSCROLL',
                ],
            ],
        ],
    ];
}
