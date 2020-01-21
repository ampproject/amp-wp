/**
 * Copyright 2017 The AMP HTML Authors. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS-IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

const {
  cssLength,
  getLayoutClass,
  calculateHeight,
  calculateWidth,
  isLayoutSizeDefined,
  getCssLengthStyle,
} = require('../../lib/ParseLayout.js');

describe('ParseLayout', () => {
  describe('cssLength', () => {
    it('parses the units correctly', () => {
      const allowedUnits = ['px', 'em', 'rem', 'vh', 'vmin', 'vmax'];
      for (const unit of allowedUnits) {
        const parsed = cssLength('10' + unit, false);
        expect(parsed.isSet).toBe(true);
        expect(parsed.isValid).toBe(true);
        expect(parsed.numeral).toEqual(10);
        expect(parsed.unit).toEqual(unit);
        expect(parsed.isAuto).toBe(false);
      }
    });
    it('uses \'px\' when unit is empty', () => {
      const parsed = cssLength('10', false);
      expect(parsed.isSet).toBe(true);
      expect(parsed.isValid).toBe(true);
      expect(parsed.numeral).toEqual(10);
      expect(parsed.unit).toEqual('px');
      expect(parsed.isAuto).toBe(false);
    });
    it('handles null input as value not set', () => {
      const parsed = cssLength(null, false);
      expect(parsed.isSet).toBe(false);
      expect(parsed.isValid).toBe(true);
      expect(parsed.unit).toEqual('px');
      expect(parsed.isAuto).toBe(false);
    });
    it('handles empty input as invalid', () => {
      const parsed = cssLength('', false);
      expect(parsed.isValid).toBe(false);
    });
    it('handles garbage input as invalid', () => {
      expect((cssLength('100%', false)).isValid).toBe(false);
      expect((cssLength('not a number', false)).isValid).toBe(false);
      expect((cssLength('1.1.1', false)).isValid).toBe(false);
      expect((cssLength('5inches', false)).isValid).toBe(false);
      expect((cssLength('fahrenheit', false)).isValid).toBe(false);
      expect((cssLength('px', false)).isValid).toBe(false);
      expect((cssLength('ix unciae"', false)).isValid).toBe(false);
    });
    it('handles input / auto combinations correctly', () => {
      {
        const parsed = cssLength('1', false);
        expect(parsed.isValid).toBe(true);
        expect(parsed.isAuto).toBe(false);
      }
      {
        const parsed = cssLength('1', true);
        expect(parsed.isValid).toBe(true);
        expect(parsed.isAuto).toBe(false);
      }
      {
        const parsed = cssLength('auto', false);
        expect(parsed.isValid).toBe(false);
      }
      {
        const parsed = cssLength('auto', true);
        expect(parsed.isValid).toBe(true);
        expect(parsed.isAuto).toBe(true);
      }
      {
        const parsed = cssLength('fluid', false, true);
        expect(parsed.isValid).toBe(true);
        expect(parsed.isFluid).toBe(true);
      }
    });
  });
  describe('getLayoutClass', () => {
    it('returns the correct layout classes', () => {
      expect(getLayoutClass('fixed-height')).toEqual('i-amphtml-layout-fixed-height');
      expect(getLayoutClass('responsive')).toEqual('i-amphtml-layout-responsive');
      expect(getLayoutClass('')).toEqual('');
    });
  });
  describe('Calculates dimensions for amp-analytics', () => {
    it('correctly calculates height for amp-analytics', () => {
      const expectedOutput = cssLength('1', false);
      const output = calculateHeight('fixed', cssLength(null, false), 'amp-analytics');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
    it('correctly calculats width for amp-analytics', () => {
      const expectedOutput = cssLength('1', false);
      const output = calculateWidth('fixed', cssLength(null, false), 'amp-analytics');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
  });
  describe('Calculates dimensions for amp-pixel', () => {
    it('correctly calculates height for amp-pixel', () => {
      const expectedOutput = cssLength('1', false);
      const output = calculateHeight('fixed', cssLength(null, false), 'amp-pixel');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
    it('correctly calculats width for amp-pixel', () => {
      const expectedOutput = cssLength('1', false);
      const output = calculateWidth('fixed', cssLength(null, false), 'amp-pixel');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
  });
  describe('Calculates dimensions for amp-social-share', () => {
    it('correctly calculates height for amp-social-share', () => {
      const expectedOutput = cssLength('44', false);
      const output = calculateHeight('fixed', cssLength(null, false), 'amp-social-share');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
    it('correctly calculates width for amp-social-share', () => {
      const expectedOutput = cssLength('60', false);
      const output = calculateWidth('fixed', cssLength(null, false), 'amp-social-share');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
  });
  describe('Calculates dimensions for amp-youtube', () => {
    it('correctly calculates height for amp-youtube', () => {
      const expectedOutput = cssLength('720', false);
      const output = calculateHeight('fixed', cssLength('720', false), 'amp-youtube');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
    it('correctly calculates width for amp-youtube', () => {
      const expectedOutput = cssLength('480', false);
      const output = calculateWidth('fixed', cssLength('480', false), 'amp-youtube');
      expect(output.numeral).toEqual(expectedOutput.numeral);
      expect(output.unit).toEqual(expectedOutput.unit);
    });
  });
  describe('isLayoutSizeDefined', () => {
    it('returns correct values', () => {
      expect(isLayoutSizeDefined('fill')).toBe(true);
      expect(isLayoutSizeDefined('container')).toBe(false);
      expect(isLayoutSizeDefined('')).toBe(false);
    });
  });
  describe('getCssLengthStyle', () => {
    it('uses px for empty unit', () => {
      const emptyUnitPx = cssLength('30', false);
      expect(getCssLengthStyle(emptyUnitPx, 'height')).toEqual('height:30px;');
    });
    it('works correctly with em unit', () => {
      const emptyUnitPx = cssLength('10.1em', false);
      expect(getCssLengthStyle(emptyUnitPx, 'width')).toEqual('width:10.1em;');
    });
    it('works correctly with auto', () => {
      const emptyUnitPx = cssLength('auto', true);
      expect(getCssLengthStyle(emptyUnitPx, 'width')).toEqual('width:auto;');
    });
    it('handles empty values', () => {
      const emptyUnitPx = cssLength(null, false);
      expect(getCssLengthStyle(emptyUnitPx, 'height')).toEqual('');
    });
  });
});
