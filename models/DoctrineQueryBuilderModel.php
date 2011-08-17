<?php

namespace Gridito;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Nette\ObjectMixin;

/**
 * Doctrine QueryBuilder model
 *
 * @author Jan Marek
 * @license MIT
 */
class DoctrineQueryBuilderModel extends AbstractModel
{
	/** @var \Doctrine\ORM\QueryBuilder */
	private $qb;

	/** @var array */
	private $columnAliases = array();


	/**
	 * Construct
	 * @param \Doctrine\ORM\QueryBuilder $qb query builder
	 */
	public function __construct(QueryBuilder $qb)
	{
		$this->qb = $qb;
	}



	protected function _count()
	{
		$qb = clone $this->qb;
		$qb->select('count(' . $qb->getRootAlias() . ') fullcount');
		return $qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
	}



	public function getItems()
	{
		$this->qb->setMaxResults($this->getLimit());
		$this->qb->setFirstResult($this->getOffset());

		list($sortColumn, $sortType) = $this->getSorting();
		if ($sortColumn) {
			if (strpos($sortColumn, '.') === FALSE) {
				$sortColumn = $this->qb->getRootAlias() . '.' . $sortColumn;
			}
			$this->qb->orderBy($sortColumn, $sortType);
		}

		return $this->qb->getQuery()->getResult();
	}



	public function getItemByUniqueId($uniqueId)
	{
		$qb = clone $this->qb;
		return $qb->andWhere($this->qb->getRootAlias() . '.' . $this->getPrimaryKey() . ' = ' . (int) $uniqueId)->getQuery()->getSingleResult();
	}



	public function getItemValue($item, $valueName)
	{
		$valueNames = explode('.', $valueName);

		$value = $item;

		foreach ($valueNames as $valuePart) {
			if (isset($this->columnAliases[$valuePart])) {
				$valuePart = $this->columnAliases[$valuePart];
			}

			$value = ObjectMixin::get($value, $valuePart);
		}

		return $value;
	}


	public function addColumnAlias($name, $alias)
	{
		$this->columnAliases[$alias] = $name;
		return $this;
	}

}