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

const treeParser = require('../../lib/TreeParser.js');
const {
  createElement,
  firstChildByTag,
  nextNode,
  insertBefore,
  appendChild,
  hasAttribute,
} = require('../../lib/NodeUtils');

describe('Tree Parser', () => {
  describe('firstChildByTag', () => {
    let root;
    let html;
    beforeEach(async () => {
      root = await treeParser.parse(`<html>
            <head></head>
            <body></body>
          </html>`);
      html = firstChildByTag(root, 'html');
    });
    it('returns first child of tag', () => {
      expect(firstChildByTag(html, 'body').tagName).toEqual('body');
    });

    it('returns null if there are no children', () => {
      const head = html.children[0];
      expect(firstChildByTag(head, 'script')).toBe(null);
    });
  });

  describe('hasAttribute', () => {
    let node;
    beforeEach(async () => {
      const root = await treeParser.parse('<html></html>');
      node = firstChildByTag(root, 'html');
    });
    it('false if no attribute with name', () => {
      expect(hasAttribute(node, 'unknown')).toBe(false);
    });
    it('true if attribute with name', () => {
      node.attribs.amp = 'there';
      expect(hasAttribute(node, 'amp')).toBe(true);
    });
    it('true if empty attribute', () => {
      node.attribs.amp = '';
      expect(hasAttribute(node, 'amp')).toBe(true);
    });
  });

  describe('nextNode', () => {
    it('walks depth-first through the dom', async () => {
      const root = await treeParser.parse(`<!doctype html>
          <html>
            <head>
              <script></script>
            </head>
            <body>
              <p>Text<span>More text</span></p>
            </body>
          </html>
        `);
      const expectedNodes = [
        'undefined-root', 'undefined-directive',
        'undefined-text', 'html-tag',
        'undefined-text', 'head-tag',
        'undefined-text', 'script-script',
        'undefined-text', 'undefined-text',
        'body-tag', 'undefined-text',
        'p-tag', 'undefined-text',
        'span-tag', 'undefined-text',
        'undefined-text', 'undefined-text',
        'undefined-text',
      ];
      expect(traverse(root)).toEqual(expectedNodes);
    });
  });

  describe('insertBefore', () => {
    let root;
    let body;
    beforeEach(async () => {
      root = await treeParser.parse(`
      <html>
        <body><first-tag></first-tag><second-tag></second-tag></body>
      </html>`);
      const html = firstChildByTag(root, 'html');
      body = firstChildByTag(html, 'body');
    });

    it('Inserts a node in the correct place', () => {
      const newTag = createElement('newtag');
      const secondTag = firstChildByTag(body, 'second-tag');
      insertBefore(body, newTag, secondTag);
      expect(secondTag.prev.tagName).toEqual('newtag');
    });

    it('Inserts node at the end when secondTag is null', () => {
      const newTag = createElement('newtag');
      insertBefore(body, newTag, null);
      expect(body.lastChild.tagName).toEqual('newtag');
    });
  });
});

describe('Tree', () => {
  describe('createElement', () => {
    it('works without attributes', () => {
      const element = createElement('test');
      expect(element.tagName).toBe('test');
    });
    it('works with attributes', () => {
      const element = createElement('test', {myAttribute: 'hello'});
      expect(element.attribs.myAttribute).toBe('hello');
    });
  });
  describe('appendChild', () => {
    let firstElement;
    let secondElement;
    let head;
    beforeEach(async () => {
      const root = await treeParser.parse('<html><head></head></html>');
      head = root.firstChild.firstChild;

      firstElement = createElement('meta');
      appendChild(head, firstElement);

      secondElement = createElement('script');
      secondElement.nextSibling = firstElement;
      appendChild(head, secondElement);
    });
    it('appends child', () => {
      expect(head.firstChild).toEqual(firstElement);
    });
    it('sets previous', () => {
      expect(head.children[1].prev).toEqual(firstElement);
    });
    it('sets next', () => {
      expect(head.firstChild.next).toEqual(secondElement);
    });
    it('clears next', () => {
      expect(head.children[1].next).toBe(null);
    });
  });
});

function traverse(root) {
  const traversedNodes = [];
  let node = root;
  while (node) {
    traversedNodes.push(node.tagName + '-' + node.type);
    node = nextNode(node);
  }
  return traversedNodes;
}
