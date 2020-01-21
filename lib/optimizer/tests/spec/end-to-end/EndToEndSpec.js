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

require('fetch-mock');
const createSpec = require('../helpers/TransformerRunner.js');
const log = require('../../lib/log.js');
const {DomTransformer, TRANSFORMATIONS_PAIRED_AMP} = require('../../lib/DomTransformer.js');
const fetchMock = require('fetch-mock');
const fetch = fetchMock.sandbox()
    .mock('https://cdn.ampproject.org/rtv/123456789000000/v0.css', '/* v0.css */')
    .mock('https://cdn.ampproject.org/v0.css', '/* v0.css */');


createSpec({
  name: 'End-to-End: AMP First',
  testDir: __dirname,
  validAmp: true,
  transformer: {
    transform: (tree, params) => {
      const ampOptimizer = new DomTransformer({
        fetch,
        log,
        runtimeVersion: {currentVersion: () => Promise.resolve('123456789000000')},
      });
      return ampOptimizer.transformTree(tree, params);
    },
  },
});

createSpec({
  name: 'End-to-End: Paired AMP',
  testDir: __dirname,
  validAmp: false,
  transformer: {
    transform: (tree, params) => {
      const ampOptimizer = new DomTransformer({
        compress: false,
        fetch,
        transformations: TRANSFORMATIONS_PAIRED_AMP,
        runtimeVersion: {currentVersion: () => Promise.resolve('123456789000000')},
      });
      return ampOptimizer.transformTree(tree, params);
    },
  },
});
