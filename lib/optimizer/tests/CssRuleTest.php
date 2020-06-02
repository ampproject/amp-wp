<?php

namespace AmpProject\Optimizer;

use PHPUnit\Framework\TestCase;

/**
 * Tests for AmpProject\Optimizer\CssRule.
 *
 * @covers  CssRule
 * @package ampproject/optimizer
 */
class CssRuleTest extends TestCase
{

    public function dataCssRuleOutput()
    {
        // ID will always be set to i-amp-42 for the tests.

        return [
            'no arguments'       => [null, null, ''],
            'selector with null' => ['h1', null, ''],
            'null with property' => [null, 'color:red', ''],

            'empty strings'              => ['', '', ''],
            'selector with empty string' => ['h1', '', ''],
            'empty string with property' => ['', 'color:red', ''],

            'single string selector, single string property' => ['h1', 'color:red', 'h1{color:red}'],
            'single array selector, single array property' => [['h1'], ['color:red'], 'h1{color:red}'],

            'single string selector, multiple properties in string' => ['h1', 'color:red;background-color:green;font-weight:bold', 'h1{background-color:green;color:red;font-weight:bold}'],
            'single string selector, multiple properties in array' => ['h1', ['color:red', 'background-color:green', 'font-weight:bold'], 'h1{background-color:green;color:red;font-weight:bold}'],
            'single array selector, multiple properties in string' => [['h1'], 'color:red;background-color:green;font-weight:bold', 'h1{background-color:green;color:red;font-weight:bold}'],
            'single array selector, multiple properties in array' => [['h1'], ['color:red', 'background-color:green', 'font-weight:bold'], 'h1{background-color:green;color:red;font-weight:bold}'],

            'multiple selectors in string, single string property' => ['h1,h2,h3', 'color:red', 'h1,h2,h3{color:red}'],
            'multiple selectors in array, single string property' => [['h1', 'h2', 'h3'], 'color:red', 'h1,h2,h3{color:red}'],
            'multiple selectors in string, single array property' => ['h1,h2,h3', ['color:red'], 'h1,h2,h3{color:red}'],
            'multiple selectors in array, single array property' => [['h1', 'h2', 'h3'], ['color:red'], 'h1,h2,h3{color:red}'],

            'multiple selectors in string, multiple properties in string' => ['h1,h2,h3', 'color:red;background-color:green;font-weight:bold', 'h1,h2,h3{background-color:green;color:red;font-weight:bold}'],
            'multiple selectors in array, multiple properties in string' => [['h1', 'h2', 'h3'], 'color:red;background-color:green;font-weight:bold', 'h1,h2,h3{background-color:green;color:red;font-weight:bold}'],
            'multiple selectors in string, multiple properties in array' => ['h1,h2,h3', ['color:red', 'background-color:green', 'font-weight:bold'], 'h1,h2,h3{background-color:green;color:red;font-weight:bold}'],
            'multiple selectors in array, multiple properties in array' => [['h1', 'h2', 'h3'], ['color:red', 'background-color:green', 'font-weight:bold'], 'h1,h2,h3{background-color:green;color:red;font-weight:bold}'],

            'id in selector' => [ '#__ID__', 'color:red', '#i-amp-42{color:red}'],
            'multiple ids in selector' => [ '#__ID__.__ID__-button', 'color:red', '#i-amp-42.i-amp-42-button{color:red}'],

            'normalizes selectors and properties' => [
                " \t\t  ,  #some-id  \n  \n\n >  \t .some-class  \t \n  .another-class  \t \n +  element  ~ another-element  \n   \t    ,  ,,   #another-id \n  .with-class  \n \t  ,  ",
                " \t\t  ;  color \n \t : \n \t\n  red  ;;;   ; \n ; \t \n   background-color:white;;  \t  \n ; ",
                '#another-id .with-class,#some-id>.some-class .another-class+element~another-element{background-color:white;color:red}'
            ],
        ];
    }

    /**
     * Test CssRule output.
     *
     * @dataProvider dataCssRuleOutput
     *
     * @covers CssRule::__construct()
     * @covers CssRule::applyID()
     * @covers CssRule::getCss()
     */
    public function testCssRuleOutput($selectors, $properties, $expected)
    {
        $cssRule = new CssRule($selectors, $properties);
        $css = $cssRule
            ->applyID('i-amp-42')
            ->getCss();
        $this->assertEquals($expected, $css);
    }

    public function dataCssRuleMerging()
    {
        return [
            'different single property' => [['.class1', 'color:red'], ['.class2', 'color:blue'], false],
            'same single property'      => [['.class1', 'color:red'], ['.class2', 'color:red'], true, ['', ['.class1', '.class2'], ['color:red']]],

            'different multiple properties' => [['.class1', 'color:red;background-color:green'], ['.class2', 'color:red;background-color:blue'], false],
            'same multiple properties'      => [['.class1', 'color:red;background-color:green'], ['.class2', 'color:red;background-color:green'], true, ['', ['.class1', '.class2'], ['background-color:green', 'color:red']]],

            'multiple selectors with different single property' => [['.class1,.class2', 'color:red'], ['.class3,.class4', 'color:blue'], false],
            'multiple selectors with same single property'      => [['.class1,.class2', 'color:red'], ['.class3,.class4', 'color:red'], true, ['', ['.class1', '.class2', '.class3', '.class4'], ['color:red']]],

            'multiple selectors with different multiple properties' => [['.class1,.class2', 'color:red;background-color:green'], ['.class3,.class4', 'color:red;background-color:blue'], false],
            'multiple selectors with same multiple properties'      => [['.class1,.class2', 'color:red;background-color:green'], ['.class3,.class4', 'color:red;background-color:green'], true, ['', ['.class1', '.class2', '.class3', '.class4'], ['background-color:green', 'color:red']]],

            'overlap in selectors' => [['.class1,.class2', 'color:red'], ['.class2,.class3', 'color:red'], true, ['', ['.class1', '.class2', '.class3'], ['color:red']]],

            'same media query - different single property' => [['@media not all and (min-width: 650px)', '.class1', 'color:red'], ['@media not all and (min-width: 650px)', '.class2', 'color:blue'], false],
            'same media query - same single property'      => [['@media not all and (min-width: 650px)', '.class1', 'color:red'], ['@media not all and (min-width: 650px)', '.class2', 'color:red'], true, ['@media not all and (min-width: 650px)', ['.class1', '.class2'], ['color:red']]],

            'same media query - different multiple properties' => [['@media not all and (min-width: 650px)', '.class1', 'color:red;background-color:green'], ['@media not all and (min-width: 650px)', '.class2', 'color:red;background-color:blue'], false],
            'same media query - same multiple properties'      => [['@media not all and (min-width: 650px)', '.class1', 'color:red;background-color:green'], ['@media not all and (min-width: 650px)', '.class2', 'color:red;background-color:green'], true, ['@media not all and (min-width: 650px)', ['.class1', '.class2'], ['background-color:green', 'color:red']]],

            'same media query - multiple selectors with different single property' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['@media not all and (min-width: 650px)', '.class3,.class4', 'color:blue'], false],
            'same media query - multiple selectors with same single property'      => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['@media not all and (min-width: 650px)', '.class3,.class4', 'color:red'], true, ['@media not all and (min-width: 650px)', ['.class1', '.class2', '.class3', '.class4'], ['color:red']]],

            'same media query - multiple selectors with different multiple properties' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red;background-color:green'], ['@media not all and (min-width: 650px)', '.class3,.class4', 'color:red;background-color:blue'], false],
            'same media query - multiple selectors with same multiple properties'      => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red;background-color:green'], ['@media not all and (min-width: 650px)', '.class3,.class4', 'color:red;background-color:green'], true, ['@media not all and (min-width: 650px)', ['.class1', '.class2', '.class3', '.class4'], ['background-color:green', 'color:red']]],

            'same media query - overlap in selectors' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['@media not all and (min-width: 650px)', '.class2,.class3', 'color:red'], true, ['@media not all and (min-width: 650px)', ['.class1', '.class2', '.class3'], ['color:red']]],

            'different media query - different single property' => [['@media not all and (min-width: 650px)', '.class1', 'color:red'], ['@media not all and (min-width: 950px)', '.class2', 'color:blue'], false],
            'different media query - same single property'      => [['@media not all and (min-width: 650px)', '.class1', 'color:red'], ['@media not all and (min-width: 950px)', '.class2', 'color:red'], false],

            'different media query - different multiple properties' => [['@media not all and (min-width: 650px)', '.class1', 'color:red;background-color:green'], ['@media not all and (min-width: 950px)', '.class2', 'color:red;background-color:blue'], false],
            'different media query - same multiple properties'      => [['@media not all and (min-width: 650px)', '.class1', 'color:red;background-color:green'], ['@media not all and (min-width: 950px)', '.class2', 'color:red;background-color:green'], false],

            'different media query - multiple selectors with different single property' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['@media not all and (min-width: 950px)', '.class3,.class4', 'color:blue'], false],
            'different media query - multiple selectors with same single property'      => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['@media not all and (min-width: 950px)', '.class3,.class4', 'color:red'], false],

            'different media query - multiple selectors with different multiple properties' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red;background-color:green'], ['@media not all and (min-width: 950px)', '.class3,.class4', 'color:red;background-color:blue'], false],
            'different media query - multiple selectors with same multiple properties'      => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red;background-color:green'], ['@media not all and (min-width: 950px)', '.class3,.class4', 'color:red;background-color:green'], false],

            'different media query - overlap in selectors' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['@media not all and (min-width: 950px)', '.class2,.class3', 'color:red'], false],

            'single-sided media query - different single property' => [['@media not all and (min-width: 650px)', '.class1', 'color:red'], ['.class2', 'color:blue'], false],
            'single-sided media query - same single property'      => [['@media not all and (min-width: 650px)', '.class1', 'color:red'], ['.class2', 'color:red'], false],

            'single-sided media query - different multiple properties' => [['@media not all and (min-width: 650px)', '.class1', 'color:red;background-color:green'], ['.class2', 'color:red;background-color:blue'], false],
            'single-sided media query - same multiple properties'      => [['@media not all and (min-width: 650px)', '.class1', 'color:red;background-color:green'], ['.class2', 'color:red;background-color:green'], false],

            'single-sided media query - multiple selectors with different single property' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['.class3,.class4', 'color:blue'], false],
            'single-sided media query - multiple selectors with same single property'      => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['.class3,.class4', 'color:red'], false],

            'single-sided media query - multiple selectors with different multiple properties' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red;background-color:green'], ['.class3,.class4', 'color:red;background-color:blue'], false],
            'single-sided media query - multiple selectors with same multiple properties'      => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red;background-color:green'], ['.class3,.class4', 'color:red;background-color:green'], false],

            'single-sided media query - overlap in selectors' => [['@media not all and (min-width: 650px)', '.class1,.class2', 'color:red'], ['.class2,.class3', 'color:red'], false],
        ];
    }

    /**
     * Test CssRule merging.
     *
     * @dataProvider dataCssRuleMerging
     *
     * @covers CssRule::__construct()
     * @covers CssRule::withMediaQuery()
     * @covers CssRule::canBeMerged()
     * @covers CssRule::mergeWith()
     */
    public function testCssRulesCanBeMerged($ruleAValues, $ruleBValues, $expectedCanBeMerged, $expectedMergedRuleValues = [])
    {
        $ruleA = count($ruleAValues) > 2
            ? CssRule::withMediaQuery($ruleAValues[0], $ruleAValues[1], $ruleAValues[2])
            : new CssRule($ruleAValues[0], $ruleAValues[1]);

        $ruleB = count($ruleBValues) > 2
            ? CssRule::withMediaQuery($ruleBValues[0], $ruleBValues[1], $ruleBValues[2])
            : new CssRule($ruleBValues[0], $ruleBValues[1]);

        $this->assertEquals($expectedCanBeMerged, $ruleA->canBeMerged($ruleB));
        $this->assertEquals($expectedCanBeMerged, $ruleB->canBeMerged($ruleA));

        if ($expectedCanBeMerged) {
            $mergedRuleFromA = $ruleA->mergeWith($ruleB);
            $mergedRuleFromB = $ruleB->mergeWith($ruleA);

            $this->assertEquals($expectedMergedRuleValues[0], $mergedRuleFromA->getMediaQuery());
            $this->assertEquals($expectedMergedRuleValues[0], $mergedRuleFromB->getMediaQuery());

            $this->assertEquals($expectedMergedRuleValues[1], $mergedRuleFromA->getSelectors());
            $this->assertEquals($expectedMergedRuleValues[1], $mergedRuleFromB->getSelectors());

            $this->assertEquals($expectedMergedRuleValues[2], $mergedRuleFromA->getProperties());
            $this->assertEquals($expectedMergedRuleValues[2], $mergedRuleFromB->getProperties());
        }
    }
}
