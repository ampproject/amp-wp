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

const validator = require('amphtml-validator');
const {getFileContents, getResources} = require('../helpers/Utils.js');
const {join, basename} = require('path');
const {writeFileSync} = require('fs');

const {DomTransformer} = require('../../lib/DomTransformer.js');

const ampOptimizer = new DomTransformer();

const files = getResources(join(__dirname, 'files')).filter((f) => f.endsWith('.html'));

describe('Optimizer produces valid AMP', () => {
  files.forEach((filePath) => {
    const fileName = basename(filePath);
    it(fileName, async () => {
      const contents = getFileContents(filePath);
      const optimizedContents = await ampOptimizer.transformHtml(contents);
      const validatorInstance = await validator.getInstance();
      const result = validatorInstance.validateString(optimizedContents);
      if (result.status !== 'PASS') {
        writeFileSync(join(__dirname, 'tmp', fileName), optimizedContents, 'utf-8');
        fail(`Validation errors:\n\n ${JSON.stringify(result.errors, null, 2)}`);
      }
    });
  });
});
