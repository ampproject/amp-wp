<?php

namespace AmpProject\Validator;

use AmpProject\Format;
use AmpProject\Html\Parser\DocLocator;
use AmpProject\Html\Parser\HtmlSaxHandlerWithLocation;
use AmpProject\Html\Parser\ParsedAttribute;
use AmpProject\Html\Parser\ParsedTag;
use AmpProject\Html\UpperCaseTag as Tag;
use AmpProject\Str;
use AmpProject\Validator\Spec\Error\DisallowedManufacturedBody;
use AmpProject\Validator\Spec\Error\DuplicateAttribute;
use AmpProject\Validator\Spec\Error\DuplicateUniqueTag;
use AmpProject\Validator\Spec\Error\InvalidDoctypeHtml;

/**
 * Validation Handler which accepts callbacks from HTML Parser.
 *
 * @package ampproject/amp-toolbox
 */
final class ValidationHandler implements HtmlSaxHandlerWithLocation
{
    /**
     * AMP HTML format to validate against.
     *
     * @var string
     */
    private $htmlFormat;

    /**
     * Selection of validation rules to use.
     *
     * @var ValidatorRules
     */
    private $rules;

    /**
     * Validator specification to use.
     *
     * @var Spec
     */
    private $spec;

    /**
     * Validation context.
     *
     * @var Context
     */
    private $context;

    /**
     * Result of the validation.
     *
     * @var ValidationResult
     */
    private $validationResult;

    public function __construct($htmlFormat = Format::AMP, Spec $spec = null)
    {
        $this->htmlFormat       = $htmlFormat;
        $this->spec             = $spec instanceof Spec ? $spec : new Spec();
        $this->validationResult = new ValidationResult();
        $this->rules            = new ValidatorRules($htmlFormat, $spec);
        $this->context          = new Context($this->rules);
    }

    /**
     * Get the validation result.
     *
     * @return ValidationResult Validation result.
     */
    public function getResult()
    {
        return $this->validationResult;
    }

    /**
     * Handler called when the parser found a new tag.
     *
     * @param ParsedTag $tag New tag that was found.
     * @return void
     */
    public function startTag(ParsedTag $tag)
    {
        if ($tag->upperName() === Tag::HTML) {
            $this->context->getRules()->validateHtmlTag($tag, $this->context, $this->validationResult);
        }
        if ($tag->upperName() === Tag::_DOCTYPE) {
            $this->validateDocType($tag);
            // Even though validateDocType emits all necessary errors about the tag, we continue to process it further
            // (validateTag and such) so that we can record the tag was present and record it as the root pseudo element
            // for the document.
        }
        $maybeDuplicateAttributeName = $tag->hasDuplicateAttributes();
        if ($maybeDuplicateAttributeName !== null) {
            $this->context->addWarning(
                DuplicateAttribute::CODE,
                $this->context->getFilePosition(),
                [$tag->lowerName(), $maybeDuplicateAttributeName],
                '',
                $this->validationResult
            );
            $tag->dedupeAttributes();
        }

        if ($tag->upperName() === Tag::BODY) {
            $this->context->recordBodyTag($tag->attributes());
            $this->emitMissingExtensionErrors();
        }

        /*
        /** @type {ValidateTagResult} * /
        let resultForReferencePoint = {
        bestMatchTagSpec: null,
        validationResult: new generated.ValidationResult(),
        devModeSuppress: false,
        inlineStyleCssBytes: 0,
        };
        resultForReferencePoint.validationResult->status =
        generated.ValidationResult.Status.UNKNOWN;
        const referencePointMatcher =
        $this->context->getTagStack()->parentReferencePointMatcher();
        // We must match the reference point before the TagSpec, as otherwise we
        // will end up with "unexplained" attributes during tagspec matching
        // which the reference point takes care of.
        if (referencePointMatcher !== null) {
        resultForReferencePoint =
        referencePointMatcher.validateTag($tag, $this->context);
        }

        const resultForTag = validateTag(
        $tag, resultForReferencePoint.bestMatchTagSpec,
        $this->context);
        resultForTag.devModeSuppress =
        ShouldSuppressDevModeErrors($tag, $this->context);
        // Only merge in the reference point errors into the final result if the
        // tag otherwise passes one of the TagSpecs. Otherwise, we end up with
        // unnecessarily verbose errors.
        if (referencePointMatcher !== null &&
        resultForTag.validationResult->status ===
        generated.ValidationResult.Status.PASS &&
        !resultForTag.devModeSuppress) {
        $this->validationResult->mergeFrom(
        resultForReferencePoint.validationResult);
        }


        checkForReferencePointCollision(
        resultForReferencePoint.bestMatchTagSpec, resultForTag.bestMatchTagSpec,
        $this->context, resultForTag.validationResult);
        if (!resultForTag.devModeSuppress)
        $this->validationResult->mergeFrom(resultForTag.validationResult);

        $this->context->updateFromTagResults(
        $tag, resultForReferencePoint, resultForTag);
        */
    }

    /**
     * Handler called when the parser found a closing tag.
     *
     * @param ParsedTag $tag Closing tag that was found.
     * @return void
     */
    public function endTag(ParsedTag $tag)
    {
        // TODO: Implement endTag() method.
    }

    /**
     * Handler called when PCDATA is found.
     *
     * @param string $text The PCDATA that was found.
     * @return void
     */
    public function pcdata($text)
    {
        // TODO: Implement pcdata() method.
    }

    /**
     * Handler called when RCDATA is found.
     *
     * @param string $text The RCDATA that was found.
     * @return void
     */
    public function rcdata($text)
    {
        // TODO: Implement rcdata() method.
    }

    /**
     * Handler called when CDATA is found.
     *
     * @param string $text The CDATA that was found.
     * @return void
     */
    public function cdata($text)
    {
        // TODO: Implement cdata() method.
    }

    /**
     * Handler called when the parser is starting to parse the document.
     *
     * @return void
     */
    public function startDoc()
    {
        $this->validationResult = new ValidationResult();
    }

    /**
     * Handler called when the parsing is done.
     *
     * @return void
     */
    public function endDoc()
    {
        $this->rules->maybeEmitGlobalTagValidationErrors($this->context, $this->validationResult);

        if ($this->validationResult->getStatus()->equals(ValidationStatus::UNKNOWN())) {
            $this->validationResult->setStatus(ValidationStatus::PASS());
        }

        // As some errors can be inserted out of order, sort errors at the end based on their line / column numbers.
        $this->validationResult->getErrors()->sortByPosition();
    }

    /**
     * Callback for informing that the parser is manufacturing a <body> tag not actually found on the page. This will be
     * followed by a startTag() with the actual body tag in question.
     *
     * @return void
     */
    public function markManufacturedBody()
    {
        $this->context->addError(
            DisallowedManufacturedBody::CODE,
            $this->context->getFilePosition(),
            [],
            '',
            $this->validationResult
        );
    }

    /**
     * HTML5 defines how parsers treat documents with multiple body tags: they merge the attributes from the later ones
     * into the first one. Therefore, just before the parser sends the endDoc event, it will also send this event which
     * will provide the attributes from the effective body tag to the client (the handler).
     *
     * @param array<ParsedAttribute> $attributes Array of parsed attributes.
     * @return void
     */
    public function effectiveBodyTag($attributes)
    {
        $encounteredAttributes = $this->context->getEncounteredBodyAttributes();

        // If we never recorded a body tag with attributes, it was manufactured, in which case we've already logged an
        // error for that. Doing more here would be confusing.
        if ($encounteredAttributes === null) {
            return;
        }

        // So now we compare the attributes from the tag that we encountered (HtmlParser sent us a startTag() event for
        // it earlier) with the attributes from the effective body tag that we're just receiving now, which contains all
        // attributes on body tags within the doc. It's correct to think of this synthetic tag simply as a concatenation
        // - there is in general no elimination of duplicate attributes or overriding behavior. Thus, if the second body
        // tag has any attribute this will result in an error.
        $differenceSeen = count($attributes) !== count($encounteredAttributes);
        if (! $differenceSeen) {
            $attributesCount = count($attributes);
            for ($index = 0; $index < $attributesCount; $index++) {
                if ($attributes[$index] !== $encounteredAttributes[$index]) {
                    $differenceSeen = true;
                    break;
                }
            }
        }

        if (! $differenceSeen) {
            return;
        }

        $this->context->addError(
            DuplicateUniqueTag::CODE,
            $this->context->getEncounteredBodyFilePosition(),
            [Tag::BODY],
            '',
            $this->validationResult
        );
    }

    /**
     * Called prior to parsing a document, that is, before startTag().
     *
     * @param DocLocator $locator A locator instance which provides access to the line/column information while SAX
     *                            events are being received by the handler.
     * @return void
     */
    public function setDocLocator(DocLocator $locator)
    {
        $this->context->setDocLocator($locator);
    }

    /**
     * While parsing the document HEAD, we may accumulate errors which depend on seeing later extension <script> tags.
     */
    private function emitMissingExtensionErrors()
    {
        foreach ($this->context->getExtensionsContext()->getMissingExtensionErrors() as $missingExtensionError) {
            $this->context->recordError($missingExtensionError, $this->validationResult);
        }
    }

    /**
     * Validate the <!doctype> tag.
     *
     * Currently, the HTML parser considers Doctype to be another HTML tag, which is not technically accurate. There is
     * special handling for doctype in Javascript which applies to all AMP formats, as this is strict handling for all
     * HTML in general. Specifically "attributes" are not allowed, even things like `data-foo`.
     *
     * @param ParsedTag $tag The <!doctype> tag to validate.
     */
    private function validateDocType(ParsedTag $tag)
    {
        $attributes = $tag->attributes();

        // <!doctype html> - OK
        if (count($attributes) === 1 && $attributes[0]->name() === 'html') {
            return;
        }

        // <!doctype html lang=...> OK
        // This is technically invalid. The 'correct' way to do this is to emit the
        // lang attribute on the `<html>` tag. However, we observe a number of
        // websites incorrectly emitting `lang` as part of doctype, so this specific
        // attribute is allowed to avoid breaking existing pages.
        if (
            count($attributes) === 2
            &&
            (
                ($attributes[0]->name() === 'html' && $attributes[1]->name() === 'lang')
                ||
                ($attributes[0]->name() === 'lang' && $attributes[1]->name() === 'html')
            )
        ) {
            return;
        }

        if (count($attributes) !== 1 || $attributes[0]->name() !== 'html') {
            $this->context->addError(
                InvalidDoctypeHtml::CODE,
                $this->context->getFilePosition(),
                [],
                'https://amp.dev/documentation/guides-and-tutorials/start/create/basic_markup/',
                $this->validationResult
            );
        }
    }

    /**
     * Validates the provided `ParsedHtmlTag` with respect to the tag specifications in the validator's rules, returning
     * a `ValidationResult` with errors for this tag and a PASS or FAIL status. At least one specification must
     * validate, or the result will have status `FAIL`.
     * Also passes back a reference to the tag spec which matched, if a match was found.
     * Context is not mutated; instead, pending mutations are stored in the return value, and are merged only if the tag
     * spec is applied (pending some reference point stuff).
     *
     * @param ParsedTag          $encounteredTag          Tag that was encountered.
     * @param ParsedTagSpec|null $bestMatchReferencePoint Reference point for the best match.
     * @param Context            $context                 Validation context.
     * @return ValidateTagResult
     */
    private function validateTag($encounteredTag, $bestMatchReferencePoint, $context)
    {
        $tagSpecDispatch = $context->getRules()->dispatchForTagName($encounteredTag->upperName());
        // Filter TagSpecDispatch.AllTagSpecs by type identifiers.
        $filteredTagSpecs = [];
        if ($tagSpecDispatch !== null) {
            foreach ($tagSpecDispatch->allTagSpecs() as $tagSpecId) {
                $parsedTagSpec = $context->getRules()->getByTagSpecId($tagSpecId);
                // Keep TagSpecs that are used for these type identifiers.
                if ($parsedTagSpec->isUsedForTypeIdentifiers($context->getTypeIdentifiers())) {
                    $filteredTagSpecs[] = $parsedTagSpec;
                }
            }
        }

        // If there are no dispatch keys matching the tag name, ex: tag name is "foo", set a disallowed tag error.
        if (
            $tagSpecDispatch === null
            ||
            (! $tagSpecDispatch->hasDispatchKeys() && count($filteredTagSpecs) === 0)
        ) {
            $result  = new ValidationResult();
            $specUrl = '';
            // Special case the spec_url for font tags to be slightly more useful.
            if ($encounteredTag->upperName() === Tag::FONT) {
                $specUrl = $context->getRules()->getStylesSpecUrl();
            }
            $context->addError(
                ErrorCode::DISALLOWED_TAG,
                $context->getFilePosition(),
                [$encounteredTag->lowerName()],
                $specUrl,
                $result
            );

            return new ValidateTagResult($result);
        }

        // At this point, we have dispatch keys, tag specs, or both.
        // The strategy is to look for a matching dispatch key first. A matching dispatch key does not guarantee that
        // the dispatched tag spec will also match. If we find a matching dispatch key, we immediately return the result
        // for that tag spec, success or fail.
        // If we don't find a matching dispatch key, we must try all the tag specs to see if any of them match. If there
        // are no tag specs, we want to return a `GENERAL_DISALLOWED_TAG` error.
        // Calling `hasDispatchKeys()` here is only an optimization to skip the loop over encountered attributes in the
        // case where we have no dispatches.
        if ($tagSpecDispatch->hasDispatchKeys()) {
            foreach ($encounteredTag->attributes() as $attribute) {
                $tagSpecIds  = $tagSpecDispatch->matchingDispatchKey(
                    $attribute->name(),
                    // Attribute values are case-sensitive by default, but we match dispatch keys in a case-insensitive
                    // manner and then validate using whatever the tag spec requests.
                    Str::toLowerCase($attribute->value()),
                    $context->getTagStack()->parentTagName()
                );
                $bestAttempt = new ValidateTagResult(new ValidationResult());
                $bestAttempt->getValidationResult()->setStatus(ValidationStatus::UNKNOWN());
                foreach ($tagSpecIds as $tagSpecId) {
                    $parsedTagSpec = $context->getRules()->getByTagSpecId($tagSpecId);
                    // Skip tag specs that aren't used for these type identifiers.
                    if (! $parsedTagSpec->isUsedForTypeIdentifiers($context->getTypeIdentifiers())) {
                        continue;
                    }
                    $attempt = $this->validateTagAgainstSpec(
                        $parsedTagSpec,
                        $bestMatchReferencePoint,
                        $context,
                        $encounteredTag
                    );
                    if (
                        $context->getRules()->betterValidationResultThan(
                            $attempt->getValidationResult(),
                            $bestAttempt->getValidationResult()
                        )
                    ) {
                        $bestAttempt                   = $attempt;
                        $bestAttempt->bestMatchTagSpec = $parsedTagSpec;
                        // Exit early on success.
                        if ($bestAttempt->getValidationResult()->getStatus()->equals(ValidationStatus::PASS())) {
                            return $bestAttempt;
                        }
                    }
                }
                if (! $bestAttempt->getValidationResult()->getStatus()->equals(ValidationStatus::UNKNOWN())) {
                    return $bestAttempt;
                }
            }
        }

        // None of the dispatch tag specs matched and passed. If there are no non-dispatch tag specs, consider this a
        // 'generally' disallowed tag, which gives an error that reads "tag foo is disallowed except in specific forms".
        if (count($filteredTagSpecs) === 0) {
            $result = new ValidationResult();
            if ($encounteredTag->upperName() === Tag::SCRIPT) {
                // Special case for `<script>` tags to produce better error messages.
                $context->addError(
                    ErrorCode::DISALLOWED_SCRIPT_TAG,
                    $context->getFilePosition(),
                    [],
                    $context->getRules()->getScriptSpecUrl(),
                    $result
                );
            } else {
                $context->addError(
                    ErrorCode::GENERAL_DISALLOWED_TAG,
                    $context->getFilePosition(),
                    [$encounteredTag->lowerName()],
                    '',
                    $result
                );
            }

            return new ValidateTagResult($result);
        }

        // Validate against all remaining tag specs. Each tag spec will produce a different set of errors. Even if none
        // of them match, we only want to return errors from a single tag spec, not all of them. We keep around the
        // 'best' attempt until we have found a matching tag spec or have tried them all.
        $bestAttempt = new ValidateTagResult(new ValidationResult());
        $bestAttempt->getValidationResult()->setStatus(ValidationStatus::UNKNOWN());
        foreach ($filteredTagSpecs as $parsedTagSpec) {
            $attempt = $this->validateTagAgainstSpec(
                $parsedTagSpec,
                $bestMatchReferencePoint,
                $context,
                $encounteredTag
            );
            if (
                $context->getRules()->betterValidationResultThan(
                    $attempt->getValidationResult(),
                    $bestAttempt->getValidationResult()
                )
            ) {
                $bestAttempt                   = $attempt;
                $bestAttempt->bestMatchTagSpec = $parsedTagSpec;
                // Exit early.
                if ($bestAttempt->getValidationResult()->getStatus()->equals(ValidationStatus::PASS())) {
                    return $bestAttempt;
                }
            }
        }

        return $bestAttempt;
    }
}
