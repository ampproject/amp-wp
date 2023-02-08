<?php

namespace AmpProject\Validator;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\LowerCaseTag;
use AmpProject\Html\Parser\ParsedAttribute;
use AmpProject\Html\Parser\ParsedTag;
use AmpProject\Validator\Spec\Error\DevModeOnly;
use AmpProject\Validator\Spec\Error\DisallowedAttr;
use AmpProject\Validator\Spec\Error\InvalidAttrValue;
use AmpProject\Validator\Spec\Error\MandatoryAttrMissing;

final class ValidatorRules
{
    /**
     * Regular expression to validate the value of a transformed attribute.
     *
     * @var string
     */
    const TRANSFORMED_VALUE_REGEX = '/^\\w+;v=(\\d+)$/';

    /**
     * Set of valid type identifiers.
     *
     * @var array<string>
     */
    const TYPE_IDENTIFIERS = [
        Attribute::AMP_EMOJI,
        Attribute::AMP_EMOJI_ALT,
        Attribute::AMP,
        Attribute::AMP4ADS_EMOJI,
        Attribute::AMP4ADS_EMOJI_ALT,
        Attribute::AMP4ADS,
        Attribute::AMP4EMAIL_EMOJI,
        Attribute::AMP4EMAIL_EMOJI_ALT,
        Attribute::AMP4EMAIL,
        Attribute::TRANSFORMED,
        Attribute::DATA_AMPDEVMODE,
        Attribute::DATA_CSS_STRICT
    ];

    /**
     * Set of type identifiers that are not mandatory.
     *
     * @var array<string>
     */
    const OPTIONAL_TYPE_IDENTIFIERS = [
        Attribute::TRANSFORMED,
        Attribute::DATA_AMPDEVMODE,
        Attribute::DATA_CSS_STRICT
    ];

    /**
     * AMP HTML format to validate against.
     *
     * @var string
     */
    private $htmlFormat;

    /**
     * Validator Specification to use.
     *
     * @var Spec
     */
    private $spec;

    /**
     * Creates a new instance and initializes it with the proper specification rules.
     *
     * @param string    $htmlFormat Optional. AMP HTML format to validate against.
     * @param Spec|null $spec       Optional. Validator specification to use.
     */
    public function __construct($htmlFormat = Format::AMP, Spec $spec = null)
    {
        $this->htmlFormat = $htmlFormat;
        $this->spec       = $spec instanceof Spec ? $spec : new Spec();
    }

    /**
     * Emits any validation errors which require a global view (mandatory tags, tags required by other tags, mandatory
     * alternatives).
     *
     * @param Context          $context          Validation context.
     * @param ValidationResult $validationResult Validation result.
     */
    public function maybeEmitGlobalTagValidationErrors(Context $context, ValidationResult $validationResult)
    {
        $this->maybeEmitMandatoryTagValidationErrors($context, $validationResult);
        $this->maybeEmitAlsoRequiresTagValidationErrors($context, $validationResult);
        $this->maybeEmitMandatoryAlternativesSatisfiedErrors($context, $validationResult);
        $this->maybeEmitDocSizeErrors($context, $validationResult);
        $this->maybeEmitCssLengthSpecErrors($context, $validationResult);
        $this->maybeEmitValueSetMismatchErrors($context, $validationResult);
    }

    /**
     * Emits errors for tags that are specified to be mandatory.
     *
     * @param Context          $context Validation context.
     * @param ValidationResult $validationResult Validation result.
     */
    private function maybeEmitMandatoryTagValidationErrors($context, $validationResult)
    {
        /*
        For (const tagSpecId of $this->mandatoryTagSpecs_) {
              const parsedTagSpec = $this->getByTagSpecId(tagSpecId);
              // Skip TagSpecs that aren't used for these type identifiers.
              if (!parsedTagSpec.isUsedForTypeIdentifiers(
                      context.getTypeIdentifiers())) {
                continue;
              }
        if (!context.getTagspecsValidated().hasOwnProperty(tagSpecId)) {
            const spec = parsedTagSpec.getSpec();
            context.addError(
                            generated.ValidationError.Code.MANDATORY_TAG_MISSING,
                            context.getFilePosition(),
                [getTagDescriptiveName(spec)], getTagSpecUrl(spec),
                            validationResult);
        }
        }
        */
    }

    /**
     * Emits errors for tags that specify that another tag is also required or a condition is required to be satisfied.
     *
     * Returns false iff context.Progress(result).complete.
     *
     * @param Context          $context Validation context.
     * @param ValidationResult $validationResult Validation result.
     */
    private function maybeEmitAlsoRequiresTagValidationErrors($context, $validationResult)
    {
        /*
        /** @type {!Array<number>} * /
            const tagspecsValidated =
                Object.keys($context->g$etTagspecsValidated()).map(Number);
            googArray.sort(tagspecsValidated);
            for (const tagSpecId of tagspecsValidated) {
            const parsedTagSpec = $this->getByTagSpecId(tagSpecId);
            // Skip TagSpecs that aren't used for these type identifiers.
            if (!parsedTagSpec.isUsedForTypeIdentifiers(
                    context.getTypeIdentifiers())) {
                continue;
            }
            for (const condition of parsedTagSpec.requires()) {
                if (!context.satisfiesCondition(condition)) {
                    context.addError(
                        generated.ValidationError.Code.TAG_REQUIRED_BY_MISSING,
                        context.getFilePosition(),
                        [
                            context.getRules().getInternedString(condition),
                            getTagDescriptiveName(parsedTagSpec.getSpec()),
                        ],
                        getTagSpecUrl(parsedTagSpec), validationResult);
                }
            }
              for (const condition of parsedTagSpec.excludes()) {
                if ($context->satisfiesCondition(condition)) {
                    context.addError(
                        generated.ValidationError.Code.TAG_EXCLUDED_BY_TAG,
                        context.getFilePosition(),
                        [
                            getTagDescriptiveName(parsedTagSpec.getSpec()),
                            context.getRules().getInternedString(condition),
                        ],
                        getTagSpecUrl(parsedTagSpec), validationResult);
                }
            }
              for (const tagspecId of parsedTagSpec.getAlsoRequiresTagWarning()) {
                if (!context.getTagspecsValidated().hasOwnProperty(tagspecId)) {
                    const alsoRequiresTagspec = $this->getByTagSpecId(tagspecId);
                    context.addWarning(
                        generated.ValidationError.Code.WARNING_TAG_REQUIRED_BY_MISSING,
                        context.getFilePosition(),
                        [
                            getTagDescriptiveName(alsoRequiresTagspec.getSpec()),
                            getTagDescriptiveName(parsedTagSpec.getSpec()),
                        ],
                        getTagSpecUrl(parsedTagSpec), validationResult);
                }
            }
            }

            const extensionsCtx = context.getExtensionsContext();
            const unusedRequired = extensionsCtx.unusedExtensionsRequired();
            for (const unusedExtensionName of unusedRequired) {
            context.addError(
                              generated.ValidationError.Code.EXTENSION_UNUSED, context.getFilePosition(),
                [unusedExtensionName],
                '', validationResult);
        }
        */
    }

      /**
       * Emits errors for tags that are specified as mandatory alternatives.
       *
       * Returns false iff context.Progress(result).complete.
       *
       * @param Context          $context Validation context.
       * @param ValidationResult $validationResult Validation result.
       */
    private function maybeEmitMandatoryAlternativesSatisfiedErrors($context, $validationResult)
    {
        /*
        Const satisfied = context.getMandatoryAlternativesSatisfied();
        /** @type {!Array<string>} * /
        const missing = [];
        const specUrlsByMissing = Object.create(null);
        for (const tagSpec of $this->rules_.tags) {
            if (tagSpec.mandatoryAlternatives === null ||
              !$this->isTagSpecCorrectHtmlFormat_(tagSpec)) {
              continue;
            }
            const alternative = tagSpec.mandatoryAlternatives;
            if (satisfied.indexOf(alternative) === -1) {
              const alternativeName = context.getRules().getInternedString(alternative);
              missing.push(alternativeName);
              specUrlsByMissing[alternativeName] = getTagSpecUrl(tagSpec);
            }
        }
        sortAndUniquify(missing);
        for (const tagMissing of missing) {
            context.addError(
                generated.ValidationError.Code.MANDATORY_TAG_MISSING,
                context.getFilePosition(),
                [tagMissing],
                specUrlsByMissing[tagMissing],
                validationResult
            );
        }
        */
    }

      /**
       * Emits errors for doc size limitations across entire document.
       *
       * @param Context          $context Validation context.
       * @param ValidationResult $validationResult Validation result.
       */
    private function maybeEmitDocSizeErrors($context, $validationResult)
    {
        /*
        Const parsedDocSpec = context.matchingDocSpec();
        if (parsedDocSpec !== null) {
          const bytesUsed = context.getDocByteSize();
          /** @type {!generated.DocSpec} * /
          const docSpec = parsedDocSpec.spec();
          if (docSpec.maxBytes !== -2 && bytesUsed > docSpec.maxBytes) {
              context.addError(
                  generated.ValidationError.Code.DOCUMENT_SIZE_LIMIT_EXCEEDED,
                  context.getFilePosition(),
                  [docSpec.maxBytes.toString(), bytesUsed.toString()],
                  docSpec.maxBytesSpecUrl, validationResult);
          }
        }
        */
    }

      /**
       * Emits errors for css size limitations across entire document.
       *
       * @param Context          $context Validation context.
       * @param ValidationResult $validationResult Validation result.
       */
    private function maybeEmitCssLengthSpecErrors($context, $validationResult)
    {
        /*
        Const bytesUsed =
          context.getInlineStyleByteSize() + context.getStyleTagByteSize();

        const parsedCssSpec = context.matchingDocCssSpec();
        if (parsedCssSpec !== null) {
          /** @type {!generated.DocCssSpec} * /
          const cssSpec = parsedCssSpec.spec();
          if (cssSpec.maxBytes !== -2 && bytesUsed > cssSpec.maxBytes) {
              if (cssSpec.maxBytesIsWarning) {
                  context.addWarning(
                      generated.ValidationError.Code
                      .STYLESHEET_AND_INLINE_STYLE_TOO_LONG,
                      context.getFilePosition(),
                      [bytesUsed.toString(), cssSpec.maxBytes.toString()],
                      cssSpec.maxBytesSpecUrl, validationResult);
              } else {
                  context.addError(
                      generated.ValidationError.Code
                      .STYLESHEET_AND_INLINE_STYLE_TOO_LONG,
                      context.getFilePosition(),
                      [bytesUsed.toString(), cssSpec.maxBytes.toString()],
                      cssSpec.maxBytesSpecUrl, validationResult);
              }
          }
        }
        */
    }

      /**
       * Emits errors when there is a ValueSetRequirement with no matching ValueSetProvision in the document.
       *
       * @param Context          $context Validation context.
       * @param ValidationResult $validationResult Validation result.
       */
    private function maybeEmitValueSetMismatchErrors($context, $validationResult)
    {
        /*
        Const providedKeys = context.valueSetsProvided();
        for (const [requiredKey, errors] of context.valueSetsRequired()) {
        if (!providedKeys.has(/** @type {string} * / (requiredKey))) {
          for (const error of errors)
            context.addBuiltError(error, validationResult);
        }
        */
    }

    /**
     * Validates the <html> tag for type identifiers.
     *
     * @param ParsedTag        $htmlTag          <html> tag to validate.
     * @param Context          $context          Validation context.
     * @param ValidationResult $validationResult Validation result.
     */
    public function validateHtmlTag(ParsedTag $htmlTag, Context $context, ValidationResult $validationResult)
    {
        switch ($this->htmlFormat) {
            case Format::AMP:
                $this->validateTypeIdentifiers(
                    $htmlTag->attributes(),
                    [
                        Attribute::AMP_EMOJI,
                        Attribute::AMP_EMOJI_ALT,
                        Attribute::AMP,
                        Attribute::TRANSFORMED,
                        Attribute::DATA_AMPDEVMODE
                    ],
                    $context,
                    $validationResult
                );
                break;
            case Format::AMP4ADS:
                $this->validateTypeIdentifiers(
                    $htmlTag->attributes(),
                    [
                        Attribute::AMP4ADS_EMOJI,
                        Attribute::AMP4ADS_EMOJI_ALT,
                        Attribute::AMP4ADS,
                        Attribute::DATA_AMPDEVMODE
                    ],
                    $context,
                    $validationResult
                );
                break;
            case Format::AMP4EMAIL:
                $this->validateTypeIdentifiers(
                    $htmlTag->attributes(),
                    [
                        Attribute::AMP4EMAIL_EMOJI,
                        Attribute::AMP4EMAIL_EMOJI_ALT,
                        Attribute::AMP4EMAIL,
                        Attribute::DATA_AMPDEVMODE,
                        Attribute::DATA_CSS_STRICT
                    ],
                    $context,
                    $validationResult
                );
                break;
        }
    }

    /**
     * @param ParsedAttribute[] $attributes        Array of parsed attributes.
     * @param string[]          $formatIdentifiers Array of format identifiers to validate against.
     * @param Context           $context           Validation context.
     * @param ValidationResult  $validationResult  Validation result.
     */
    private function validateTypeIdentifiers(
        $attributes,
        $formatIdentifiers,
        Context $context,
        ValidationResult $validationResult
    ) {
        $hasMandatoryTypeIdentifier = false;
        foreach ($attributes as $attribute) {
            // Verify this attribute is a type identifier. Other attributes are validated in validateAttributes().
            if ($this->isTypeIdentifier($attribute->name())) {
                // Verify this type identifier is allowed for this format.
                if (in_array($attribute->name(), $formatIdentifiers, true)) {
                    // Only add the type identifier once per representation. That is, both "⚡" and "amp", which
                    // represent the same type identifier.
                    $typeIdentifier = str_replace(
                        [Attribute::AMP_EMOJI_ALT, Attribute::AMP_EMOJI],
                        Attribute::AMP,
                        $attribute->name()
                    );
                    if (! in_array($typeIdentifier, $validationResult->getTypeIdentifiers(), true)) {
                        $validationResult->addTypeIdentifier($typeIdentifier);
                        $context->recordTypeIdentifier($typeIdentifier);
                    }

                    // Register the presence of a mandatory identifier (i.e. anything that is not optional).
                    if (! in_array($typeIdentifier, self::OPTIONAL_TYPE_IDENTIFIERS, true)) {
                        $hasMandatoryTypeIdentifier = true;
                    }

                    // The type identifier "transformed" has restrictions on its value.
                    // It must be \w+;v=\d+ (e.g. google;v=1).
                    if (($typeIdentifier === Attribute::TRANSFORMED) && ($attribute->value() !== '')) {
                        $matches = [];
                        if (preg_match(self::TRANSFORMED_VALUE_REGEX, $attribute->value(), $matches)) {
                            $validationResult->setTransformerVersion((int)$matches[1]);
                        } else {
                            $context->addError(
                                InvalidAttrValue::CODE,
                                $context->getFilePosition(),
                                [$attribute->name(), LowerCaseTag::HTML, $attribute->value()],
                                'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml#required-markup',
                                $validationResult
                            );
                        }
                    }
                    if ($typeIdentifier === Attribute::DATA_AMPDEVMODE) {
                        // We always emit an error for this type identifier, but it suppresses other errors later in the
                        // document. See https://github.com/ampproject/amphtml/issues/20974.
                        $context->addError(
                            DevModeOnly::CODE,
                            $context->getFilePosition(),
                            [],
                            '',
                            $validationResult
                        );
                    }
                } else {
                    $context->addError(
                        DisallowedAttr::CODE,
                        $context->getFilePosition(),
                        [$attribute->name(), LowerCaseTag::HTML],
                        'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml#required-markup',
                        $validationResult
                    );
                }
            }
        }
        if (! $hasMandatoryTypeIdentifier) {
            // Missing mandatory type identifier (any AMP variant but "transformed").
            $context->addError(
                MandatoryAttrMissing::CODE,
                $context->getFilePosition(),
                [$formatIdentifiers[0], LowerCaseTag::HTML],
                'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml#required-markup',
                $validationResult
            );
        }
    }

    /**
     * Check whether a given attribute is a valid type identifier.
     *
     * @param string $attribute Attribute to check.
     * @return bool Whether the provided attribute is a valid type identifier.
     */
    private function isTypeIdentifier($attribute)
    {
        return in_array($attribute, self::TYPE_IDENTIFIERS, true);
    }
}
