BlockMover
============

This is Block Mover component for using with the block UI for AMP Stories.
It is based on `BlockMover` from `@wordpress/components`, it uses the same props.

In addition the component also uses default `IgnoreNestedEvents` component which is not publicly available via API but required for the drag to work.

## Unchanged files.

The following files of the component are 100% or almost unchanged:
- block-draggable.js:
  - This file is mainly copied from core, the only difference is switching to using internal Draggable
- block-drag-area.js (modified from drag-handle.js)
- icons.js (unchanged)
- ignore-nested-events.js (unchanged)

## Modified files
draggable.js: This file has been modified to display the clone in relation to the specific page;
- Creating the clone has been updated to take the initial position based on the wrapper element;
- The clone styling has been changed to ignore the % values and to match the size of the original element;
- Resizing the dragged clone has been removed.
block-drag-area.js: This was renamed from from drag-handle.js

index.js: There are slight differences between the original file and this file.
- The labels of the icons have been changed;
- Dragging is always allowed even if the block is the only block.
- Mover description is removed.


## Removed Files
The following file of the component was removed since it wasn't needed:
- mover-descriptiong.js
- style.scss
- icons.js
