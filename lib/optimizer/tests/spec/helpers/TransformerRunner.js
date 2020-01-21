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
const {basename, join} = require('path');
const {writeFileContents, getFileContents, getDirectories} = require('../helpers/Utils.js');

const jsBeautify = require('js-beautify/js/lib/beautify-html.js');

const BEAUTIFY_OPTIONS = {
  'indent_size': 2,
  'unformatted': ['noscript', 'style'],
  'indent-char': ' ',
  'no-preserve-newlines': '',
  'extra_liners': [],
};

const treeParser = require('../../lib/TreeParser.js');

const TRANSFORMER_PARAMS = {
  verbose: true,
  ampUrl: 'https://example.com/amp-version.html',
};

const CONFIG_START_TOKEN = '<!--';
const CONFIG_END_TOKEN = '-->';

const WRITE_SNAPSHOT = process.env.OPTIMIZER_SNAPSHOT;
if (WRITE_SNAPSHOT) {
  console.log('[AMP Optimizer Test] Creating new snapshot');
}

module.exports = function(testConfig) {
  describe(testConfig.name, () => {
    getDirectories(testConfig.testDir).forEach((testDir) => {
      it(basename(testDir), async () => {
        let params = TRANSFORMER_PARAMS;

        // parse input and extract params
        let input = getFileContents(join(testDir, 'input.html'));
        if (input.startsWith(CONFIG_START_TOKEN)) {
          const indexStartConfig = CONFIG_START_TOKEN.length;
          const indexEndConfig = input.indexOf(CONFIG_END_TOKEN);
          const paramsString = input.substring(indexStartConfig, indexEndConfig);
          params = JSON.parse(paramsString);
          // trim params from input string
          input = input.substring(indexEndConfig + CONFIG_END_TOKEN.length);
        }

        const tree = treeParser.parse(input);

        // parse expected output
        const expectedOutputPath =
          join(
              testDir,
            testConfig.validAmp ? 'expected_output.valid.html' : 'expected_output.html',
          );
        const expectedOutput = getFileContents(expectedOutputPath);
        await testConfig.transformer.transform(tree, testConfig.validAmp ? {} : params);
        const actualOutput = serialize(tree);
        if (WRITE_SNAPSHOT) {
          writeFileContents(expectedOutputPath, actualOutput);
        } else {
          expect(actualOutput).toBe(expectedOutput);
        }
      });
    });
  });
};

function serialize(tree) {
  const html = treeParser.serialize(tree);
  return jsBeautify.html_beautify(html, BEAUTIFY_OPTIONS);
}
