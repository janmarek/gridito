<?php

namespace Gridito;

use Nette\Application\Responses\TextResponse;

/**
 * Window button
 *
 * @author Jan Marek
 * @license MIT
 */
class WindowButton extends BaseButton
{
	/**
	 * Handle click signal
	 * @param string $token security token
	 * @param mixed $uniqueId primary key
	 */
	public function handleClick($token, $uniqueId = null) {
		ob_start();
		parent::handleClick($token, $uniqueId);
		$output = ob_get_clean();

		if ($this->getPresenter()->isAjax()) {
			$this->getPresenter()->sendResponse(new TextResponse($output));
		} else {
			$this->getGrid()->getTemplate()->windowLabel = $this->getLabel();
			$this->getGrid()->getTemplate()->windowOutput = $output;
		}
	}



	/**
	 * Create button element
	 * @param mixed $row row
	 * @return \Nette\Web\Html
	 */
	public function createButton($row = null) {
		$el = parent::createButton($row);
		$el->class[] = 'gridito-window-button';
		$el->data('gridito-window-title', $this->getLabel());
		return $el;
	}

}