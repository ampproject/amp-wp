<?php

namespace AmpProject\Html;

/**
 * Interface with constants for the different types of accessibility roles.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques
 *
 * @package ampproject/amp-toolbox
 */
interface Role
{
    /**
     * A message with an alert or error information.
     *
     * @var string
     */
    const ALERT = 'alert';

    /**
     * A separate window with an alert or error information.
     *
     * @var string
     */
    const ALERTDIALOG = 'alertdialog';

    /**
     * A software unit executing a set of tasks for its users.
     *
     * @var string
     */
    const APPLICATION = 'application';

    /**
     * A section of a page that could easily stand on its own on a page, in a document, or on a website.
     *
     * @var string
     */
    const ARTICLE = 'article';

    /**
     * A region that contains mostly site-oriented content, rather than page-specific content.
     *
     * @var string
     */
    const BANNER = 'banner';

    /**
     * Allows for user-triggered actions.
     *
     * @var string
     */
    const BUTTON = 'button';

    /**
     * An element as being a cell in a tabular container that does not contain column or row header information.
     *
     * @var string
     */
    const CELL = 'cell';

    /**
     * A control that has three possible values, (true, false, mixed).
     *
     * @var string
     */
    const CHECKBOX = 'checkbox';

    /**
     * A table cell containing header information for a column.
     *
     * @var string
     */
    const COLUMNHEADER = 'columnheader';

    /**
     * Combobox is a presentation of a select, where users can type to locate a selected item.
     *
     * @var string
     */
    const COMBOBOX = 'combobox';

    /**
     * A supporting section of the document, designed to be complementary to the main content at a similar level in the
     * DOM hierarchy, but remains meaningful when separated from the main content.
     *
     * @var string
     */
    const COMPLEMENTARY = 'complementary';

    /**
     * A large perceivable region that contains information about the parent document.
     *
     * @var string
     */
    const CONTENTINFO = 'contentinfo';

    /**
     * A definition of a term or concept.
     *
     * @var string
     */
    const DEFINITION = 'definition';

    /**
     * Descriptive content for a page element which references this element via describedby.
     *
     * @var string
     */
    const DESCRIPTION = 'description';

    /**
     * A dialog is a small application window that sits above the application and is designed to interrupt the current
     * processing of an application in order to prompt the user to enter information or require a response.
     *
     * @var string
     */
    const DIALOG = 'dialog';

    /**
     * A list of references to members of a single group.
     *
     * @var string
     */
    const DIRECTORY = 'directory';

    /**
     * Content that contains related information, such as a book.
     *
     * @var string
     */
    const DOCUMENT = 'document';

    /**
     * A scrollable list of articles where scrolling may cause articles to be added to or removed from either end of the
     * list.
     *
     * @var string
     */
    const FEED = 'feed';

    /**
     * A figure inside page content where appropriate semantics do not already exist.
     *
     * @var string
     */
    const FIGURE = 'figure';

    /**
     * A landmark region that contains a collection of items and objects that, as a whole, combine to create a form.
     *
     * @var string
     */
    const FORM = 'form';

    /**
     * A grid contains cells of tabular data arranged in rows and columns (e.g., a table).
     *
     * @var string
     */
    const GRID = 'grid';

    /**
     * A gridcell is a table cell in a grid. Gridcells may be active, editable, and selectable. Cells may have
     * relationships such as controls to address the application of functional relationships.
     *
     * @var string
     */
    const GRIDCELL = 'gridcell';

    /**
     * A group is a section of user interface objects which would not be included in a page summary or table of contents
     * by an assistive technology. See region for sections of user interface objects that should be included in a page
     * summary or table of contents.
     *
     * @var string
     */
    const GROUP = 'group';

    /**
     * A heading for a section of the page.
     *
     * @var string
     */
    const HEADING = 'heading';

    /**
     * An img is a container for a collection elements that form an image.
     *
     * @var string
     */
    const IMG = 'img';

    /**
     * Interactive reference to a resource (note, that in XHTML 2.0 any element can have an href attribute and thus be a
     * link)
     *
     * @var string
     */
    const LINK = 'link';

    /**
     * Group of non-interactive list items. Lists contain children whose role is listitem.
     *
     * Uses an underscore as "list" is a conflicting PHP keyword.
     *
     * @var string
     */
    const LIST_ = 'list';

    /**
     * A list box is a widget that allows the user to select one or more items from a list. Items within the list are
     * static and may contain images. List boxes contain children whose role is option.
     *
     * @var string
     */
    const LISTBOX = 'listbox';

    /**
     * A single item in a list.
     *
     * @var string
     */
    const LISTITEM = 'listitem';

    /**
     * A region where new information is added and old information may disappear such as chat logs, messaging, game log
     * or an error log. In contrast to other regions, in this role there is a relationship between the arrival of new
     * items in the log and the reading order. The log contains a meaningful sequence and new information is added only
     * to the end of the log, not at arbitrary points.
     *
     * @var string
     */
    const LOG = 'log';

    /**
     * The main content of a document.
     *
     * @var string
     */
    const MAIN = 'main';

    /**
     * A marquee is used to scroll text across the page.
     *
     * @var string
     */
    const MARQUEE = 'marquee';

    /**
     * Content that represents a mathematical expression.
     *
     * @var string
     */
    const MATH = 'math';

    /**
     * Offers a list of choices to the user.
     *
     * @var string
     */
    const MENU = 'menu';

    /**
     * A menubar is a container of menu items. Each menu item may activate a new sub-menu. Navigation behavior should be
     * similar to the typical menu bar graphical user interface.
     *
     * @var string
     */
    const MENUBAR = 'menubar';

    /**
     * A link in a menu. This is an option in a group of choices contained in a menu.
     *
     * @var string
     */
    const MENUITEM = 'menuitem';

    /**
     * Defines a menuitem which is checkable (tri-state).
     *
     * @var string
     */
    const MENUITEMCHECKBOX = 'menuitemcheckbox';

    /**
     * Indicates a menu item which is part of a group of menuitemradio roles.
     *
     * @var string
     */
    const MENUITEMRADIO = 'menuitemradio';

    /**
     * A collection of navigational elements (usually links) for navigating the document or related documents.
     *
     * @var string
     */
    const NAVIGATION = 'navigation';

    /**
     * An element whose implicit native role semantics will not be mapped to the accessibility API.
     *
     * @var string
     */
    const NONE = 'none';

    /**
     * A section whose content is parenthetic or ancillary to the main content of the resource.
     *
     * @var string
     */
    const NOTE = 'note';

    /**
     * A selectable item in a list represented by a select.
     *
     * @var string
     */
    const OPTION = 'option';

    /**
     * An element whose role is presentational does not need to be mapped to the accessibility API.
     *
     * @var string
     */
    const PRESENTATION = 'presentation';

    /**
     * Used by applications for tasks that take a long time to execute, to show the execution progress.
     *
     * @var string
     */
    const PROGRESSBAR = 'progressbar';

    /**
     * A radio is an option in single-select list. Only one radio control in a radiogroup can be selected at the same
     * time.
     *
     * @var string
     */
    const RADIO = 'radio';

    /**
     * A group of radio controls.
     *
     * @var string
     */
    const RADIOGROUP = 'radiogroup';

    /**
     * Region is a large perceivable section on the web page.
     *
     * @var string
     */
    const REGION = 'region';

    /**
     * A row of table cells.
     *
     * @var string
     */
    const ROW = 'row';

    /**
     * A structure containing one or more row elements in a tabular container.
     *
     * @var string
     */
    const ROWGROUP = 'rowgroup';

    /**
     * A table cell containing header information for a row.
     *
     * @var string
     */
    const ROWHEADER = 'rowheader';

    /**
     * Scroll bar to navigate the horizontal or vertical dimensions of the page.
     *
     * @var string
     */
    const SCROLLBAR = 'scrollbar';

    /**
     * A section of the page used to search the page, site, or collection of sites.
     *
     * @var string
     */
    const SEARCH = 'search';

    /**
     * An entry field to provide a query to search for.
     *
     * @var string
     */
    const SEARCHBOX = 'searchbox';

    /**
     * A line or bar that separates and distinguishes sections of content.
     *
     * @var string
     */
    const SEPARATOR = 'separator';

    /**
     * A user input where the user selects an input in a given range. This form of range expects an analog keyboard
     * interface.
     *
     * @var string
     */
    const SLIDER = 'slider';

    /**
     * A form of Range that expects a user selecting from discrete choices.
     *
     * @var string
     */
    const SPINBUTTON = 'spinbutton';

    /**
     * This is a container for process advisory information to give feedback to the user.
     *
     * @var string
     */
    const STATUS = 'status';

    /**
     * Functionally identical to a checkbox but represents the states "on"/"off" instead of "checked"/"unchecked".
     *
     * Uses an underscore as "list" is a conflicting PHP keyword.
     *
     * @var string
     */
    const SWITCH_ = 'switch';

    /**
     * A header for a tabpanel.
     *
     * @var string
     */
    const TAB = 'tab';

    /**
     * A non-interactive table structure containing data arranged in rows and columns.
     *
     * @var string
     */
    const TABLE = 'table';

    /**
     * A list of tabs, which are references to tabpanels.
     *
     * @var string
     */
    const TABLIST = 'tablist';

    /**
     * Tabpanel is a container for the resources associated with a tab.
     *
     * @var string
     */
    const TABPANEL = 'tabpanel';

    /**
     * A word or phrase with a corresponding definition.
     *
     * @var string
     */
    const TERM = 'term';

    /**
     * Inputs that allow free-form text as their value.
     *
     * @var string
     */
    const TEXTBOX = 'textbox';

    /**
     * A numerical counter which indicates an amount of elapsed time from a start point, or the time remaining until an
     * end point.
     *
     * @var string
     */
    const TIMER = 'timer';

    /**
     * A toolbar is a collection of commonly used functions represented in compact visual form.
     *
     * @var string
     */
    const TOOLBAR = 'toolbar';

    /**
     * A popup that displays a description for an element when a user passes over or rests on that element. Supplement
     * to the normal tooltip processing of the user agent.
     *
     * @var string
     */
    const TOOLTIP = 'tooltip';

    /**
     * A form of a list having groups inside groups, where sub trees can be collapsed and expanded.
     *
     * @var string
     */
    const TREE = 'tree';

    /**
     * A grid whose rows can be expanded and collapsed in the same manner as for a tree.
     *
     * @var string
     */
    const TREEGRID = 'treegrid';

    /**
     * An option item of a tree. This is an element within a tree that may be expanded or collapsed.
     *
     * @var string
     */
    const TREEITEM = 'treeitem';
}
