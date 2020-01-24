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

const {DomTransformer} = require('../../lib/DomTransformer.js');
const {firstChildByTag} = require('../../lib/NodeUtils');

class SimpleTransformer {
  transform(tree) {
    const html = firstChildByTag(tree, 'html');
    html.attribs.test = 'simple';
  }
}

class SimpleTransformerWithPromise {
  transform(tree) {
    return new Promise((resolve) => {
      const html = firstChildByTag(tree, 'html');
      html.attribs.test = 'promise';
      resolve();
    });
  }
}

describe('Dom Transformer', () => {
  describe('transformHtml', () => {
    it('supports sync transformers', (done) => {
      domTransformerWith(SimpleTransformer)
          .transformHtml('<html><head></head><body></body></html>')
          .then((result) => {
            expect(result).toEqual('<html test="simple"><head></head><body></body></html>');
            done();
          });
    });
    it('supports async transformers', (done) => {
      domTransformerWith(SimpleTransformerWithPromise)
          .transformHtml('<html><head></head><body></body></html>')
          .then((result) => {
            expect(result).toEqual('<html test="promise"><head></head><body></body></html>');
            done();
          });
    });
  });
});

function domTransformerWith(transformer) {
  return new DomTransformer({
    transformations: [transformer],
  });
}

