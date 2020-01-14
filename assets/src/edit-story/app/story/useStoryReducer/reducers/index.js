
// Manipulate pages.
export { default as addPage } from './addPage';
export { default as deletePage } from './deletePage';
export { default as updatePage } from './updatePage';
export { default as arrangePage } from './arrangePage';

// Manipulate elements on a page.
export { default as addElements } from './addElements';
export { default as deleteElements } from './deleteElements';
export { default as updateElements } from './updateElements';
export { default as setBackgroundElement } from './setBackgroundElement';
export { default as arrangeElement } from './arrangeElement';

// Manipulate current page.
export { default as setCurrentPage } from './setCurrentPage';

// Manipulate list of selected elements.
export { default as setSelectedElements } from './setSelectedElements';
export { default as selectElement } from './selectElement';
export { default as unselectElement } from './unselectElement';
export { default as toggleElement } from './toggleElement';

// Manipulate entire internal state.
export { default as restore } from './restore';

// Manipulate story-global properties.
export { default as updateStory } from './updateStory';
