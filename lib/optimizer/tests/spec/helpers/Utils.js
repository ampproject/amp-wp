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

'mode strict';

const {join} = require('path');
const {lstatSync, readdirSync, readFileSync, writeFileSync} = require('fs');

module.exports = {};

const isDirectory = module.exports.isDirectory =
  (source) => lstatSync(source).isDirectory();

const getResources = (source) => readdirSync(source)
    .map((name) => join(source, name));

module.exports.getDirectories =
  (source) => getResources(source).filter(isDirectory);

module.exports.getFileContents = (filePath) => readFileSync(filePath, 'utf8');
module.exports.writeFileContents = (filePath, content) => writeFileSync(filePath, content, 'utf8');
module.exports.getResources = getResources;
