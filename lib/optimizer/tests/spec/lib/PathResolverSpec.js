/**
 * Copyright 2019 The AMP HTML Authors. All Rights Reserved.
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

const {join, resolve} = require('path');
const PathResolver = require('../../lib/PathResolver.js');


describe('PathResolver', () => {
  describe('absolute URLs', () => {
    it('ignores empty base', () => {
      expect(resolvePath('', 'https://example.com/page.html?query=true')).toEqual('https://example.com/page.html?query=true');
    });
    it('ignores undefined base', () => {
      expect(resolvePath(undefined, 'https://example.com/page.html?query=true')).toEqual('https://example.com/page.html?query=true');
    });
    it('ignores base host', () => {
      expect(resolvePath('https://test.de', 'https://example.com/page.html?query=true')).toEqual('https://example.com/page.html?query=true');
    });
    it('ignores base path', () => {
      expect(resolvePath('../img/', 'https://example.com/page.html?query=true')).toEqual('https://example.com/page.html?query=true');
    });
  });
  describe('relative URLs', () => {
    it('removes query params if no base given', () => {
      expect(resolvePath('', 'page.html?query=true')).toEqual(resolve('page.html'));
    });
    it('resolves relative URLs against process.cwd()', () => {
      expect(resolvePath('', '/page.html')).toEqual(join(process.cwd(), 'page.html'));
    });
    it('resolves against base host and keeps query params', () => {
      expect(resolvePath('https://test.de', 'page.html?query=true')).toEqual('https://test.de/page.html?query=true');
    });
    it('resolves against base relative path and removes query params', () => {
      expect(resolvePath('../img/', 'page.html?query=true')).toEqual(resolve('../img/page.html'));
    });
  });
});

function resolvePath(base, path) {
  return new PathResolver(base).resolve(path);
}
