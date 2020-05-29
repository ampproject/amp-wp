<?php

namespace AmpProject\Optimizer\Tests;

/**
 * Compare HTML markup without failing on whitespace or alignment.
 *
 * @package ampproject/optimizer
 */
trait MarkupComparison
{

    /**
     * Assert markup is equal.
     *
     * @param string $expected Expected markup.
     * @param string $actual   Actual markup.
     */
    protected function assertEqualMarkup($expected, $actual)
    {
        $actual   = preg_replace('/\s+/', ' ', $actual);
        $expected = preg_replace('/\s+/', ' ', $expected);
        $actual   = preg_replace('/(?<=>)\s+(?=<)/', '', trim($actual));
        $expected = preg_replace('/(?<=>)\s+(?=<)/', '', trim($expected));

        $normalize_attributes = static function( $token ) {
            if ( preg_match( '#^(<[a-z0-9-]+)(\s[^>]+)#is', $token, $matches ) ) {
                $token = $matches[1];

                $attrs = array_map( 'trim', array_filter( preg_split( '#(\s+[^"\'\s=]+(?:=(?:"[^"]+"|\'[^\']+\'|[^"\'\s]+))?)#', $matches[2], -1, PREG_SPLIT_DELIM_CAPTURE ) ) );
                sort( $attrs );
                $attrs = array_map(
                    static function( $attr ) {
                        return ' ' . $attr;
                    },
                    $attrs
                );
            }
            return $token;
        };

        $expected_tokens = array_map( $normalize_attributes, array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ) );
        $actual_tokens   = array_map( $normalize_attributes, array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) ) );

        $this->assertEquals( $expected_tokens, $actual_tokens );
    }

    /**
     * Assert markup is similar, disregarding differences that are inconsequential for functionality.
     *
     * @param string $expected Expected markup.
     * @param string $actual   Actual markup.
     */
    protected function assertSimilarMarkup($expected, $actual)
    {
        $actual   = preg_replace('/=([\'"]){2}/', '', $actual);
        $expected = preg_replace('/=([\'"]){2}/', '', $expected);
        $actual   = preg_replace('/<!doctype/i', '<!DOCTYPE', $actual);
        $expected = preg_replace('/<!doctype/i', '<!DOCTYPE', $expected);
        $actual   = preg_replace('/(\s+[a-zA-Z-_]+)=(?!")([a-zA-Z-_.]+)/', '\1="\2"', $actual);
        $expected = preg_replace('/(\s+[a-zA-Z-_]+)=(?!")([a-zA-Z-_.]+)/', '\1="\2"', $expected);
        $actual   = preg_replace('/>\s*{\s*}\s*</', '>{}<', $actual);
        $expected = preg_replace('/>\s*{\s*}\s*</', '>{}<', $expected);

        $this->assertEqualMarkup($expected, $actual);
    }
}
