<?php

namespace AmpProject;

/**
 * Interface with constants for the different layouts.
 *
 * @package ampproject/amp-toolbox
 */
interface Layout
{
    const NODISPLAY    = 'nodisplay';
    const FIXED        = 'fixed';
    const RESPONSIVE   = 'responsive';
    const FIXED_HEIGHT = 'fixed-height';
    const FILL         = 'fill';
    const CONTAINER    = 'container';
    const FLEX_ITEM    = 'flex-item';
    const FLUID        = 'fluid';
    const INTRINSIC    = 'intrinsic';

    const FROM_SPEC = [
        1 => self::NODISPLAY,
        2 => self::FIXED,
        3 => self::FIXED_HEIGHT,
        4 => self::RESPONSIVE,
        5 => self::CONTAINER,
        6 => self::FILL,
        7 => self::FLEX_ITEM,
        8 => self::FLUID,
        9 => self::INTRINSIC,
    ];

    const TO_SPEC = [
        self::NODISPLAY => 1,
        self::FIXED => 2,
        self::FIXED_HEIGHT => 3,
        self::RESPONSIVE => 4,
        self::CONTAINER => 5,
        self::FILL => 6,
        self::FLEX_ITEM => 7,
        self::FLUID => 8,
        self::INTRINSIC => 9,
    ];

    const SIZE_DEFINED_LAYOUTS = [
        self::FIXED,
        self::FIXED_HEIGHT,
        self::RESPONSIVE,
        self::FILL,
        self::FLEX_ITEM,
        self::INTRINSIC,
    ];
}
