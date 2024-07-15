<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Section;

use AmpProject\Exception\InvalidErrorCode;
use AmpProject\Exception\InvalidListName;
use AmpProject\Validator\Spec;
use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\IterableSection;
use AmpProject\Validator\Spec\Iteration;

/**
 * The Errors section gives access to all the known validation errors.
 *
 * @package ampproject/amp-toolbox
 *
 * @method Error parentCurrent()
 */
final class Errors implements IterableSection
{
    use Iteration {
        Iteration::current as parentCurrent;
    }

    /**
     * Mapping of error code to error implementation.
     *
     * @var array<string>
     */
    const ERRORS = [
        Error\UnknownCode::CODE => Error\UnknownCode::class,
        Error\InvalidDoctypeHtml::CODE => Error\InvalidDoctypeHtml::class,
        Error\MandatoryTagMissing::CODE => Error\MandatoryTagMissing::class,
        Error\TagRequiredByMissing::CODE => Error\TagRequiredByMissing::class,
        Error\WarningTagRequiredByMissing::CODE => Error\WarningTagRequiredByMissing::class,
        Error\TagExcludedByTag::CODE => Error\TagExcludedByTag::class,
        Error\WarningExtensionUnused::CODE => Error\WarningExtensionUnused::class,
        Error\ExtensionUnused::CODE => Error\ExtensionUnused::class,
        Error\WarningExtensionDeprecatedVersion::CODE => Error\WarningExtensionDeprecatedVersion::class,
        Error\InvalidExtensionVersion::CODE => Error\InvalidExtensionVersion::class,
        Error\InvalidExtensionPath::CODE => Error\InvalidExtensionPath::class,
        Error\IncorrectScriptReleaseVersion::CODE => Error\IncorrectScriptReleaseVersion::class,
        Error\DisallowedAmpDomain::CODE => Error\DisallowedAmpDomain::class,
        Error\NonLtsScriptAfterLts::CODE => Error\NonLtsScriptAfterLts::class,
        Error\LtsScriptAfterNonLts::CODE => Error\LtsScriptAfterNonLts::class,
        Error\AttrRequiredButMissing::CODE => Error\AttrRequiredButMissing::class,
        Error\DisallowedTag::CODE => Error\DisallowedTag::class,
        Error\GeneralDisallowedTag::CODE => Error\GeneralDisallowedTag::class,
        Error\DisallowedScriptTag::CODE => Error\DisallowedScriptTag::class,
        Error\DisallowedAttr::CODE => Error\DisallowedAttr::class,
        Error\DisallowedStyleAttr::CODE => Error\DisallowedStyleAttr::class,
        Error\InvalidAttrValue::CODE => Error\InvalidAttrValue::class,
        Error\DuplicateAttribute::CODE => Error\DuplicateAttribute::class,
        Error\AttrValueRequiredByLayout::CODE => Error\AttrValueRequiredByLayout::class,
        Error\MissingLayoutAttributes::CODE => Error\MissingLayoutAttributes::class,
        Error\ImpliedLayoutInvalid::CODE => Error\ImpliedLayoutInvalid::class,
        Error\SpecifiedLayoutInvalid::CODE => Error\SpecifiedLayoutInvalid::class,
        Error\MandatoryAttrMissing::CODE => Error\MandatoryAttrMissing::class,
        Error\InconsistentUnitsForWidthAndHeight::CODE => Error\InconsistentUnitsForWidthAndHeight::class,
        Error\StylesheetTooLong::CODE => Error\StylesheetTooLong::class,
        Error\StylesheetAndInlineStyleTooLong::CODE => Error\StylesheetAndInlineStyleTooLong::class,
        Error\InlineStyleTooLong::CODE => Error\InlineStyleTooLong::class,
        Error\InlineScriptTooLong::CODE => Error\InlineScriptTooLong::class,
        Error\MandatoryCdataMissingOrIncorrect::CODE => Error\MandatoryCdataMissingOrIncorrect::class,
        Error\CdataViolatesDenylist::CODE => Error\CdataViolatesDenylist::class,
        Error\NonWhitespaceCdataEncountered::CODE => Error\NonWhitespaceCdataEncountered::class,
        Error\InvalidJsonCdata::CODE => Error\InvalidJsonCdata::class,
        Error\DisallowedPropertyInAttrValue::CODE => Error\DisallowedPropertyInAttrValue::class,
        Error\InvalidPropertyValueInAttrValue::CODE => Error\InvalidPropertyValueInAttrValue::class,
        Error\DuplicateDimension::CODE => Error\DuplicateDimension::class,
        Error\MissingUrl::CODE => Error\MissingUrl::class,
        Error\InvalidUrl::CODE => Error\InvalidUrl::class,
        Error\InvalidUrlProtocol::CODE => Error\InvalidUrlProtocol::class,
        Error\DisallowedDomain::CODE => Error\DisallowedDomain::class,
        Error\DisallowedRelativeUrl::CODE => Error\DisallowedRelativeUrl::class,
        Error\MandatoryPropertyMissingFromAttrValue::CODE => Error\MandatoryPropertyMissingFromAttrValue::class,
        Error\UnescapedTemplateInAttrValue::CODE => Error\UnescapedTemplateInAttrValue::class,
        Error\TemplatePartialInAttrValue::CODE => Error\TemplatePartialInAttrValue::class,
        Error\DeprecatedTag::CODE => Error\DeprecatedTag::class,
        Error\DeprecatedAttr::CODE => Error\DeprecatedAttr::class,
        Error\MutuallyExclusiveAttrs::CODE => Error\MutuallyExclusiveAttrs::class,
        Error\MandatoryOneofAttrMissing::CODE => Error\MandatoryOneofAttrMissing::class,
        Error\MandatoryAnyofAttrMissing::CODE => Error\MandatoryAnyofAttrMissing::class,
        Error\WrongParentTag::CODE => Error\WrongParentTag::class,
        Error\DisallowedTagAncestor::CODE => Error\DisallowedTagAncestor::class,
        Error\MandatoryTagAncestor::CODE => Error\MandatoryTagAncestor::class,
        Error\MandatoryTagAncestorWithHint::CODE => Error\MandatoryTagAncestorWithHint::class,
        Error\DuplicateUniqueTag::CODE => Error\DuplicateUniqueTag::class,
        Error\DuplicateUniqueTagWarning::CODE => Error\DuplicateUniqueTagWarning::class,
        Error\TemplateInAttrName::CODE => Error\TemplateInAttrName::class,
        Error\AttrDisallowedByImpliedLayout::CODE => Error\AttrDisallowedByImpliedLayout::class,
        Error\AttrDisallowedBySpecifiedLayout::CODE => Error\AttrDisallowedBySpecifiedLayout::class,
        Error\IncorrectNumChildTags::CODE => Error\IncorrectNumChildTags::class,
        Error\IncorrectMinNumChildTags::CODE => Error\IncorrectMinNumChildTags::class,
        Error\TagNotAllowedToHaveSiblings::CODE => Error\TagNotAllowedToHaveSiblings::class,
        Error\MandatoryLastChildTag::CODE => Error\MandatoryLastChildTag::class,
        Error\DisallowedChildTagName::CODE => Error\DisallowedChildTagName::class,
        Error\DisallowedFirstChildTagName::CODE => Error\DisallowedFirstChildTagName::class,
        Error\DisallowedManufacturedBody::CODE => Error\DisallowedManufacturedBody::class,
        Error\ChildTagDoesNotSatisfyReferencePoint::CODE => Error\ChildTagDoesNotSatisfyReferencePoint::class,
        Error\ChildTagDoesNotSatisfyReferencePointSingular::CODE => Error\ChildTagDoesNotSatisfyReferencePointSingular::class,
        Error\MandatoryReferencePointMissing::CODE => Error\MandatoryReferencePointMissing::class,
        Error\DuplicateReferencePoint::CODE => Error\DuplicateReferencePoint::class,
        Error\TagReferencePointConflict::CODE => Error\TagReferencePointConflict::class,
        Error\BaseTagMustPreceedAllUrls::CODE => Error\BaseTagMustPreceedAllUrls::class,
        Error\MissingRequiredExtension::CODE => Error\MissingRequiredExtension::class,
        Error\AttrMissingRequiredExtension::CODE => Error\AttrMissingRequiredExtension::class,
        Error\DocumentTooComplex::CODE => Error\DocumentTooComplex::class,
        Error\InvalidUtf8::CODE => Error\InvalidUtf8::class,
        Error\CssSyntaxInvalidAtRule::CODE => Error\CssSyntaxInvalidAtRule::class,
        Error\CssSyntaxStrayTrailingBackslash::CODE => Error\CssSyntaxStrayTrailingBackslash::class,
        Error\CssSyntaxUnterminatedComment::CODE => Error\CssSyntaxUnterminatedComment::class,
        Error\CssSyntaxUnterminatedString::CODE => Error\CssSyntaxUnterminatedString::class,
        Error\CssSyntaxBadUrl::CODE => Error\CssSyntaxBadUrl::class,
        Error\CssSyntaxEofInPreludeOfQualifiedRule::CODE => Error\CssSyntaxEofInPreludeOfQualifiedRule::class,
        Error\CssSyntaxInvalidProperty::CODE => Error\CssSyntaxInvalidProperty::class,
        Error\CssSyntaxInvalidPropertyNolist::CODE => Error\CssSyntaxInvalidPropertyNolist::class,
        Error\CssSyntaxQualifiedRuleHasNoDeclarations::CODE => Error\CssSyntaxQualifiedRuleHasNoDeclarations::class,
        Error\CssSyntaxDisallowedQualifiedRuleMustBeInsideKeyframe::CODE => Error\CssSyntaxDisallowedQualifiedRuleMustBeInsideKeyframe::class,
        Error\CssSyntaxDisallowedKeyframeInsideKeyframe::CODE => Error\CssSyntaxDisallowedKeyframeInsideKeyframe::class,
        Error\CssSyntaxInvalidDeclaration::CODE => Error\CssSyntaxInvalidDeclaration::class,
        Error\CssSyntaxIncompleteDeclaration::CODE => Error\CssSyntaxIncompleteDeclaration::class,
        Error\CssSyntaxErrorInPseudoSelector::CODE => Error\CssSyntaxErrorInPseudoSelector::class,
        Error\CssSyntaxMissingSelector::CODE => Error\CssSyntaxMissingSelector::class,
        Error\CssSyntaxNotASelectorStart::CODE => Error\CssSyntaxNotASelectorStart::class,
        Error\CssSyntaxUnparsedInputRemainsInSelector::CODE => Error\CssSyntaxUnparsedInputRemainsInSelector::class,
        Error\CssSyntaxMissingUrl::CODE => Error\CssSyntaxMissingUrl::class,
        Error\CssSyntaxInvalidUrl::CODE => Error\CssSyntaxInvalidUrl::class,
        Error\CssSyntaxInvalidUrlProtocol::CODE => Error\CssSyntaxInvalidUrlProtocol::class,
        Error\CssSyntaxDisallowedDomain::CODE => Error\CssSyntaxDisallowedDomain::class,
        Error\CssSyntaxDisallowedRelativeUrl::CODE => Error\CssSyntaxDisallowedRelativeUrl::class,
        Error\CssSyntaxInvalidAttrSelector::CODE => Error\CssSyntaxInvalidAttrSelector::class,
        Error\CssSyntaxDisallowedPropertyValue::CODE => Error\CssSyntaxDisallowedPropertyValue::class,
        Error\CssSyntaxDisallowedPropertyValueWithHint::CODE => Error\CssSyntaxDisallowedPropertyValueWithHint::class,
        Error\CssSyntaxDisallowedImportant::CODE => Error\CssSyntaxDisallowedImportant::class,
        Error\CssSyntaxPropertyDisallowedWithinAtRule::CODE => Error\CssSyntaxPropertyDisallowedWithinAtRule::class,
        Error\CssSyntaxPropertyDisallowedTogetherWith::CODE => Error\CssSyntaxPropertyDisallowedTogetherWith::class,
        Error\CssSyntaxPropertyRequiresQualification::CODE => Error\CssSyntaxPropertyRequiresQualification::class,
        Error\CssSyntaxMalformedMediaQuery::CODE => Error\CssSyntaxMalformedMediaQuery::class,
        Error\CssSyntaxDisallowedMediaType::CODE => Error\CssSyntaxDisallowedMediaType::class,
        Error\CssSyntaxDisallowedMediaFeature::CODE => Error\CssSyntaxDisallowedMediaFeature::class,
        Error\CssSyntaxDisallowedAttrSelector::CODE => Error\CssSyntaxDisallowedAttrSelector::class,
        Error\CssSyntaxDisallowedPseudoClass::CODE => Error\CssSyntaxDisallowedPseudoClass::class,
        Error\CssSyntaxDisallowedPseudoElement::CODE => Error\CssSyntaxDisallowedPseudoElement::class,
        Error\CssExcessivelyNested::CODE => Error\CssExcessivelyNested::class,
        Error\DocumentSizeLimitExceeded::CODE => Error\DocumentSizeLimitExceeded::class,
        Error\ValueSetMismatch::CODE => Error\ValueSetMismatch::class,
        Error\DevModeOnly::CODE => Error\DevModeOnly::class,
        Error\AmpEmailMissingStrictCssAttr::CODE => Error\AmpEmailMissingStrictCssAttr::class,
    ];

    /**
     * Cache of instantiated Error objects.
     *
     * @var array<Spec\Error>
     */
    private $errors = [];

    /**
     * Get a specific error.
     *
     * @param string $errorCode Code of the error to get.
     * @return Spec\Error Error with the given error code.
     * @throws InvalidErrorCode If an invalid error code is requested.
     */
    public function get($errorCode)
    {
        if (!array_key_exists($errorCode, self::ERRORS)) {
            throw InvalidErrorCode::forErrorCode($errorCode);
        }

        if (array_key_exists($errorCode, $this->errors)) {
            return $this->errors[$errorCode];
        }

        $errorClassName = self::ERRORS[$errorCode];

        /** @var Spec\Error $error */
        $error = new $errorClassName();

        $this->errors[$errorCode] = $error;

        return $error;
    }

    /**
     * Get the list of available keys.
     *
     * @return array<string> Array of available keys.
     */
    public function getAvailableKeys()
    {
        return array_keys(self::ERRORS);
    }

    /**
     * Find the instantiated object for the current key.
     *
     * This should use its own caching mechanism as needed.
     *
     * Ideally, current() should be overridden as well to provide the correct object type-hint.
     *
     * @param string $key Key to retrieve the instantiated object for.
     * @return object Instantiated object for the current key.
     */
    public function findByKey($key)
    {
        return $this->get($key);
    }

    /**
     * Return the current iterable object.
     *
     * @return Error Error object.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->parentCurrent();
    }
}
