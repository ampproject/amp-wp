/**
 * External dependencies
 */
import { createGlobalStyle } from 'styled-components';

export const GlobalStyle = createGlobalStyle`
  .crop-movable .moveable-control {
    background: #222 !important;
    border-radius: 0px !important;
    border: none !important;
    box-sizing: border-box !important;
  }

  .crop-movable .moveable-control.moveable-n,
  .crop-movable .moveable-control.moveable-s,
  .crop-movable .moveable-control.moveable-e,
  .crop-movable .moveable-control.moveable-w {
    border: 1px solid #fff !important;
  }

  .crop-movable .moveable-control.moveable-n,
  .crop-movable .moveable-control.moveable-s {
    width: 16px !important;
    height: 4px !important;
    margin-left: -8px !important;
    margin-top: -2px !important;
  }

  .crop-movable .moveable-control.moveable-e,
  .crop-movable .moveable-control.moveable-w {
    width: 4px !important;
    height: 16px !important;
    margin-left: -2px !important;
    margin-top: -8px !important;
  }

  .crop-movable .moveable-control.moveable-nw,
  .crop-movable .moveable-control.moveable-ne,
  .crop-movable .moveable-control.moveable-sw,
  .crop-movable .moveable-control.moveable-se {
    width: 16px !important;
    height: 16px !important;
    background: #fff !important;
  }

  .crop-movable .moveable-control.moveable-nw:after,
  .crop-movable .moveable-control.moveable-ne:after,
  .crop-movable .moveable-control.moveable-sw:after,
  .crop-movable .moveable-control.moveable-se:after {
    content: "" !important;
    display: block !important;
    position: absolute !important;
    inset: 1px !important;
    background: #222 !important;
  }

  .crop-movable .moveable-control.moveable-nw,
  .crop-movable .moveable-control.moveable-ne {
    margin-top: -2px !important;
  }

  .crop-movable .moveable-control.moveable-sw,
  .crop-movable .moveable-control.moveable-se {
    margin-top: -14px !important;
  }

  .crop-movable .moveable-control.moveable-nw,
  .crop-movable .moveable-control.moveable-sw {
    margin-left: -2px !important;
  }

  .crop-movable .moveable-control.moveable-ne,
  .crop-movable .moveable-control.moveable-se {
    margin-left: -14px !important;
  }

  .crop-movable .moveable-control.moveable-nw {
    transform-origin: 2px 2px !important;
    clip-path: polygon(0px 0px, 16px 0px, 16px 4px, 4px 4px, 4px 16px, 0px 16px) !important;
  }
  .crop-movable .moveable-control.moveable-nw:after {
    clip-path: polygon(0px 0px, 14px 0px, 14px 2px, 2px 2px, 2px 14px, 0px 14px) !important;
  }

  .crop-movable .moveable-control.moveable-ne {
    transform-origin: 14px 2px !important;
    clip-path: polygon(0px 0px, 16px 0px, 16px 16px, 12px 16px, 12px 4px, 0px 4px) !important;
  }
  .crop-movable .moveable-control.moveable-ne:after {
    clip-path: polygon(0px 0px, 14px 0px, 14px 14px, 12px 14px, 12px 2px, 0px 2px) !important;
  }

  .crop-movable .moveable-control.moveable-sw {
    transform-origin: 2px 14px !important;
    clip-path: polygon(0px 0px, 0px 16px, 16px 16px, 16px 12px, 4px 12px, 4px 0px) !important;
  }
  .crop-movable .moveable-control.moveable-sw:after {
    clip-path: polygon(0px 0px, 0px 14px, 14px 14px, 14px 12px, 2px 12px, 2px 0px) !important;
  }

  .crop-movable .moveable-control.moveable-se {
    transform-origin: 14px 14px !important;
    clip-path: polygon(16px 0px, 16px 16px, 0px 16px, 0px 12px, 12px 12px, 12px 0px) !important;
  }
  .crop-movable .moveable-control.moveable-se:after {
    clip-path: polygon(14px 0px, 14px 14px, 0px 14px, 0px 12px, 12px 12px, 12px 0px) !important;
  }
`;
