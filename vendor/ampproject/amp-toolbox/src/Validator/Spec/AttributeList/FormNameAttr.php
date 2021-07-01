<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class FormNameAttr.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $name
 */
final class FormNameAttr extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'form-name-attr';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::NAME => [
            SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)(ATTRIBUTE_NODE|CDATA_SECTION_NODE|COMMENT_NODE|DOCUMENT_FRAGMENT_NODE|DOCUMENT_NODE|DOCUMENT_POSITION_CONTAINED_BY|DOCUMENT_POSITION_CONTAINS|DOCUMENT_POSITION_DISCONNECTED|DOCUMENT_POSITION_FOLLOWING|DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC|DOCUMENT_POSITION_PRECEDING|DOCUMENT_TYPE_NODE|ELEMENT_NODE|ENTITY_NODE|ENTITY_REFERENCE_NODE|NOTATION_NODE|PROCESSING_INSTRUCTION_NODE|TEXT_NODE|URL|URLUnencoded|__amp_\S*|__count__|__defineGetter__|__defineSetter__|__lookupGetter__|__lookupSetter__|__noSuchMethod__|__parent__|__proto__|__AMP_\S*|activeElement|addEventListener|adoptNode|alinkColor|all|anchors|append|appendChild|applets|baseURI|bgColor|body|captureEvents|caretPositionFromPoint|caretRangeFromPoint|characterSet|charset|childElementCount|childNodes|children|clear|cloneNode|close|compareDocumentPosition|compatMode|constructor|contains|contentType|cookie|createAttribute|createAttributeNS|createCDATASection|createComment|createDocumentFragment|createElement|createElementNS|createEvent|createExpression|createNSResolver|createNodeIterator|createProcessingInstruction|createRange|createTextNode|createTreeWalker|currentScript|defaultView|designMode|dir|dispatchEvent|doctype|documentElement|documentURI|domain|elementFromPoint|elementsFromPoint|embeds|enableStyleSheetsForSet|evaluate|execCommand|execCommandShowHelp|exitFullscreen|exitPictureInPicture|exitPointerLock|fgColor|firstChild|firstElementChild|focus|fonts|forms|fullscreen|fullscreenElement|fullscreenEnabled|getCSSCanvasContext|getElementById|getElementsByClassName|getElementsByName|getElementsByTagName|getElementsByTagNameNS|getOverrideStyle|getRootNode|getSelection|hasChildNodes|hasFocus|hasOwnProperty|hasStorageAccess|head|hidden|images|implementation|importNode|inputEncoding|insertBefore|isConnected|isDefaultNamespace|isEqualNode|isPrototypeOf|isSameNode|l10n|lastChild|lastElementChild|lastModified|lastStyleSheetSet|linkColor|links|location|lookupNamespaceURI|lookupPrefix|mozCancelFullScreen|mozFullScreen|mozFullScreenElement|mozFullScreenEnabled|mozSetImageElement|msCSSOMElementFloatMetrics|msCapsLockWarningOff|msElementsFromPoint|msElementsFromRect|nextSibling|nodeName|nodeType|nodeValue|normalize|onabort|onactivate|onafterscriptexecute|onanimationcancel|onanimationend|onanimationiteration|onanimationstart|onauxclick|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeinput|onbeforepaste|onbeforescriptexecute|onblur|oncancel|oncanplay|oncanplaythrough|onchange|onclick|onclose|oncontextmenu|oncopy|oncuechange|oncut|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragexit|ondragleave|ondragover|ondragstart|ondrop|ondurationchange|onemptied|onended|onerror|onfocus|onfreeze|onfullscreenchange|onfullscreenerror|ongotpointercapture|oninput|oninvalid|onkeydown|onkeypress|onkeyup|onload|onloadeddata|onloadedmetadata|onloadend|onloadstart|onlostpointercapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmozfullscreenchange|onmozfullscreenerror|onmscontentzoom|onmsgesturechange|onmsgesturedoubletap|onmsgestureend|onmsgesturehold|onmsgesturestart|onmsgesturetap|onmsinertiastart|onmsmanipulationstatechanged|onmssitemodejumplistitemremoved|onmsthumbnailclick|onpaste|onpause|onplay|onplaying|onpointercancel|onpointerdown|onpointerenter|onpointerleave|onpointerlockchange|onpointerlockerror|onpointermove|onpointerout|onpointerover|onpointerup|onprogress|onratechange|onreadystatechange|onrejectionhandled|onreset|onresize|onresume|onscroll|onsearch|onseeked|onseeking|onselect|onselectionchange|onselectstart|onshow|onstalled|onstop|onsubmit|onsuspend|ontimeupdate|ontoggle|ontransitioncancel|ontransitionend|ontransitionrun|ontransitionstart|onunhandledrejection|onvisibilitychange|onvolumechange|onwaiting|onwebkitanimationend|onwebkitanimationiteration|onwebkitanimationstart|onwebkitfullscreenchange|onwebkitfullscreenerror|onwebkitmouseforcechanged|onwebkitmouseforcedown|onwebkitmouseforceup|onwebkitmouseforcewillbegin|onwebkittransitionend|onwheel|open|origin|ownerDocument|parentElement|parentNode|pictureInPictureElement|pictureInPictureEnabled|plugins|pointerLockElement|preferredStyleSheetSet|prepend|previousSibling|propertyIsEnumerable|queryCommandEnabled|queryCommandIndeterm|queryCommandState|queryCommandSupported|queryCommandText|queryCommandValue|querySelector|querySelectorAll|readyState|referrer|registerElement|releaseCapture|releaseEvents|removeChild|removeEventListener|replaceChild|requestStorageAccess|rootElement|scripts|scrollingElement|selectedStyleSheetSet|styleSheetSets|styleSheets|textContent|title|toLocaleString|toSource|toString|updateSettings|valueOf|visibilityState|vlinkColor|wasDiscarded|webkitCancelFullScreen|webkitCurrentFullScreenElement|webkitExitFullscreen|webkitFullScreenKeyboardInputAllowed|webkitFullscreenElement|webkitFullscreenEnabled|webkitHidden|webkitIsFullScreen|webkitVisibilityState|write|writeln|xmlEncoding|xmlStandalone|xmlVersion)(\s|$)',
        ],
    ];
}
