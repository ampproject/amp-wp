<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Section;

use AmpProject\Exception\InvalidListName;
use AmpProject\Validator\Spec;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\IterableSection;
use AmpProject\Validator\Spec\Iteration;

/**
 * The AttributeLists section provides lists of allowed attributes.
 *
 * @package ampproject/amp-toolbox
 *
 * @method AttributeList parentCurrent()
 */
final class AttributeLists implements IterableSection
{
    use Iteration {
        Iteration::current as parentCurrent;
    }

    /**
     * Mapping of attribute list ID to attribute list implementation.
     *
     * @var array<string>
     */
    const ATTRIBUTE_LISTS = [
        AttributeList\CommonLinkAttrs::ID => AttributeList\CommonLinkAttrs::class,
        AttributeList\PooolAccessAttrs::ID => AttributeList\PooolAccessAttrs::class,
        AttributeList\CiteAttr::ID => AttributeList\CiteAttr::class,
        AttributeList\ClickAttributions::ID => AttributeList\ClickAttributions::class,
        AttributeList\PrivateClickMeasurementAttributes::ID => AttributeList\PrivateClickMeasurementAttributes::class,
        AttributeList\TrackAttrsNoSubtitles::ID => AttributeList\TrackAttrsNoSubtitles::class,
        AttributeList\TrackAttrsSubtitles::ID => AttributeList\TrackAttrsSubtitles::class,
        AttributeList\InputCommonAttr::ID => AttributeList\InputCommonAttr::class,
        AttributeList\AmphtmlEngineAttrs::ID => AttributeList\AmphtmlEngineAttrs::class,
        AttributeList\AmphtmlModuleEngineAttrs::ID => AttributeList\AmphtmlModuleEngineAttrs::class,
        AttributeList\AmphtmlNomoduleEngineAttrs::ID => AttributeList\AmphtmlNomoduleEngineAttrs::class,
        AttributeList\MandatorySrcOrSrcset::ID => AttributeList\MandatorySrcOrSrcset::class,
        AttributeList\MandatorySrcAmp4email::ID => AttributeList\MandatorySrcAmp4email::class,
        AttributeList\OptionalSrcAmp4email::ID => AttributeList\OptionalSrcAmp4email::class,
        AttributeList\ExtendedAmpGlobal::ID => AttributeList\ExtendedAmpGlobal::class,
        AttributeList\AmpLayoutAttrs::ID => AttributeList\AmpLayoutAttrs::class,
        AttributeList\NonceAttr::ID => AttributeList\NonceAttr::class,
        AttributeList\CommonExtensionAttrs::ID => AttributeList\CommonExtensionAttrs::class,
        AttributeList\MandatoryIdAttr::ID => AttributeList\MandatoryIdAttr::class,
        AttributeList\FormNameAttr::ID => AttributeList\FormNameAttr::class,
        AttributeList\NameAttr::ID => AttributeList\NameAttr::class,
        AttributeList\MandatoryNameAttr::ID => AttributeList\MandatoryNameAttr::class,
        AttributeList\GlobalAttrs::ID => AttributeList\GlobalAttrs::class,
        AttributeList\SvgConditionalProcessingAttributes::ID => AttributeList\SvgConditionalProcessingAttributes::class,
        AttributeList\SvgCoreAttributes::ID => AttributeList\SvgCoreAttributes::class,
        AttributeList\SvgFilterPrimitiveAttributes::ID => AttributeList\SvgFilterPrimitiveAttributes::class,
        AttributeList\SvgPresentationAttributes::ID => AttributeList\SvgPresentationAttributes::class,
        AttributeList\SvgTransferFunctionAttributes::ID => AttributeList\SvgTransferFunctionAttributes::class,
        AttributeList\SvgXlinkAttributes::ID => AttributeList\SvgXlinkAttributes::class,
        AttributeList\SvgStyleAttr::ID => AttributeList\SvgStyleAttr::class,
        AttributeList\AmpAudioCommon::ID => AttributeList\AmpAudioCommon::class,
        AttributeList\AmpBaseCarouselCommon::ID => AttributeList\AmpBaseCarouselCommon::class,
        AttributeList\AmpCarouselCommon::ID => AttributeList\AmpCarouselCommon::class,
        AttributeList\AmpDatePickerCommonAttributes::ID => AttributeList\AmpDatePickerCommonAttributes::class,
        AttributeList\AmpDatePickerRangeTypeAttributes::ID => AttributeList\AmpDatePickerRangeTypeAttributes::class,
        AttributeList\AmpDatePickerSingleTypeAttributes::ID => AttributeList\AmpDatePickerSingleTypeAttributes::class,
        AttributeList\AmpDatePickerStaticModeAttributes::ID => AttributeList\AmpDatePickerStaticModeAttributes::class,
        AttributeList\AmpDatePickerOverlayModeAttributes::ID => AttributeList\AmpDatePickerOverlayModeAttributes::class,
        AttributeList\AmpFacebook::ID => AttributeList\AmpFacebook::class,
        AttributeList\AmpFacebookStrict::ID => AttributeList\AmpFacebookStrict::class,
        AttributeList\AmpInputmaskCommonAttr::ID => AttributeList\AmpInputmaskCommonAttr::class,
        AttributeList\LightboxableElements::ID => AttributeList\LightboxableElements::class,
        AttributeList\AmpMegaphoneCommon::ID => AttributeList\AmpMegaphoneCommon::class,
        AttributeList\AmpNestedMenuActions::ID => AttributeList\AmpNestedMenuActions::class,
        AttributeList\InteractiveSharedConfigsAttrs::ID => AttributeList\InteractiveSharedConfigsAttrs::class,
        AttributeList\InteractiveOptionsTextAttrs::ID => AttributeList\InteractiveOptionsTextAttrs::class,
        AttributeList\InteractiveOptionsConfettiAttrs::ID => AttributeList\InteractiveOptionsConfettiAttrs::class,
        AttributeList\InteractiveOptionsResultsCategoryAttrs::ID => AttributeList\InteractiveOptionsResultsCategoryAttrs::class,
        AttributeList\InteractiveOptionsImgAttrs::ID => AttributeList\InteractiveOptionsImgAttrs::class,
        AttributeList\AmpStreamGalleryCommon::ID => AttributeList\AmpStreamGalleryCommon::class,
        AttributeList\AmpVideoIframeCommon::ID => AttributeList\AmpVideoIframeCommon::class,
        AttributeList\AmpVideoCommon::ID => AttributeList\AmpVideoCommon::class,
    ];

    /**
     * Cache of instantiated AttributeList objects.
     *
     * @var array<Spec\AttributeList>
     */
    private $attributeLists = [];

    /**
     * Get a specific attribute list.
     *
     * @param string $attributeListName Name of the attribute list to get.
     * @return Spec\AttributeList Attribute list with the given attribute list name.
     * @throws InvalidListName If an invalid attribute list name is requested.
     */
    public function get($attributeListName)
    {
        if (!array_key_exists($attributeListName, self::ATTRIBUTE_LISTS)) {
            throw InvalidListName::forAttributeList($attributeListName);
        }

        if (array_key_exists($attributeListName, $this->attributeLists)) {
            return $this->attributeLists[$attributeListName];
        }

        $attributeListClassName = self::ATTRIBUTE_LISTS[$attributeListName];

        /** @var Spec\AttributeList $attributeList */
        $attributeList = new $attributeListClassName();

        $this->attributeLists[$attributeListName] = $attributeList;

        return $attributeList;
    }

    /**
     * Get the list of available keys.
     *
     * @return array<string> Array of available keys.
     */
    public function getAvailableKeys()
    {
        return array_keys(self::ATTRIBUTE_LISTS);
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
     * @return AttributeList Attribute list object.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->parentCurrent();
    }
}
