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
            if (isset($this->columnAliases[$sortColumn])) {
                $sortColumn = $this->columnAliases[$sortColumn]->qbName;
            } else {
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
        if (isset($this->columnAliases[$valueName])) {
            $getterPath = $this->columnAliases[$valueName]->getterPath;
        } else {
            $getterPath = $valueName;
        }

		$getters = explode('.', $getterPath);

		$value = $item;

		foreach ($getters as $getter) {
			$value = ObjectMixin::get($value, $getter);
		}

		return $value;
	}


    /**
     * @param string $columnName column name in gridito
     * @param string $getterPath name for getting a value for default renderer (e.g. "image.name" is translated to $entity->getImage()->getName())
     * @param string $qbName name for doctrine query builder (used for ordering)
     * @return \Gridito\DoctrineQueryBuilderModel
     */
	public function addColumnAliases($columnName, $getterPath, $qbName)
	{
		$this->columnAliases[$columnName] = (object) array(
            'getterPath' => $getterPath,
            'qbName' => $qbName,
        );

		return $this;
	}

}