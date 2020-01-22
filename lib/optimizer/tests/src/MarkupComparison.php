<?php

namespace Amp\Optimizer\Tests;

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

        $this->assertEquals(
            array_filter(preg_split('#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE)),
            array_filter(preg_split('#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE))
        );
    }

    /**
     * Assert markup is similar, disregarding differences that are inconsequential for functionality.
     *
     * @param string $expected Expected markup.
     * @param string $actual   Actual markup.
     */
    protected function assertSimilarMarkup($expected, $actual)
    {
        $actual   = str_replace('=""', '', $actual);
        $expected = str_replace('=""', '', $expected);
        $actual   = preg_replace('/(\s+[a-zA-Z-_]+)=(?!")([a-zA-Z-_.]+)/', '\1="\2"', $actual);
        $expected = preg_replace('/(\s+[a-zA-Z-_]+)=(?!")([a-zA-Z-_.]+)/', '\1="\2"', $expected);

        $this->assertEqualMarkup($expected, $actual);
    }
}
