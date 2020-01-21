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

const {findMetaViewport} = require('../../lib/HtmlDomHelper');
const treeParser = require('../../lib/TreeParser');

describe('HtmlDomHelper', () => {
  describe('findMetaViewport', () => {
    it('returns null if tag is not present', () => {
      const tree = treeParser.parse(`<html><head>
            <meta charset="utf-8">
          </head></html>`);
      const html = tree.root.firstChild;
      const head = html.firstChild;
      const result = findMetaViewport(head);
      expect(result).toBeNull();
    });

    it('returns the correct tag', () => {
      const tree = treeParser.parse(`<html><head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="hello world">
          </head></html>`);
      const html = tree.root.firstChild;
      const head = html.firstChild;
      const result = findMetaViewport(head);
      expect(result).toEqual(head.children[3]);
    });
  });
});
