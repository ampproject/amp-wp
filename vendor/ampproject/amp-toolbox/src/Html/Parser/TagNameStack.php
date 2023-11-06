<?php

namespace AmpProject\Html\Parser;

use AmpProject\Html\UpperCaseTag as Tag;
use AmpProject\Str;

/**
 * Abstraction to keep track of which tags have been opened / closed as we traverse the tags in the document.
 *
 * Closing tags is tricky:
 * - Some tags have no end tag per spec. For example, there is no </img> tag per spec. Since we are making
 *   startTag()/endTag() calls, we manufacture endTag() calls for these immediately after the startTag().
 * - We assume all end tags are optional and we pop tags off our stack as we encounter parent closing tags. This part
 *   differs slightly from the behavior per spec: instead of closing an <option> tag when a following <option> tag
 *   is seen, we close it when the parent closing tag (in practice <select>) is encountered.
 *
 * @package ampproject/amp-toolbox
 */
final class TagNameStack
{
    /**
     * Regular expression that matches strings composed of all space characters, as defined in
     * https://infra.spec.whatwg.org/#ascii-whitespace, and in the various HTML parsing rules at
     * https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inhtml.
     *
     * Note: Do not USE \s to match whitespace as this includes many other characters that HTML parsing does not
     * consider whitespace.
     *
     * @var string
     */
    const SPACE_REGEX = '/^[ \f\n\r\t]*$/';

    /**
     * Regular expression that matches the characters considered whitespace by the C++ HTML parser.
     *
     * @var string
     */
    const CPP_SPACE_REGEX = '/^[ \f\n\r\t\v'
                            . '\x{00a0}\x{1680}\x{2000}-\x{200a}\x{2028}\x{2029}\x{202f}\x{205f}\x{3000}]*$/u';

    /**
     * The handler to manage the stack for.
     *
     * @var HtmlSaxHandler
     */
    private $handler;

    /**
     * The current tag name and its parents.
     *
     * @var array<string>
     */
    private $stack = [];

    /**
     * The current region within the document.
     *
     * @var TagRegion
     */
    private $region;

    /**
     * Keeps track of the attributes from all body tags encountered within the document.
     *
     * @var array<ParsedAttribute>
     */
    private $effectiveBodyAttributes = [];

    /**
     * TagNameStack constructor.
     *
     * @param HtmlSaxHandler $handler Handler to handle the HTML SAX parser events.
     */
    public function __construct(HtmlSaxHandler $handler)
    {
        $this->handler = $handler;
        $this->region  = TagRegion::PRE_DOCTYPE();
    }

    /**
     * Returns the attributes from all body tags within the document.
     *
     * @return array<ParsedAttribute>
     */
    public function effectiveBodyAttributes()
    {
        return $this->effectiveBodyAttributes;
    }

    /**
     * Enter a tag, opening a scope for child tags. Entering a tag can close the previous tag or enter other tags (such
     * as opening a <body> tag when encountering a tag not allowed outside the body.
     *
     * @param ParsedTag $tag Tag that is being started.
     */
    public function startTag(ParsedTag $tag)
    {
        // We only report the first body for each document - either a manufactured one, or the first one encountered.
        // However, we collect all attributes in $this->effectiveBodyAttributes.
        if ($tag->upperName() === Tag::BODY) {
            $this->effectiveBodyAttributes = array_merge($this->effectiveBodyAttributes, $tag->attributes());
        }

        // This section deals with manufacturing <head>, </head>, and <body> tags if the document has left them out or
        // placed them in the wrong location.
        switch ($this->region->getValue()) {
            case TagRegion::PRE_DOCTYPE:
                if ($tag->upperName() === Tag::_DOCTYPE) {
                    $this->region = TagRegion::PRE_HTML();
                } elseif ($tag->upperName() === Tag::HTML) {
                    $this->region = TagRegion::PRE_HEAD();
                } elseif ($tag->upperName() === Tag::HEAD) {
                    $this->region = TagRegion::IN_HEAD();
                } elseif ($tag->upperName() === Tag::BODY) {
                    $this->region = TagRegion::IN_BODY();
                } elseif (! in_array($tag->upperName(), Tag::STRUCTURE_TAGS, true)) {
                    if (in_array($tag->upperName(), Tag::ELEMENTS_ALLOWED_IN_HEAD, true)) {
                        $this->startTag(new ParsedTag(Tag::HEAD));
                    } else {
                        $this->handler->markManufacturedBody();
                        $this->startTag(new ParsedTag(Tag::BODY));
                    }
                }
                break;
            case TagRegion::PRE_HTML:
                // Stray DOCTYPE/HTML tags are ignored, not emitted twice.
                if ($tag->upperName() === Tag::_DOCTYPE) {
                    return;
                }
                if ($tag->upperName() === Tag::HTML) {
                    $this->region = TagRegion::PRE_HEAD();
                } elseif ($tag->upperName() === Tag::HEAD) {
                    $this->region = TagRegion::IN_HEAD();
                } elseif ($tag->upperName() === Tag::BODY) {
                    $this->region = TagRegion::IN_BODY();
                } elseif (! in_array($tag->upperName(), Tag::STRUCTURE_TAGS, true)) {
                    if (in_array($tag->upperName(), Tag::ELEMENTS_ALLOWED_IN_HEAD, true)) {
                        $this->startTag(new ParsedTag(Tag::HEAD));
                    } else {
                        $this->handler->markManufacturedBody();
                        $this->startTag(new ParsedTag(Tag::BODY));
                    }
                }
                break;
            case TagRegion::PRE_HEAD:
                // Stray DOCTYPE/HTML tags are ignored, not emitted twice.
                if ($tag->upperName() === Tag::_DOCTYPE || $tag->upperName() === Tag::HTML) {
                    return;
                }
                if ($tag->upperName() === Tag::HEAD) {
                    $this->region = TagRegion::IN_HEAD();
                } elseif ($tag->upperName() === Tag::BODY) {
                    $this->region = TagRegion::IN_BODY();
                } elseif (! in_array($tag->upperName(), Tag::STRUCTURE_TAGS, true)) {
                    if (in_array($tag->upperName(), Tag::ELEMENTS_ALLOWED_IN_HEAD, true)) {
                        $this->startTag(new ParsedTag(Tag::HEAD));
                    } else {
                        $this->handler->markManufacturedBody();
                        $this->startTag(new ParsedTag(Tag::BODY));
                    }
                }
                break;
            case TagRegion::IN_HEAD:
                // Stray DOCTYPE/HTML/HEAD tags are ignored, not emitted twice.
                if (
                    $tag->upperName() === Tag::_DOCTYPE || $tag->upperName() === Tag::HTML ||
                    $tag->upperName() === Tag::HEAD
                ) {
                    return;
                }
                if (! in_array($tag->upperName(), Tag::ELEMENTS_ALLOWED_IN_HEAD, true)) {
                    $this->endTag(new ParsedTag(Tag::HEAD));
                    if ($tag->upperName() !== Tag::BODY) {
                        $this->handler->markManufacturedBody();
                        $this->startTag(new ParsedTag(Tag::BODY));
                    } else {
                        $this->region = TagRegion::IN_BODY();
                    }
                }
                break;
            case TagRegion::PRE_BODY:
                // Stray DOCTYPE/HTML/HEAD tags are ignored, not emitted twice.
                if (
                    $tag->upperName() === Tag::_DOCTYPE
                    ||
                    $tag->upperName() === Tag::HTML
                    ||
                    $tag->upperName() === Tag::HEAD
                ) {
                    return;
                }
                if ($tag->upperName() !== Tag::BODY) {
                    $this->handler->markManufacturedBody();
                    $this->startTag(new ParsedTag(Tag::BODY));
                } else {
                    $this->region = TagRegion::IN_BODY();
                }
                break;
            case TagRegion::IN_BODY:
                // Stray DOCTYPE/HTML/HEAD tags are ignored, not emitted twice.
                if (
                    $tag->upperName() === Tag::_DOCTYPE
                    ||
                    $tag->upperName() === Tag::HTML
                    ||
                    $tag->upperName() === Tag::HEAD
                ) {
                    return;
                }
                if ($tag->upperName() === Tag::BODY) {
                    // We only report the first body for each document - either a manufactured one, or the first one
                    // encountered.
                    return;
                }
                if ($tag->upperName() === Tag::SVG) {
                    $this->region = TagRegion::IN_SVG();
                    break;
                }
                // Check implicit tag closing due to opening tags.
                if (count($this->stack) > 0) {
                    $parentTagName = $this->stack[count($this->stack) - 1];
                    // <p> tags can be implicitly closed by certain other start tags.
                    // See https://www.w3.org/TR/html-markup/p.html.
                    if (
                        $parentTagName === Tag::P
                        &&
                        in_array($tag->upperName(), Tag::P_CLOSING_TAGS, true)
                    ) {
                        $this->endTag(new ParsedTag(Tag::P));
                        // <dd> and <dt> tags can be implicitly closed by other <dd> and <dt> tags.
                        // See https://www.w3.org/TR/html-markup/dd.html.
                    } elseif (
                        ($parentTagName === Tag::DD || $parentTagName === Tag::DT)
                        &&
                        ($tag->upperName() === Tag::DD || $tag->upperName() === Tag::DT)
                    ) {
                        $this->endTag(new ParsedTag($parentTagName));
                        // <li> tags can be implicitly closed by other <li> tags.
                        // See https://www.w3.org/TR/html-markup/li.html.
                    } elseif (
                        $parentTagName === Tag::LI
                        &&
                        $tag->upperName() === Tag::LI
                    ) {
                        $this->endTag(new ParsedTag(Tag::LI));
                    }
                }
                break;
            case TagRegion::IN_SVG:
                $this->handler->startTag($tag);
                $this->stack[] = $tag->upperName();

                return;
            default:
                break;
        }

        $this->handler->startTag($tag);

        if (in_array($tag->upperName(), Tag::SELF_CLOSING_TAGS, true)) {
            // Ignore attributes in end tags.
            $this->handler->endTag(new ParsedTag($tag->upperName()));
        } else {
            $this->stack[] = $tag->upperName();
        }
    }

    /**
     * Callback for pcdata.
     *
     * Some text nodes can trigger the start of the body region.
     *
     * @param string $text Text of the text node.
     */
    public function pcdata($text)
    {
        if (Str::regexMatch(self::SPACE_REGEX, $text)) {
            // Only ASCII whitespace; this can be ignored for validator's purposes.
        } elseif (Str::regexMatch(self::CPP_SPACE_REGEX, $text)) {
            // Non-ASCII whitespace; if this occurs outside <body>, output a manufactured-body error. Do not create
            // implicit tags, in order to match the behavior of the buggy C++ parser. It just so happens this is also
            // good UX, since the subsequent validation errors caused by the implicit tags are unhelpful.
            switch ($this->region->getValue()) {
                // Fallthroughs intentional.
                case TagRegion::PRE_DOCTYPE:
                case TagRegion::PRE_HTML:
                case TagRegion::PRE_HEAD:
                case TagRegion::IN_HEAD:
                case TagRegion::PRE_BODY:
                    $this->handler->markManufacturedBody();
            }
        } else {
            // Non-whitespace text; if this occurs outside <body>, output a manufactured-body error and create the
            // necessary implicit tags.
            switch ($this->region->getValue()) {
                case TagRegion::PRE_DOCTYPE: // Doctype is not manufactured, fallthrough intentional.
                case TagRegion::PRE_HTML:
                    $this->startTag(new ParsedTag(Tag::HTML));
                    // Fallthrough intentional.
                case TagRegion::PRE_HEAD:
                    $this->startTag(new ParsedTag(Tag::HEAD));
                    // Fallthrough intentional.
                case TagRegion::IN_HEAD:
                    $this->endTag(new ParsedTag(Tag::HEAD));
                    // Fallthrough intentional.
                case TagRegion::PRE_BODY:
                    $this->handler->markManufacturedBody();
                    $this->startTag(new ParsedTag(Tag::BODY));
            }
        }

        $this->handler->pcdata($text);
    }

    /**
     * Upon exiting a tag, validation for the current matcher is triggered, e.g. for checking that the tag had some
     * specified number of children.
     *
     * @param ParsedTag $tag Tag that is being exited.
     */
    public function endTag($tag)
    {
        if ($this->region->equals(TagRegion::IN_HEAD()) && $tag->upperName() === Tag::HEAD) {
            $this->region = TagRegion::PRE_BODY();
        }

        /*
         * We ignore close body tags (</body) and instead insert them when their outer scope is closed (/html). This is
         * closer to how a browser parser works. The idea here is if other tags are found after the <body>, (ex: <div>)
         * which are only allowed in the <body>, we will effectively move them into the body section.
         */
        if ($tag->upperName() === Tag::BODY) {
            return;
        }

        /*
         * We look for tag.upperName() from the end. If we can find it, we pop everything from thereon off the stack. If
         * we can't find it, we don't bother with closing the tag, since it doesn't have a matching open tag, though in
         * practice the HtmlParser class will have already manufactured a start tag.
         */
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            if ($this->stack[$index] === $tag->upperName()) {
                while (count($this->stack) > $index) {
                    if ($this->stack[count($this->stack) - 1] === Tag::SVG) {
                        $this->region = TagRegion::IN_BODY();
                    }
                    $this->handler->endTag(new ParsedTag(array_pop($this->stack)));
                }

                return;
            }
        }
    }

    /**
     * This method is called when we're done with the document.
     *
     * Normally, the parser should actually close the tags, but just in case it doesn't this easy-enough method will
     * take care of it.
     */
    public function exitRemainingTags()
    {
        while (count($this->stack) > 0) {
            $this->handler->endTag(
                new ParsedTag(array_pop($this->stack))
            );
        }
    }
}
