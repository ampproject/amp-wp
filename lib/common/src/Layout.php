<?php

namespace AmpProject;

/**
 * Interface with constants for the different layouts.
 *
 * @package ampproject/common
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

    const VALID_LAYOUTS = [
        self::NODISPLAY,
        self::FIXED,
        self::RESPONSIVE,
        self::FIXED_HEIGHT,
        self::FILL,
        self::CONTAINER,
        self::FLEX_ITEM,
        self::FLUID,
        self::INTRINSIC,
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
