<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\DeclarationList;

use AmpProject\Attribute;
use AmpProject\Validator\Spec\DeclarationList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Declaration list class BasicDeclarations.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $alignContent
 * @property-read array $alignItems
 * @property-read array $alignSelf
 * @property-read array $all
 * @property-read array $animation
 * @property-read array $animationDelay
 * @property-read array $animationDirection
 * @property-read array $animationDuration
 * @property-read array $animationFillMode
 * @property-read array $animationIterationCount
 * @property-read array $animationName
 * @property-read array $animationPlayState
 * @property-read array $animationTimingFunction
 * @property-read array $aspectRatio
 * @property-read array $backfaceVisibility
 * @property-read array $background
 * @property-read array $backgroundAttachment
 * @property-read array $backgroundBlendMode
 * @property-read array $backgroundClip
 * @property-read array $backgroundColor
 * @property-read array $backgroundImage
 * @property-read array $backgroundOrigin
 * @property-read array $backgroundPosition
 * @property-read array $backgroundRepeat
 * @property-read array $backgroundSize
 * @property-read array $border
 * @property-read array $borderBottom
 * @property-read array $borderBottomColor
 * @property-read array $borderBottomLeftRadius
 * @property-read array $borderBottomRightRadius
 * @property-read array $borderBottomStyle
 * @property-read array $borderBottomWidth
 * @property-read array $borderCollapse
 * @property-read array $borderColor
 * @property-read array $borderImage
 * @property-read array $borderImageOutset
 * @property-read array $borderImageRepeat
 * @property-read array $borderImageSlice
 * @property-read array $borderImageSource
 * @property-read array $borderImageWidth
 * @property-read array $borderLeft
 * @property-read array $borderLeftColor
 * @property-read array $borderLeftStyle
 * @property-read array $borderLeftWidth
 * @property-read array $borderRadius
 * @property-read array $borderRight
 * @property-read array $borderRightColor
 * @property-read array $borderRightStyle
 * @property-read array $borderRightWidth
 * @property-read array $borderSpacing
 * @property-read array $borderStyle
 * @property-read array $borderTop
 * @property-read array $borderTopColor
 * @property-read array $borderTopLeftRadius
 * @property-read array $borderTopRightRadius
 * @property-read array $borderTopStyle
 * @property-read array $borderTopWidth
 * @property-read array $borderWidth
 * @property-read array $bottom
 * @property-read array $boxDecorationBreak
 * @property-read array $boxShadow
 * @property-read array $boxSizing
 * @property-read array $breakAfter
 * @property-read array $breakBefore
 * @property-read array $breakInside
 * @property-read array $captionSide
 * @property-read array $caretColor
 * @property-read array $clear
 * @property-read array $clip
 * @property-read array $color
 * @property-read array $columnCount
 * @property-read array $columnFill
 * @property-read array $columnGap
 * @property-read array $columnRule
 * @property-read array $columnRuleColor
 * @property-read array $columnRuleStyle
 * @property-read array $columnRuleWidth
 * @property-read array $columnSpan
 * @property-read array $columnWidth
 * @property-read array $columns
 * @property-read array $content
 * @property-read array $counterIncrement
 * @property-read array $counterReset
 * @property-read array $cursor
 * @property-read array $direction
 * @property-read array $display
 * @property-read array $emptyCells
 * @property-read array $filter
 * @property-read array $flex
 * @property-read array $flexBasis
 * @property-read array $flexDirection
 * @property-read array $flexFlow
 * @property-read array $flexGrow
 * @property-read array $flexShrink
 * @property-read array $flexWrap
 * @property-read array $float
 * @property-read array $font
 * @property-read array $fontFamily
 * @property-read array $fontFeatureSettings
 * @property-read array $fontKerning
 * @property-read array $fontLanguageOverride
 * @property-read array $fontSize
 * @property-read array $fontSizeAdjust
 * @property-read array $fontStretch
 * @property-read array $fontStyle
 * @property-read array $fontSynthesis
 * @property-read array $fontVariant
 * @property-read array $fontVariantAlternates
 * @property-read array $fontVariantCaps
 * @property-read array $fontVariantEastAsian
 * @property-read array $fontVariantLigatures
 * @property-read array $fontVariantNumeric
 * @property-read array $fontVariantPosition
 * @property-read array $fontWeight
 * @property-read array $grid
 * @property-read array $gridArea
 * @property-read array $gridAutoColumns
 * @property-read array $gridAutoFlow
 * @property-read array $gridAutoRows
 * @property-read array $gridColumn
 * @property-read array $gridColumnEnd
 * @property-read array $gridColumnGap
 * @property-read array $gridColumnStart
 * @property-read array $gridGap
 * @property-read array $gridRow
 * @property-read array $gridRowEnd
 * @property-read array $gridRowGap
 * @property-read array $gridRowStart
 * @property-read array $gridTemplate
 * @property-read array $gridTemplateAreas
 * @property-read array $gridTemplateColumns
 * @property-read array $gridTemplateRows
 * @property-read array $hangingPunctuation
 * @property-read array $height
 * @property-read array $hyphens
 * @property-read array $imageRendering
 * @property-read array $isolation
 * @property-read array $justifyContent
 * @property-read array $left
 * @property-read array $letterSpacing
 * @property-read array $lineBreak
 * @property-read array $lineHeight
 * @property-read array $listStyle
 * @property-read array $listStyleImage
 * @property-read array $listStylePosition
 * @property-read array $listStyleType
 * @property-read array $margin
 * @property-read array $marginBottom
 * @property-read array $marginLeft
 * @property-read array $marginRight
 * @property-read array $marginTop
 * @property-read array $maxHeight
 * @property-read array $maxWidth
 * @property-read array $minHeight
 * @property-read array $minWidth
 * @property-read array $mixBlendMode
 * @property-read array $objectFit
 * @property-read array $objectPosition
 * @property-read array $opacity
 * @property-read array $order
 * @property-read array $orphans
 * @property-read array $outline
 * @property-read array $outlineColor
 * @property-read array $outlineOffset
 * @property-read array $outlineStyle
 * @property-read array $outlineWidth
 * @property-read array $overflow
 * @property-read array $overflowWrap
 * @property-read array $overflowX
 * @property-read array $overflowY
 * @property-read array $padding
 * @property-read array $paddingBottom
 * @property-read array $paddingLeft
 * @property-read array $paddingRight
 * @property-read array $paddingTop
 * @property-read array $pageBreakAfter
 * @property-read array $pageBreakBefore
 * @property-read array $pageBreakInside
 * @property-read array $perspective
 * @property-read array $perspectiveOrigin
 * @property-read array $pointerEvents
 * @property-read array<array<string>> $position
 * @property-read array $quotes
 * @property-read array $resize
 * @property-read array $right
 * @property-read array $tabSize
 * @property-read array $tableLayout
 * @property-read array $textAlign
 * @property-read array $textAlignLast
 * @property-read array $textCombineUpright
 * @property-read array $textDecoration
 * @property-read array $textDecorationColor
 * @property-read array $textDecorationLine
 * @property-read array $textDecorationSkipInk
 * @property-read array $textDecorationStyle
 * @property-read array $textFillColor
 * @property-read array $textIndent
 * @property-read array $textJustify
 * @property-read array $textOrientation
 * @property-read array $textOverflow
 * @property-read array $textShadow
 * @property-read array $textStroke
 * @property-read array $textStrokeColor
 * @property-read array $textStrokeWidth
 * @property-read array $textTransform
 * @property-read array $textUnderlinePosition
 * @property-read array $top
 * @property-read array $transform
 * @property-read array $transformOrigin
 * @property-read array $transformStyle
 * @property-read array $transition
 * @property-read array $transitionDelay
 * @property-read array $transitionDuration
 * @property-read array $transitionProperty
 * @property-read array $transitionTimingFunction
 * @property-read array $unicodeBidi
 * @property-read array $userSelect
 * @property-read array $verticalAlign
 * @property-read array $visibility
 * @property-read array $whiteSpace
 * @property-read array $widows
 * @property-read array $width
 * @property-read array $wordBreak
 * @property-read array $wordSpacing
 * @property-read array $wordWrap
 * @property-read array $writingMode
 * @property-read array<string> $zIndex
 */
final class BasicDeclarations extends DeclarationList implements Identifiable
{
    /**
     * ID of the declaration list.
     *
     * @var string
     */
    const ID = 'BASIC_DECLARATIONS';

    /**
     * Array of declarations.
     *
     * @var array<array>
     */
    const DECLARATIONS = [
        Attribute::ALIGN_CONTENT => [],
        Attribute::ALIGN_ITEMS => [],
        Attribute::ALIGN_SELF => [],
        Attribute::ALL => [],
        Attribute::ANIMATION => [],
        Attribute::ANIMATION_DELAY => [],
        Attribute::ANIMATION_DIRECTION => [],
        Attribute::ANIMATION_DURATION => [],
        Attribute::ANIMATION_FILL_MODE => [],
        Attribute::ANIMATION_ITERATION_COUNT => [],
        Attribute::ANIMATION_NAME => [],
        Attribute::ANIMATION_PLAY_STATE => [],
        Attribute::ANIMATION_TIMING_FUNCTION => [],
        Attribute::ASPECT_RATIO => [],
        Attribute::BACKFACE_VISIBILITY => [],
        Attribute::BACKGROUND => [],
        Attribute::BACKGROUND_ATTACHMENT => [],
        Attribute::BACKGROUND_BLEND_MODE => [],
        Attribute::BACKGROUND_CLIP => [],
        Attribute::BACKGROUND_COLOR => [],
        Attribute::BACKGROUND_IMAGE => [],
        Attribute::BACKGROUND_ORIGIN => [],
        Attribute::BACKGROUND_POSITION => [],
        Attribute::BACKGROUND_REPEAT => [],
        Attribute::BACKGROUND_SIZE => [],
        Attribute::BORDER => [],
        Attribute::BORDER_BOTTOM => [],
        Attribute::BORDER_BOTTOM_COLOR => [],
        Attribute::BORDER_BOTTOM_LEFT_RADIUS => [],
        Attribute::BORDER_BOTTOM_RIGHT_RADIUS => [],
        Attribute::BORDER_BOTTOM_STYLE => [],
        Attribute::BORDER_BOTTOM_WIDTH => [],
        Attribute::BORDER_COLLAPSE => [],
        Attribute::BORDER_COLOR => [],
        Attribute::BORDER_IMAGE => [],
        Attribute::BORDER_IMAGE_OUTSET => [],
        Attribute::BORDER_IMAGE_REPEAT => [],
        Attribute::BORDER_IMAGE_SLICE => [],
        Attribute::BORDER_IMAGE_SOURCE => [],
        Attribute::BORDER_IMAGE_WIDTH => [],
        Attribute::BORDER_LEFT => [],
        Attribute::BORDER_LEFT_COLOR => [],
        Attribute::BORDER_LEFT_STYLE => [],
        Attribute::BORDER_LEFT_WIDTH => [],
        Attribute::BORDER_RADIUS => [],
        Attribute::BORDER_RIGHT => [],
        Attribute::BORDER_RIGHT_COLOR => [],
        Attribute::BORDER_RIGHT_STYLE => [],
        Attribute::BORDER_RIGHT_WIDTH => [],
        Attribute::BORDER_SPACING => [],
        Attribute::BORDER_STYLE => [],
        Attribute::BORDER_TOP => [],
        Attribute::BORDER_TOP_COLOR => [],
        Attribute::BORDER_TOP_LEFT_RADIUS => [],
        Attribute::BORDER_TOP_RIGHT_RADIUS => [],
        Attribute::BORDER_TOP_STYLE => [],
        Attribute::BORDER_TOP_WIDTH => [],
        Attribute::BORDER_WIDTH => [],
        Attribute::BOTTOM => [],
        Attribute::BOX_DECORATION_BREAK => [],
        Attribute::BOX_SHADOW => [],
        Attribute::BOX_SIZING => [],
        Attribute::BREAK_AFTER => [],
        Attribute::BREAK_BEFORE => [],
        Attribute::BREAK_INSIDE => [],
        Attribute::CAPTION_SIDE => [],
        Attribute::CARET_COLOR => [],
        Attribute::CLEAR => [],
        Attribute::CLIP => [],
        Attribute::COLOR => [],
        Attribute::COLUMN_COUNT => [],
        Attribute::COLUMN_FILL => [],
        Attribute::COLUMN_GAP => [],
        Attribute::COLUMN_RULE => [],
        Attribute::COLUMN_RULE_COLOR => [],
        Attribute::COLUMN_RULE_STYLE => [],
        Attribute::COLUMN_RULE_WIDTH => [],
        Attribute::COLUMN_SPAN => [],
        Attribute::COLUMN_WIDTH => [],
        Attribute::COLUMNS => [],
        Attribute::CONTENT => [],
        Attribute::COUNTER_INCREMENT => [],
        Attribute::COUNTER_RESET => [],
        Attribute::CURSOR => [],
        Attribute::DIRECTION => [],
        Attribute::DISPLAY => [],
        Attribute::EMPTY_CELLS => [],
        Attribute::FILTER => [],
        Attribute::FLEX => [],
        Attribute::FLEX_BASIS => [],
        Attribute::FLEX_DIRECTION => [],
        Attribute::FLEX_FLOW => [],
        Attribute::FLEX_GROW => [],
        Attribute::FLEX_SHRINK => [],
        Attribute::FLEX_WRAP => [],
        Attribute::FLOAT => [],
        Attribute::FONT => [],
        Attribute::FONT_FAMILY => [],
        Attribute::FONT_FEATURE_SETTINGS => [],
        Attribute::FONT_KERNING => [],
        Attribute::FONT_LANGUAGE_OVERRIDE => [],
        Attribute::FONT_SIZE => [],
        Attribute::FONT_SIZE_ADJUST => [],
        Attribute::FONT_STRETCH => [],
        Attribute::FONT_STYLE => [],
        Attribute::FONT_SYNTHESIS => [],
        Attribute::FONT_VARIANT => [],
        Attribute::FONT_VARIANT_ALTERNATES => [],
        Attribute::FONT_VARIANT_CAPS => [],
        Attribute::FONT_VARIANT_EAST_ASIAN => [],
        Attribute::FONT_VARIANT_LIGATURES => [],
        Attribute::FONT_VARIANT_NUMERIC => [],
        Attribute::FONT_VARIANT_POSITION => [],
        Attribute::FONT_WEIGHT => [],
        Attribute::GRID => [],
        Attribute::GRID_AREA => [],
        Attribute::GRID_AUTO_COLUMNS => [],
        Attribute::GRID_AUTO_FLOW => [],
        Attribute::GRID_AUTO_ROWS => [],
        Attribute::GRID_COLUMN => [],
        Attribute::GRID_COLUMN_END => [],
        Attribute::GRID_COLUMN_GAP => [],
        Attribute::GRID_COLUMN_START => [],
        Attribute::GRID_GAP => [],
        Attribute::GRID_ROW => [],
        Attribute::GRID_ROW_END => [],
        Attribute::GRID_ROW_GAP => [],
        Attribute::GRID_ROW_START => [],
        Attribute::GRID_TEMPLATE => [],
        Attribute::GRID_TEMPLATE_AREAS => [],
        Attribute::GRID_TEMPLATE_COLUMNS => [],
        Attribute::GRID_TEMPLATE_ROWS => [],
        Attribute::HANGING_PUNCTUATION => [],
        Attribute::HEIGHT => [],
        Attribute::HYPHENS => [],
        Attribute::IMAGE_RENDERING => [],
        Attribute::ISOLATION => [],
        Attribute::JUSTIFY_CONTENT => [],
        Attribute::LEFT => [],
        Attribute::LETTER_SPACING => [],
        Attribute::LINE_BREAK => [],
        Attribute::LINE_HEIGHT => [],
        Attribute::LIST_STYLE => [],
        Attribute::LIST_STYLE_IMAGE => [],
        Attribute::LIST_STYLE_POSITION => [],
        Attribute::LIST_STYLE_TYPE => [],
        Attribute::MARGIN => [],
        Attribute::MARGIN_BOTTOM => [],
        Attribute::MARGIN_LEFT => [],
        Attribute::MARGIN_RIGHT => [],
        Attribute::MARGIN_TOP => [],
        Attribute::MAX_HEIGHT => [],
        Attribute::MAX_WIDTH => [],
        Attribute::MIN_HEIGHT => [],
        Attribute::MIN_WIDTH => [],
        Attribute::MIX_BLEND_MODE => [],
        Attribute::OBJECT_FIT => [],
        Attribute::OBJECT_POSITION => [],
        Attribute::OPACITY => [],
        Attribute::ORDER => [],
        Attribute::ORPHANS => [],
        Attribute::OUTLINE => [],
        Attribute::OUTLINE_COLOR => [],
        Attribute::OUTLINE_OFFSET => [],
        Attribute::OUTLINE_STYLE => [],
        Attribute::OUTLINE_WIDTH => [],
        Attribute::OVERFLOW => [],
        Attribute::OVERFLOW_WRAP => [],
        Attribute::OVERFLOW_X => [],
        Attribute::OVERFLOW_Y => [],
        Attribute::PADDING => [],
        Attribute::PADDING_BOTTOM => [],
        Attribute::PADDING_LEFT => [],
        Attribute::PADDING_RIGHT => [],
        Attribute::PADDING_TOP => [],
        Attribute::PAGE_BREAK_AFTER => [],
        Attribute::PAGE_BREAK_BEFORE => [],
        Attribute::PAGE_BREAK_INSIDE => [],
        Attribute::PERSPECTIVE => [],
        Attribute::PERSPECTIVE_ORIGIN => [],
        Attribute::POINTER_EVENTS => [],
        Attribute::POSITION => [
            SpecRule::VALUE_CASEI => [
                'absolute',
                'inherit',
                'initial',
                'relative',
                'static',
            ],
        ],
        Attribute::QUOTES => [],
        Attribute::RESIZE => [],
        Attribute::RIGHT => [],
        Attribute::TAB_SIZE => [],
        Attribute::TABLE_LAYOUT => [],
        Attribute::TEXT_ALIGN => [],
        Attribute::TEXT_ALIGN_LAST => [],
        Attribute::TEXT_COMBINE_UPRIGHT => [],
        Attribute::TEXT_DECORATION => [],
        Attribute::TEXT_DECORATION_COLOR => [],
        Attribute::TEXT_DECORATION_LINE => [],
        Attribute::TEXT_DECORATION_SKIP_INK => [],
        Attribute::TEXT_DECORATION_STYLE => [],
        Attribute::TEXT_FILL_COLOR => [],
        Attribute::TEXT_INDENT => [],
        Attribute::TEXT_JUSTIFY => [],
        Attribute::TEXT_ORIENTATION => [],
        Attribute::TEXT_OVERFLOW => [],
        Attribute::TEXT_SHADOW => [],
        Attribute::TEXT_STROKE => [],
        Attribute::TEXT_STROKE_COLOR => [],
        Attribute::TEXT_STROKE_WIDTH => [],
        Attribute::TEXT_TRANSFORM => [],
        Attribute::TEXT_UNDERLINE_POSITION => [],
        Attribute::TOP => [],
        Attribute::TRANSFORM => [],
        Attribute::TRANSFORM_ORIGIN => [],
        Attribute::TRANSFORM_STYLE => [],
        Attribute::TRANSITION => [],
        Attribute::TRANSITION_DELAY => [],
        Attribute::TRANSITION_DURATION => [],
        Attribute::TRANSITION_PROPERTY => [],
        Attribute::TRANSITION_TIMING_FUNCTION => [],
        Attribute::UNICODE_BIDI => [],
        Attribute::USER_SELECT => [],
        Attribute::VERTICAL_ALIGN => [],
        Attribute::VISIBILITY => [],
        Attribute::WHITE_SPACE => [],
        Attribute::WIDOWS => [],
        Attribute::WIDTH => [],
        Attribute::WORD_BREAK => [],
        Attribute::WORD_SPACING => [],
        Attribute::WORD_WRAP => [],
        Attribute::WRITING_MODE => [],
        Attribute::Z_INDEX => [
            SpecRule::VALUE_REGEX_CASEI => 'auto|initial|inherit|[-+]?[0-9]+',
        ],
    ];
}
