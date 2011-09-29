<?php

namespace Gridito;
use Nette\Utils\Strings;
use Nette\Utils\Html;

/**
 * Grid column
 *
 * @author Jan Marek
 * @license MIT
 */
class Column extends \Nette\Application\UI\Control
{
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var string */
	private $label;

	/** @var callback */
	private $renderer = null;

	/** @var int */
	private $maxlen = null;

	/** @var string */
	private $type = 'string';

	/** @var bool */
	private $sortable = false;

	/** @var bool */
	private $editable = false;

	/** @var string */
	private $dateTimeFormat = "j.n.Y G:i";

	/** @var string|callable */
	private $cellClass = null;

	/** @var string */
	private $format = null;

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getters & setters">

	public function setCellClass($class)
	{
		$this->cellClass = $class;
		return $this;
	}



	public function getCellClass($iterator, $row)
	{
		if (is_callable($this->cellClass)) {
			return call_user_func($this->cellClass, $iterator, $row);
		} elseif (is_string($this->cellClass)) {
			return $this->cellClass;
		} else {
			return null;
		}
	}



	/**
	 * Get label
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}



	/**
	 * Set label
	 * @param string label
	 * @return Column
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}



	/**
	 * Get cell renderer
	 * @return callback
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * Set cell renderer
	 * @param callback cell renderer
	 * @return Column
	 */
	public function setRenderer($cellRenderer)
	{
		$this->renderer = $cellRenderer;
		return $this;
	}

	/**
	 * Set maximal length of cell
	 * @param $maxlen
	 * @return Column
	 */
	public function setLength($maxlen)
	{
		$this->maxlen = $maxlen;
		return $this;
	}

	/**
	 * Get maximal length of cell
	 * @return int
	 */
	public function getLength()
	{
		return $this->maxlen;
	}

	/**
	 * Set the type of cell
	 * @param string type
	 * @return Column
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Get the type of cell
	 * @return string type
	 */
	public function getType($type)
	{
		return $this->type;
	}

	/**
	 * Set format of the cell
	 * @param mixed format
	 * @return Column
	 */
	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 * Get the format
	 * @return mixed
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * Is sortable?
	 * @return bool
	 */
	public function isSortable() {
		return $this->sortable;
	}



	/**
	 * Set sortable
	 * @param bool sortable
	 * @return Column
	 */
	public function setSortable($sortable) {
		$this->sortable = $sortable;
		return $this;
	}

	/**
	 * Set editable
	 * @param bool editable
	 * @return Column
	 */
	public function setEditable($editable) {
		$this->editable = $editable;
		return $this;
	}

    /**
	 * Is editable?
	 * @return bool
	 */
	public function isEditable() {
		return $this->editable;
	}


	/**
	 * Get sorting
	 * @return string|null asc, desc or null
	 */
	public function getSorting()
	{
		$grid = $this->getGrid();
		if ($grid->sortColumn === $this->getName()) {
			return $grid->sortType;
		} else {
			return null;
		}
	}



	/**
	 * Get date/time format
	 * @return string
	 */
	public function getDateTimeFormat() {
		return $this->dateTimeFormat;
	}



	/**
	 * Set date/time format
	 * @param string datetime format
	 * @return Column
	 */
	public function setDateTimeFormat($dateTimeFormat) {
		$this->dateTimeFormat = $dateTimeFormat;
		return $this;
	}



	/**
	 * Get grid
	 * @return Grid
	 */
	public function getGrid() {
		return $this->getParent()->getParent();
	}

	// </editor-fold>

	/**
	 * Render boolean
	 * @param bool value
	 */
	public static function renderBoolean($value)
	{
		$icon = $value ? "check" : "closethick";
        $el = Html::el('span');
        $el->data['value'] = $value ? '1' : '0';
        $el->data['type']  = 'bool';
        $el->add(Html::el("span class='ui-icon ui-icon-$icon'"));
        return $el;
	}



	/**
	 * Render datetime
	 * @param Datetime value
	 * @param string datetime format
	 */
	public static function renderDateTime($value, $format)
	{
		return $value->format($format);
	}

	/**
	 * Render the text, takes care of length
	 * @param string $text	 text to render
	 * @param int	 $maxlen maximum length of text
	 */
	public static function renderText($text, $maxlen)
	{
		if (is_null($maxlen) || Strings::length($text) < $maxlen) {
            return Html::el('span')->setText($text);
		} else {
			return Html::el('span')->title($text)
				->setText(Strings::truncate($text, $maxlen));
		}
	}

	/**
	 * Render the email address, takes care of length
	 * @param string $email  email address
	 * @param int	 $maxlen maximum length of text
     * @return mixed
	 */
	public static function renderEmail($email, $maxlen)
	{
		$el = Html::el('a')->href('mailto:' . $email);
		if (is_null($maxlen) || Strings::length($email) < $maxlen) {
			return $el->setText($email);
		} else {
			return $el->title($email)
				->setText(Strings::truncate($email, $maxlen));
		}
	}


	/**
	 * Default cell renderer
	 * @param mixed $record
	 * @param Column $column
     * @return mixed
	 */
	public function defaultCellRenderer($record, $column) {
		$name = $column->getName();
		$value = $record->$name;

		// boolean
		if (in_array($this->type, array('bool', 'boolean')) || is_bool($value)) {
			return self::renderBoolean($value);

		// date
		} elseif ($value instanceof \DateTime) {
			return self::renderDateTime($value, $this->dateTimeFormat);

		// email
		} elseif ($this->type == 'email') {
			return self::renderEmail($value, $this->maxlen);

		// other
		} else {
			if (!is_null($this->format)) {
				$value = Grid::formatRecordString($record, $this->format);
			}
			return self::renderText($value, $this->maxlen);
		}
	}



	/**
	 * Render cell
	 * @param mixed record
	 */
	public function renderCell($record) {
		$column = call_user_func($this->renderer ?: array($this, "defaultCellRenderer"), $record, $this);
        if (!($column instanceOf Html)) {
            $column = Html::el('span')->setText($column);
        }
        if ($this->editable) {
            $column->class[] = 'editable';
            if (!isset($column->data['value'])) {
                $column->data['value'] = $column->getText();
            }
            $column->data['url'] = $this->getGrid()->link('edit!');
            $column->data['name'] = $this->getName();
            $column->data['id'] = $this->getGrid()->getModel()->getUniqueId($record);
        }
        echo $column;
	}

}
