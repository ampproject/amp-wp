<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Comment\Commentable;

/**
 * A CSSList is the most generic container available. Its contents include RuleSet as well as other CSSList objects.
 * Also, it may contain Import and Charset objects stemming from @-rules.
 */
abstract class CSSList implements Renderable, Commentable {

	protected $aComments;
	protected $aContents;
	protected $iLineNo;

	public function __construct($iLineNo = 0) {
		$this->aComments = array();
		$this->aContents = array();
		$this->iLineNo = $iLineNo;
	}

	/**
	 * @return int
	 */
	public function getLineNo() {
		return $this->iLineNo;
	}

	/**
	 * Prepend item to list of contents.
	 *
	 * @param object $oItem Item.
	 */
	public function prepend($oItem) {
		array_unshift($this->aContents, $oItem);
	}

	/**
	 * Append item to list of contents.
	 *
	 * @param object $oItem Item.
	 */
	public function append($oItem) {
		$this->aContents[] = $oItem;
	}

	/**
	 * Splice the list of contents.
	 *
	 * @param int       $iOffset      Offset.
	 * @param int       $iLength      Length. Optional.
	 * @param RuleSet[] $mReplacement Replacement. Optional.
	 */
	public function splice($iOffset, $iLength = null, $mReplacement = null) {
		array_splice($this->aContents, $iOffset, $iLength, $mReplacement);
	}

	/**
	 * Removes an item from the CSS list.
	 * @param RuleSet|Import|Charset|CSSList $oItemToRemove May be a RuleSet (most likely a DeclarationBlock), a Import, a Charset or another CSSList (most likely a MediaQuery)
	 * @return bool Whether the item was removed.
	 */
	public function remove($oItemToRemove) {
		$iKey = array_search($oItemToRemove, $this->aContents, true);
		if ($iKey !== false) {
			unset($this->aContents[$iKey]);
			return true;
		}
		return false;
	}

	/**
	 * Replace one item with another one or more items.
	 * @param RuleSet|Import|Charset|CSSList $oItemToRemove May be a RuleSet (most likely a DeclarationBlock), a Import, a Charset or another CSSList (most likely a MediaQuery)
	 * @param object|array $aReplacedItems Item(s) to replace the item with.
	 * @return bool Whether the item was removed.
	 */
	public function replace($oItemToRemove, $aReplacedItems) {
		$iKey = array_search($oItemToRemove, $this->aContents, true);
		if ($iKey !== false) {
			array_splice($this->aContents, $iKey, 1, $aReplacedItems);
			return true;
		}
		return false;
	}

	/**
	 * Set the contents.
	 * @param array $aContents Objects to set as content.
	 */
	public function setContents(array $aContents) {
		$this->aContents = array();
		foreach ($aContents as $content) {
			$this->append($content);
		}
	}

	/**
	 * Removes a declaration block from the CSS list if it matches all given selectors.
	 * @param array|string $mSelector The selectors to match.
	 * @param boolean $bRemoveAll Whether to stop at the first declaration block found or remove all blocks
	 */
	public function removeDeclarationBlockBySelector($mSelector, $bRemoveAll = false) {
		if ($mSelector instanceof DeclarationBlock) {
			$mSelector = $mSelector->getSelectors();
		}
		if (!is_array($mSelector)) {
			$mSelector = explode(',', $mSelector);
		}
		foreach ($mSelector as $iKey => &$mSel) {
			if (!($mSel instanceof Selector)) {
				$mSel = new Selector($mSel);
			}
		}
		foreach ($this->aContents as $iKey => $mItem) {
			if (!($mItem instanceof DeclarationBlock)) {
				continue;
			}
			if ($mItem->getSelectors() == $mSelector) {
				unset($this->aContents[$iKey]);
				if (!$bRemoveAll) {
					return;
				}
			}
		}
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult = '';
		$bIsFirst = true;
		$oNextLevel = $oOutputFormat;
		if(!$this->isRootList()) {
			$oNextLevel = $oOutputFormat->nextLevel();
		}
		foreach ($this->aContents as $oContent) {
			$sRendered = $oOutputFormat->safely(function() use ($oNextLevel, $oContent) {
				return $oContent->render($oNextLevel);
			});
			if($sRendered === null) {
				continue;
			}
			if($bIsFirst) {
				$bIsFirst = false;
				$sResult .= $oNextLevel->spaceBeforeBlocks();
			} else {
				$sResult .= $oNextLevel->spaceBetweenBlocks();
			}
			$sResult .= $sRendered;
		}

		if(!$bIsFirst) {
			// Had some output
			$sResult .= $oOutputFormat->spaceAfterBlocks();
		}

		return $sResult;
	}
	
	/**
	* Return true if the list can not be further outdented. Only important when rendering.
	*/
	public abstract function isRootList();

	public function getContents() {
		return $this->aContents;
	}

	/**
	 * @param array $aComments Array of comments.
	 */
	public function addComments(array $aComments) {
		$this->aComments = array_merge($this->aComments, $aComments);
	}

	/**
	 * @return array
	 */
	public function getComments() {
		return $this->aComments;
	}

	/**
	 * @param array $aComments Array containing Comment objects.
	 */
	public function setComments(array $aComments) {
		$this->aComments = $aComments;
	}

}
