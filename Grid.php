<?php

namespace Gridito;

use Nette\ComponentModel\Container, Nette\Environment, Nette\Utils\Paginator;

/**
 * Grid
 *
 * @author Jan Marek
 * @license MIT
 */
class Grid extends \Nette\Application\UI\Control
{
	/** @var IModel */
	private $model;

	/** @var Paginator */
	private $paginator;

	/** @var int */
	private $defaultItemsPerPage = 20;

	/**
	 * @var int
	 * @persistent
	 */
	public $page = 1;

	/**
	 * @var string
	 * @persistent
	 */
	public $sortColumn = null;

	/**
	 * @var string
	 * @persistent
	 */
	public $sortType = null;

	/** @var string */
	private $ajaxClass = 'ajax';

	/** @var bool */
	private $highlightOrderedColumn = true;

	/** @var string|callable */
	private $rowClass = null;

	/** @var string */
	public $defaultSortColumn = null;

	/** @var string */
	public $defaultSortType = null;



	public function __construct(\Nette\ComponentModel\IContainer $parent = null, $name = null)
	{
		parent::__construct($parent, $name);

		$this->addComponent(new Container, 'toolbar');
		$this->addComponent(new Container, 'actions');
		$this->addComponent(new Container, 'columns');

		$this->paginator = new Paginator;
		$this->paginator->setItemsPerPage($this->defaultItemsPerPage);
	}



	/**
	 * @param bool $highlightOrderedColumn highlight ordered column
	 * @return Grid
	 */
	public function setHighlightOrderedColumn($highlightOrderedColumn)
	{
		$this->highlightOrderedColumn = (bool) $highlightOrderedColumn;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function getHighlightOrderedColumn()
	{
		return $this->highlightOrderedColumn;
	}



	/**
	 * Is column highlighted?
	 * @param Column $column
	 * @return bool
	 */
	public function isColumnHighlighted(Column $column)
	{
		$sorting = $this->getSorting();

		if (!$this->highlightOrderedColumn || $sorting === NULL) {
			return FALSE;
		}

		return $sorting[0] === $column->getColumnName();
	}



	/**
	 * Set row class
	 * @param callable|string $class callable or CSS class
	 * @return Grid
	 */
	public function setRowClass($class)
	{
	    $this->rowClass = $class;
		return $this;
	}



	/**
	 * Get row class
	 * @param \Nette\Iterators\CachingIterator $iterator
	 * @param mixed $row
	 * @return string|null
	 */
	public function getRowClass($iterator, $row)
	{
		if (is_callable($this->rowClass)) {
			return call_user_func($this->rowClass, $iterator, $row);
		} elseif (is_string($this->rowClass)) {
			return $this->rowClass;
		} else {
			return NULL;
		}
	}



	/**
	 * Get model
	 * @return IModel
	 */
	public function getModel()
	{
		return $this->model;
	}



	/**
	 * Set model
	 * @param IModel $model model
	 * @return Grid
	 */
	public function setModel(IModel $model)
	{
		$this->getPaginator()->setItemCount($model->count());
		$this->model = $model;
		return $this;
	}



	/**
	 * Get items per page
	 * @return int
	 */
	public function getItemsPerPage()
	{
		return $this->getPaginator()->getItemsPerPage();
		return $this;
	}



	/**
	 * Set items per page
	 * @param int $itemsPerPage items per page
	 * @return Grid
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		$this->getPaginator()->setItemsPerPage($itemsPerPage);
		return $this;
	}



	/**
	 * Get ajax class
	 * @return string
	 */
	public function getAjaxClass()
	{
		return $this->ajaxClass;
	}



	/**
	 * Set ajax class
	 * @param string $ajaxClass ajax class
	 * @return Grid
	 */
	public function setAjaxClass($ajaxClass)
	{
		$this->ajaxClass = $ajaxClass;
		return $this;
	}



	/**
	 * Set default sorting
	 * @param string $column column name for model
	 * @param string $type asc or desc
	 * @return Grid
	 */
	public function setDefaultSorting($column, $type)
	{
		$this->defaultSortColumn = $column;
		$this->defaultSortType = $type;

		return $this;
	}



	/**
	 * Get sorting options
	 * @return array|null array with sorting column for model and asc or desc
	 */
	public function getSorting()
	{
		$columns = $this['columns'];

		/* @var $columns \Nette\ComponentModel\IContainer */

		$sortByColumn = $this->sortColumn ? $columns->getComponent($this->sortColumn) : NULL;

		/* @var $sortByColumn \Gridito\Column */

		if ($sortByColumn && $sortByColumn->isSortable() && ($this->sortType === IModel::ASC || $this->sortType === IModel::DESC)) {
			return array($sortByColumn->getColumnName(), $this->sortType);
		} elseif ($this->defaultSortColumn) {
			return array($this->defaultSortColumn, $this->defaultSortType);
		} else {
			return NULL;
		}
	}



	/**
	 * Get paginator
	 * @return Nette\Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}



	/**
	 * Get security token
	 * @return string
	 */
	public function getSecurityToken()
	{
		$session = Environment::getSession(__CLASS__ . '-' . __METHOD__);

		if (empty($session->securityToken)) {
			$session->securityToken = md5(uniqid(mt_rand(), true));
		}

		return $session->securityToken;
	}



	/**
	 * Has toolbar
	 * @return bool
	 */
	public function hasToolbar()
	{
		return count($this['toolbar']->getComponents()) > 0;
	}



	/**
	 * Has actions
	 * @return bool
	 */
	public function hasActions()
	{
		return count($this['actions']->getComponents()) > 0;
	}



	/**
	 * Handle change page signal
	 * @param int $page page
	 */
	public function handleChangePage($page)
	{
		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}



	public function handleSort($sortColumn, $sortType)
	{
		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}



	/**
	 * Create template
	 * @param string|null $class
	 * @return \Nette\Templating\Template
	 */
	protected function createTemplate($class = null)
	{
		return parent::createTemplate($class)->setFile(__DIR__ . '/templates/grid.phtml');
	}



	/**
	 * Render grid
	 */
	public function render()
	{
		$this->paginator->setPage($this->page);
		$this->model->setLimit($this->paginator->getLength());
		$this->model->setOffset($this->paginator->getOffset());

		if ($this->sortColumn && $this['columns']->getComponent($this->sortColumn)->isSortable()) {
			$sortByColumn = $this['columns']->getComponent($this->sortColumn);
			$this->model->setSorting($sortByColumn->getColumnName(), $this->sortType);
		} elseif ($this->defaultSortColumn) {
			$this->model->setSorting($this->defaultSortColumn, $this->defaultSortType);
		}

		// better pagination thx to David Grudl (http://addons.nette.org/cs/visualpaginator)
		$page = $this->paginator->getPage();
		if ($this->paginator->getPageCount() < 2) {
			$steps = array($page);
		} else {
			$arr = range(max($this->paginator->getFirstPage(), $page - 3), min($this->paginator->getLastPage(), $page + 3));
			$count = 4;
			$quotient = ($this->paginator->getPageCount() - 1) / $count;
			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $this->paginator->getFirstPage();
			}
			sort($arr);
			$steps = array_values(array_unique($arr));
		}

		$this->template->paginationSteps = $steps;
		$this->template->render();
	}



	/**
	 * Add column
	 * @param string $name name
	 * @param string $label label
	 * @param array $options options
	 * @return Column
	 */
	public function addColumn($name, $label = null, array $options = array())
	{
		$componentName = \Nette\Utils\Strings::webalize($name);
		$componentName = strtr($componentName, '-', '_');
		$column = new Column($this['columns'], $componentName);
		$column->setColumnName($name);
		$column->setLabel($label);
		$this->setOptions($column, $options);
		return $column;
	}



	/**
	 * Add action button
	 * @param string $name button name
	 * @param string $label label
	 * @param array $options options
	 * @return Button
	 */
	public function addButton($name, $label = null, array $options = array())
	{
		$button = new Button($this['actions'], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Add window button
	 * @param string $name button name
	 * @param string $label label
	 * @param array $options options
	 * @return WindowButton
	 */
	public function addWindowButton($name, $label = null, array $options = array())
	{
		$button = new WindowButton($this['actions'], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Add action button to toolbar
	 * @param string $name button name
	 * @param string $label label
	 * @param array $options options
	 * @return Button
	 */
	public function addToolbarButton($name, $label = null, array $options = array())
	{
		$button = new Button($this['toolbar'], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Add window button to toolbar
	 * @param string $name button name
	 * @param string $label label
	 * @param array $options options
	 * @return WindowButton
	 */
	public function addToolbarWindowButton($name, $label = null, array $options = array())
	{
		$button = new WindowButton($this['toolbar'], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Set page
	 * @param int $page page
	 */
	private function setPage($page)
	{
		$this->getPaginator()->setPage($page);
	}



	protected function setOptions($object, $options)
	{
		foreach	($options as $option => $value) {
			$method = 'set' . ucfirst($option);
			if (method_exists($object, $method)) {
				$object->$method($value);
			} else {
				throw new \InvalidArgumentException("Option with name $option does not exist.");
			}
		}
	}

}