<?php

namespace AmpProject\Html\Parser;

use AmpProject\Encoding;
use AmpProject\Exception\FailedToParseHtml;
use AmpProject\Html\UpperCaseTag as Tag;
use AmpProject\Str;

/**
 * An Html parser.
 *
 * The parse() method takes a string and calls methods on HtmlSaxHandler while it is visiting its tokens.
 *
 * @package ampproject/amp-toolbox
 */
final class HtmlParser
{
    /**
     * Regular expression that matches the next token to be processed.
     *
     * @var string
     */
    const INSIDE_TAG_TOKEN =
        // Don't capture space. In this case, we don't use \s because it includes a non-breaking space which gets
        // included as an attribute in our validation.
        '%^[ \t\n\f\r\v]*(?:' .
            // Capture an attribute name in group 1, and value in group 3. We capture the fact that there was an
            // attribute in group 2, since interpreters are inconsistent in whether a group that matches nothing is
            // null, undefined, or the empty string.
            '(?:' .
                // Allow attribute names to start with /, avoiding assigning the / in close-tag syntax */>.
                '([^\t\r\n /=>][^\t\r\n =>]*|' .  // e.g. "href".
                '[^\t\r\n =>]+[^ >]|' .              // e.g. "/asdfs/asd".
                '\/+(?!>))' .                           // e.g. "/".
                // Optionally followed by...
                '(' .
                    '\s*=\s*' .
                    '(' .
                        // A double quoted string.
                        '\"[^\"]*\"' .
                        // A single quoted string.
                        '|\'[^\']*\'' .
                        // The positive lookahead is used to make sure that in <foo bar= baz=boo>, the value for bar is
                        // blank, not "baz=boo". Note that <foo bar=baz=boo zik=zak>, the value for bar is "baz=boo" and
                        // the value for zip is "zak".
                        '|(?=[a-z][a-z-]*\s+=)' .
                        // An unquoted value that is not an attribute name. We know it is not an attribute name because
                        // the previous zero-width match would've eliminated that possibility.
                        '|[^>\s]*' .
                        ')' .
                    ')' .
                '?' .
                ')' .
            // End of tag captured in group 3.
            '|(/?>)' .
            // Don't capture cruft.
            '|[^a-z\s>]+)' .
        '%i';


    /**
     * Regular expression that matches the next token to be processed when we are outside a tag.
     *
     * @var string
     */
    const OUTSIDE_TAG_TOKEN =
        '%^(?:' .
            // Entity captured in group 1.
            '&(\#[0-9]+|\#[x][0-9a-f]+|\w+);' .
            // Comments not captured.
            '|<[!]--[\s\S]*?(?:--[!]?>|$)' .
            // '/' captured in group 2 for close tags, and name captured in group 3. The first character of a tag (after
            // possibly '/') can be A-Z, a-z, '!' or '?'. The remaining characters are more easily expressed as a
            // negative set of: '\0', ' ', '\n', '\r', '\t', '\f', '\v', '>', or '/'.
            '|<(/)?([a-z!\?][^\0 \n\r\t\f\v>/]*)' .
            // Text captured in group 4.
            '|([^<&>]+)' .
            // Cruft captured in group 5.
            '|([<&>]))' .
        '%i';

    /**
     * Regular expression that matches null characters.
     *
     * @var string
     */
    const NULL_REGEX = "/\0/g";

    /**
     * Regular expression that matches entities.
     *
     * @var string
     */
    const ENTITY_REGEX = '/&(#\d+|#x[0-9A-Fa-f]+|\w+);/g';

    /**
     * Regular expression that matches loose &s.
     *
     * @var string
     */
    const LOOSE_AMP_REGEX = '/&([^a-z#]|#(?:[^0-9x]|x(?:[^0-9a-f]|$)|$)|$)/gi';

    /**
     * Regular expression that matches <.
     *
     * @var string
     */
    const LT_REGEX = '/</g';

    /**
     * Regular expression that matches >.
     *
     * @var string
     */
    const GT_REGEX = '/>/g';

    /**
     * Regular expression that matches decimal numbers.
     *
     * @var string
     */
    const DECIMAL_ESCAPE_REGEX = '/^#(\d+)$/';

    /**
     * Regular expression that matches hexadecimal numbers.
     *
     * @var string
     */
    const HEX_ESCAPE_REGEX = '/^#x([0-9A-Fa-f]+)$/';

    /**
     * HTML entities that are encoded/decoded.
     *
     * @type array<string>
     */
    const ENTITIES = [
        'colon' => ':',
        'lt'    => '<',
        'gt'    => '>',
        'amp'   => '&',
        'nbsp'  => '\u00a0',
        'quot'  => '"',
        'apos'  => '\'',
    ];

    /**
     * A map of element to a bitmap of flags it has, used internally on the parser.
     *
     * @var array<int>
     */
    const ELEMENTS = [
        Tag::A          => 0,
        Tag::ABBR       => 0,
        Tag::ACRONYM    => 0,
        Tag::ADDRESS    => 0,
        Tag::APPLET     => EFlags::UNSAFE,
        Tag::AREA       => EFlags::EMPTY_,
        Tag::B          => 0,
        Tag::BASE       => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::BASEFONT   => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::BDO        => 0,
        Tag::BIG        => 0,
        Tag::BLOCKQUOTE => 0,
        Tag::BODY       => EFlags::OPTIONAL_ENDTAG | EFlags::UNSAFE | EFlags::FOLDABLE,
        Tag::BR         => EFlags::EMPTY_,
        Tag::BUTTON     => 0,
        Tag::CANVAS     => 0,
        Tag::CAPTION    => 0,
        Tag::CENTER     => 0,
        Tag::CITE       => 0,
        Tag::CODE       => 0,
        Tag::COL        => EFlags::EMPTY_,
        Tag::COLGROUP   => EFlags::OPTIONAL_ENDTAG,
        Tag::DD         => EFlags::OPTIONAL_ENDTAG,
        Tag::DEL        => 0,
        Tag::DFN        => 0,
        Tag::DIR        => 0,
        Tag::DIV        => 0,
        Tag::DL         => 0,
        Tag::DT         => EFlags::OPTIONAL_ENDTAG,
        Tag::EM         => 0,
        Tag::FIELDSET   => 0,
        Tag::FONT       => 0,
        Tag::FORM       => 0,
        Tag::FRAME      => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::FRAMESET   => EFlags::UNSAFE,
        Tag::H1         => 0,
        Tag::H2         => 0,
        Tag::H3         => 0,
        Tag::H4         => 0,
        Tag::H5         => 0,
        Tag::H6         => 0,
        Tag::HEAD       => EFlags::OPTIONAL_ENDTAG | EFlags::UNSAFE | EFlags::FOLDABLE,
        Tag::HR         => EFlags::EMPTY_,
        Tag::HTML       => EFlags::OPTIONAL_ENDTAG | EFlags::UNSAFE | EFlags::FOLDABLE,
        Tag::I          => 0,
        Tag::IFRAME     => EFlags::UNSAFE | EFlags::CDATA,
        Tag::IMG        => EFlags::EMPTY_,
        Tag::INPUT      => EFlags::EMPTY_,
        Tag::INS        => 0,
        Tag::ISINDEX    => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::KBD        => 0,
        Tag::LABEL      => 0,
        Tag::LEGEND     => 0,
        Tag::LI         => EFlags::OPTIONAL_ENDTAG,
        Tag::LINK       => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::MAP        => 0,
        Tag::MENU       => 0,
        Tag::META       => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::NOFRAMES   => EFlags::UNSAFE | EFlags::CDATA,
        // TODO: This used to read:
        // Tag::NOSCRIPT => EFlags::UNSAFE | EFlags::CDATA,
        // It appears that the effect of that is that anything inside is then considered cdata, so
        // <noscript><style>foo</noscript></noscript> never sees a style start tag / end tag event. But we must
        // recognize such style tags and they're also allowed by HTML, e.g. see:
        // https://developer.mozilla.org/en-US/docs/Web/HTML/Element/noscript
        // On a broader note this also means we may be missing other start/end tag events inside elements marked as
        // CDATA which our parser should better reject. Yikes.
        Tag::NOSCRIPT   => EFlags::UNSAFE,
        Tag::OBJECT     => EFlags::UNSAFE,
        Tag::OL         => 0,
        Tag::OPTGROUP   => 0,
        Tag::OPTION     => EFlags::OPTIONAL_ENDTAG,
        Tag::P          => EFlags::OPTIONAL_ENDTAG,
        Tag::PARAM      => EFlags::EMPTY_ | EFlags::UNSAFE,
        Tag::PRE        => 0,
        Tag::Q          => 0,
        Tag::S          => 0,
        Tag::SAMP       => 0,
        Tag::SCRIPT     => EFlags::UNSAFE | EFlags::CDATA,
        Tag::SELECT     => 0,
        Tag::SMALL      => 0,
        Tag::SPAN       => 0,
        Tag::STRIKE     => 0,
        Tag::STRONG     => 0,
        Tag::STYLE      => EFlags::UNSAFE | EFlags::CDATA,
        Tag::SUB        => 0,
        Tag::SUP        => 0,
        Tag::TABLE      => 0,
        Tag::TBODY      => EFlags::OPTIONAL_ENDTAG,
        Tag::TD         => EFlags::OPTIONAL_ENDTAG,
        Tag::TEXTAREA   => EFlags::RCDATA,
        Tag::TFOOT      => EFlags::OPTIONAL_ENDTAG,
        Tag::TH         => EFlags::OPTIONAL_ENDTAG,
        Tag::THEAD      => EFlags::OPTIONAL_ENDTAG,
        Tag::TITLE      => EFlags::RCDATA | EFlags::UNSAFE,
        Tag::TR         => EFlags::OPTIONAL_ENDTAG,
        Tag::TT         => 0,
        Tag::U          => 0,
        Tag::UL         => 0,
        Tag::VAR_       => 0,
    ];

    /**
     * Given a SAX-like HtmlSaxHandler, this parses a $htmlText and lets the $handler know the structure while visiting
     * the nodes. If the provided handler is an implementation of HtmlSaxHandlerWithLocation, then its setDocLocator()
     * method will get called prior to startDoc(), and the getLine() / getColumn() methods will reflect the current
     * line / column while a SAX callback (e.g., startTag()) is active.
     *
     * @param HtmlSaxHandler $handler  The HtmlSaxHandler that will receive the events.
     * @param string         $htmlText The html text.
     */
    public function parse(HtmlSaxHandler $handler, $htmlText)
    {
        $htmlUpper  = null;
        $inTag      = false; // True iff we're currently processing a tag.
        $attributes = [];    // Accumulates attribute names and values.
        $tagName    = null;  // The name of the tag currently being processed.
        $eflags     = null;  // The element flags for the current tag.
        $openTag    = false; // True if the current tag is an open tag.
        $tagStack   = new TagNameStack($handler);

        Str::setEncoding(Encoding::AMP);

        // Only provide location information if the handler implements the setDocLocator method.
        $locator = null;
        if ($handler instanceof HtmlSaxHandlerWithLocation) {
            $locator = new DocLocator($htmlText);
            $handler->setDocLocator($locator);
        }

        // Lets the handler know that we are starting to parse the document.
        $handler->startDoc();

        // Consumes tokens from the htmlText and stops once all tokens are processed.
        while ($htmlText) {
            $regex = $inTag ? self::INSIDE_TAG_TOKEN : self::OUTSIDE_TAG_TOKEN;
            // Gets the next token.
            $matches = null;
            Str::regexMatch($regex, $htmlText, $matches);

            // Avoid infinite loop in case nothing could be matched.
            // This can be caused by documents provided in the wrong encoding, which the regex engine fails to handle.
            if (empty($matches[0])) {
                throw FailedToParseHtml::forHtml($htmlText);
            }

            if ($locator) {
                $locator->advancePosition($matches[0]);
            }
            // And removes it from the string.
            $htmlText = Str::substring($htmlText, Str::length($matches[0]));

            if ($inTag) {
                if (!empty($matches[1])) {  // Attribute.
                    // SetAttribute with uppercase names doesn't work on IE6.
                    $attributeName = Str::toLowerCase($matches[1]);
                    // Use empty string as value for valueless attribs, so <input type=checkbox checked> gets attributes
                    // ['type', 'checkbox', 'checked', ''].
                    $decodedValue = '';
                    if (!empty($matches[2])) {
                        $encodedValue = $matches[3];
                        switch (Str::substring($encodedValue, 0, 1)) {  // Strip quotes.
                            case '"':
                            case "'":
                                $encodedValue = Str::substring($encodedValue, 1, Str::length($encodedValue) - 2);
                                break;
                        }
                        $decodedValue = $this->unescapeEntities($this->stripNULs($encodedValue));
                    }
                    $attributes[] = $attributeName;
                    $attributes[] = $decodedValue;
                } elseif (!empty($matches[4])) {
                    if ($eflags !== null) {  // False if not in allowlist.
                        if ($openTag) {
                            $tagStack->startTag(new ParsedTag($tagName, $attributes));
                        } else {
                            $tagStack->endTag(new ParsedTag($tagName));
                        }
                    }

                    if ($openTag && ($eflags & (EFlags::CDATA | EFlags::RCDATA))) {
                        if ($htmlUpper === null) {
                            $htmlUpper = Str::toUpperCase($htmlText);
                        } else {
                            $htmlUpper = Str::substring($htmlUpper, Str::length($htmlUpper) - Str::length($htmlText));
                        }
                        $dataEnd = Str::position($htmlUpper, "</{$tagName}");
                        if ($dataEnd < 0) {
                                  $dataEnd = Str::length($htmlText);
                        }
                        if ($eflags & EFlags::CDATA) {
                            $handler->cdata(Str::substring($htmlText, 0, $dataEnd));
                        } else {
                            $handler->rcdata($this->normalizeRCData(Str::substring($htmlText, 0, $dataEnd)));
                        }
                        if ($locator) {
                            $locator->advancePosition(Str::substring($htmlText, 0, $dataEnd));
                        }
                        $htmlText = Str::substring($htmlText, $dataEnd);
                    }

                    $tagName    = null;
                    $eflags     = null;
                    $openTag    = false;
                    $attributes = [];
                    if ($locator) {
                        $locator->snapshotPosition();
                    }
                    $inTag = false;
                }
            } else {
                if (!empty($matches[1])) { // Entity.
                    $tagStack->pcdata($matches[0]);
                } elseif (!empty($matches[3])) { // Tag.
                    $openTag = ! $matches[2];
                    if ($locator) {
                        $locator->snapshotPosition();
                    }
                    $inTag = true;
                    $tagName = Str::toUpperCase($matches[3]);
                    $eflags = array_key_exists($tagName, self::ELEMENTS)
                        ? self::ELEMENTS[$tagName]
                        : EFlags::UNKNOWN_OR_CUSTOM;
                } elseif (!empty($matches[4])) { // Text.
                    if ($locator) {
                        $locator->snapshotPosition();
                    }
                    $tagStack->pcdata($matches[4]);
                } elseif (!empty($matches[5])) { // Cruft.
                    switch ($matches[5]) {
                        case '<':
                            $tagStack->pcdata('&lt;');
                            break;
                        case '>':
                            $tagStack->pcdata('&gt;');
                            break;
                        default:
                            $tagStack->pcdata('&amp;');
                            break;
                    }
                }
            }
        }

        if (!$inTag && $locator) {
            $locator->snapshotPosition();
        }
        // Lets the handler know that we are done parsing the document.
        $tagStack->exitRemainingTags();
        $handler->effectiveBodyTag($tagStack->effectiveBodyAttributes());
        $handler->endDoc();
    }


    /**
     * Decode an HTML entity.
     *
     * This method is public as it needs to be passed into Str::regexReplaceCallback().
     *
     * @param string $entity The full entity (including the & and the ;).
     * @return string A single unicode code-point as a string.
     */
    public function lookupEntity($entity)
    {
        $name = Str::toLowerCase(Str::substring($entity, Str::length($entity) - 1));
        if (array_key_exists($name, self::ENTITIES)) {
            return self::ENTITIES[$name];
        }
        $matches = [];
        if (Str::regexMatch(self::DECIMAL_ESCAPE_REGEX, $name, $matches)) {
            return chr((int)$matches[1]);
        }
        if (Str::regexMatch(self::HEX_ESCAPE_REGEX, $name, $matches)) {
            return chr(hexdec($matches[1]));
        }
        // If unable to decode, return the name.
        return $name;
    }

    /**
     * Remove null characters on the string.
     *
     * @param string $text The string to have the null characters removed.
     * @return string A string without null characters.
     * @private
     */
    private function stripNULs($text)
    {
        return Str::regexReplace(self::NULL_REGEX, '', $text);
    }

    /**
     * The plain text of a chunk of HTML CDATA which possibly containing.
     *
     * @param string $text A chunk of HTML CDATA. It must not start or end inside an HTML entity.
     * @return string The unescaped entities.
     */
    private function unescapeEntities($text)
    {
        return Str::regexReplaceCallback(self::ENTITY_REGEX, [$this, 'lookupEntity'], $text);
    }

    /**
     * Escape entities in RCDATA that can be escaped without changing the meaning.
     *
     * @param string $rcdata The RCDATA string we want to normalize.
     * @return string A normalized version of RCDATA.
     */
    private function normalizeRCData($rcdata)
    {
        $rcdata = Str::regexReplace(self::LOOSE_AMP_REGEX, '&amp;$1', $rcdata);
        $rcdata = Str::regexReplace(self::LT_REGEX, '&lt;', $rcdata);
        $rcdata = Str::regexReplace(self::GT_REGEX, '&gt;', $rcdata);

        return $rcdata;
    }
}
