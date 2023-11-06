<?php

namespace AmpProject\Validator;

use AmpProject\Extension;

/**
 * The extensions context keeps track of the extensions that the validator has seen, as well as which have been used,
 * which are required to be used, etc.
 *
 * @package ampproject/amp-toolbox
 */
final class ExtensionsContext
{
    /**
     * Extensions that have been already loaded.
     *
     * This tracks the valid <script> tags loading amp extensions which were seen in the document's head. Most
     * extensions are also added to $extensionsUnusedRequired when encountered in the head. When a tag is seen later in
     * the document which makes use of an extension, that extension is recorded in $extensionsUsed.
     *
     * @var string[]
     */
    private $extensionsLoaded = [
        // AMP-AD is exempted to not require the respective extension javascript file for historical reasons. We still
        // need to mark that the extension is used if we see the tags.
        Extension::AD,
    ];

    /**
     * Extensions recorded as being used.
     *
     * @var string[]
     */
    private $extensionsUsed = [];

    /**
     * Extensions marked as required but not yet recorded as being used.
     *
     * @var string[]
     */
    private $extensionsUnusedRequired = [];

    /**
     * Validation errors due to missing required extensions.
     *
     * @var ValidationError[]
     */
    private $extensionMissingErrors = [];

    /**
     * Check if the given extension has already been loaded.
     *
     * Note that this assumes that all extensions will be loaded in the document earlier than their first usage. This
     * is
     * true for <amp-foo> tags, since the extension must be loaded in the head and <amp-foo> tags are not supported in
     * the head as per HTML spec.
     *
     * @param string $extension Extension to check.
     * @return bool Whether the extension is loaded.
     */
    private function isExtensionLoaded($extension)
    {
        return in_array($extension, $this->extensionsLoaded, true);
    }

    /*
    /**
     * Record a possible error to report once we have collected all
     * extensions in the document. If the given extension is missing,
     * then report the given error.
     *
     * @param ParsedTagSpec $parsedTagSpec
     * @param FilePosition  $filePosition
     * /
    private function recordFutureErrorsIfMissing(ParsedTagSpec $parsedTagSpec, FilePosition $filePosition)
    {
        const tagSpec = parsedTagSpec.getSpec();
        for (const requiredExtension of tagSpec.requiresExtension) {
        if (!this.isExtensionLoaded(requiredExtension)) {
            const error = new generated.ValidationError();
            error.severity = generated.ValidationError.Severity.ERROR;
            error.code = generated.ValidationError.Code.MISSING_REQUIRED_EXTENSION;
            error.params = [getTagDescriptiveName(tagSpec), requiredExtension];
            error.line = lineCol.getLine();
            error.col = lineCol.getCol();
            error.specUrl = getTagSpecUrl(tagSpec);

            this.extensionMissingErrors_.push(
                {missingExtension: requiredExtension, maybeError: error});
            }
        }
    }
    */

    /**
     * Returns a list of errors accrued while processing the <head> for tags requiring an extension which was not found.
     *
     * @return ValidationError[]
     */
    public function getMissingExtensionErrors()
    {
        $result = [];
        foreach ($this->extensionMissingErrors as $extensionMissingError) {
            if (! $this->isExtensionLoaded($extensionMissingError->missingExtension)) {
                $result[] = $extensionMissingError->maybeError;
            }
        }

        return $result;
    }

    /**
     * Records extensions that are used within the document.
     *
     * @param string[] $extensions Extensions to record as being used.
     */
    private function recordUsedExtensions($extensions)
    {
        foreach ($extensions as $extension) {
            $this->extensionsUsed[] = $extension;
        }
    }

    /**
     * Returns a list of unused extensions which produce validation errors when unused.
     *
     * @return string[]
     */
    private function getUnusedExtensionsRequired()
    {
        $result = [];
        foreach ($this->extensionsUnusedRequired as $extension) {
            if (! in_array($extension, $this->extensionsUsed, true)) {
                $result[] = $extension;
            }
        }
        sort($result);

        return $result;
    }

    /**
     * Update ExtensionContext state when we encounter an amp extension or tag using an extension.
     *
     * @param ValidateTagResult $validateTagResult Tag result to update from.
     */
    private function updateFromTagResult(ValidateTagResult $validateTagResult)
    {
        /*
        If (result.bestMatchTagSpec === null) {
            return;
        }
        const parsedTagSpec = result.bestMatchTagSpec;
        const tagSpec = parsedTagSpec.getSpec();

        // Keep track of which extensions are loaded.
        if (tagSpec.extensionSpec !== null) {
            const {extensionSpec} = tagSpec;
          // This is an always present field if extension spec is set.
          const extensionName = /** @type{string} * / (extensionSpec.name);

          // Record that we have encountered an extension 'load' tag. This will
          // look like <script custom-element=amp-foo ...> or similar.
          this.extensionsLoaded_[extensionName] = true;
          switch (extensionSpec.requiresUsage) {
              case generated.ExtensionSpec.ExtensionUsageRequirement
                   .EXEMPTED:  // Fallthrough intended:
              case generated.ExtensionSpec.ExtensionUsageRequirement.NONE:
                  // This extension does not have usage demonstrated by a tag, for
                  // example: amp-dynamic-css-classes
                  break;
              case generated.ExtensionSpec.ExtensionUsageRequirement.ERROR:
                  // TODO(powdercloud): Make enum proto defaults work in generated
                  // javascript.
              default:  // Default is error
                  // Record that a loaded extension indicates a new requirement:
                  // namely that some tag must make use of this extension.
                  this.extensionsUnusedRequired_.push(extensionName);
                  break;
          }
        }

        // Record presence of a tag, such as <amp-foo> which requires the usage
        // of an amp extension.
        this.recordUsedExtensions(tagSpec.requiresExtension);
        */
    }
}
