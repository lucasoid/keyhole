<?php 

namespace Keyhole\Registry;

abstract class RegistryMapper {
	
	/**
	 * 
	 * @var string
	 */
	protected $tablePrefix;
	
	/**
	 * 
	 * @var string
	 */
	protected $fullTableName;
	
	/**
	 * 
	 * @var array
	 */
	protected $columnDefinitions;
	
	/**
	 * 
	 * @var string
	 */
	protected $mappedClass;
	
	/**
	 * 
	 * @var array
	 */
	protected $map;
	
	/**
	 * 
	 * @param \Doctrine\DBAL\Connection $conn
	 * @param string $prefix
	 */
	public function __construct($conn, $prefix = '') {
		
		$this->conn = $conn;
		$this->tablePrefix = $prefix;
		$this->setMappedClass();
		$this->setTableName();
		$this->setColumnDefinitions();
		$this->setMap();
		
	}

	/**
	 * 
	 * @return array
	 */
	public function getColumnNames() {
		
		$names = array();
		
		if(isset($this->columnDefinitions) && is_array($this->columnDefinitions)) {
			foreach($this->columnDefinitions as $column) {
				if(is_array($column) && isset($column['name'])) {
					$names[] = $column['name'];
				}
			}
		}
		return $names;
		
	}
	
	/**
	 * Define the table name, including the provided prefix.
	 */
	abstract public function setTableName();
	
	/**
	 * Retrieve the full table name, with prefix.
	 * 
	 * @return string
	 */
	public function getTableName() {
		return $this->fullTableName;
	}
	
	/**
	 * Define the class that corresponds to the mapper.
	 */
	abstract public function setMappedClass();
	
	/**
	 * 
	 * @return string
	 */
	public function getMappedClass() {
		return $this->mappedClass;
	}
	
	/**
	 * Define columns for the registry tables
	 */
	abstract public function setColumnDefinitions();
	
	/**
	 * Retrieve column definitions.
	 * Required array keys: 'name', type'.
	 * Optional array keys: 'options', 'primarykey'=>bool, 'foreignkey=>array(). 
	 * 
	 * @return array
	 */
	public function getColumnDefinitions() {
		
		if(!isset($this->columnDefinitions)) {
			$this->setColumnDefinitions();
		}
		return $this->columnDefinitions;
		
	}
	
	/**
	 * Define map between table columns and object properties
	 */
	abstract public function setMap();
	
	/**
	 * Retrieve column->property map.
	 * 
	 * @return array
	 */
	public function getMap() {
		return $this->map;
	}
	
	/**
	 * 
	 * @param integer $id
	 * @param array $conditions
	 * @return RegisteredEntity|NULL
	 */
	public function findById($id, $conditions = array()) {
		$qb = $this->conn->createQueryBuilder();
		
		$select = !empty($conditions['select']) ? $conditions['select'] : $this->getColumnNames();
		
		$qb
			->select($select)
			->from($this->getTableName())
			->where('id = ?')
			->setParameter(0, $id)
			->setFirstResult(0)
			->setMaxResults(1);
		
		$stmt = $qb->execute();
		
		$row = $stmt->fetch();
		
		if(!empty($row) && $obj = $this->mapProperties($row)) {
			return $obj;
		}
		
		return null;
		
	}
	
	/**
	 * 
	 * @param array $conditions
	 * @return array
	 */
	public function selectAll($conditions = array()) {
		
		$qb = $this->conn->createQueryBuilder();
		
		$select = !empty($conditions['select']) ? $conditions['select'] : $this->getColumnNames();
		
		foreach($select as $k=>$v) {
			$select[$k] = $this->getTableName() . '.' . $v;
		}
		
		$qb->select($select)->from($this->getTableName(), $this->getTableName());
		
		if(!empty($conditions['where']) && is_array($conditions['where'])) {
			foreach($conditions['where'] as $where) {
				if(is_array($where)) {
					$mappedColumn = $this->mapPropertyToColumn($where['field']);
					$field = $mappedColumn ? $mappedColumn : $where['field'];
					$qb->andWhere($field . ' ' . $where['operator'] . ' ' .$where['value']);
				}
			}
		}
		
		if(!empty($conditions['params'])) {
			foreach($conditions['params'] as $k=>$v) {
				$qb->setParameter($k, $v);
			}
		}
		
		if(!empty($conditions['join'])) {
			foreach($conditions['join'] as $join) {
				$qb->join($this->getTableName(), $join['table'], $join['table'], $join['on']);
			}
		}
			
		if(!empty($conditions['firstResult'])) {
			$qb->setFirstResult($conditions['firstResult']);
		}
		
		if(!empty($conditions['maxResults'])) {
			$qb->setMaxResults($conditions['maxResults']);
		}
		
		$objects = array();
		
		try {
			$stmt = $qb->execute();
			while($row = $stmt->fetch()) {
				if($obj = $this->mapProperties($row)) {
					$objects[] = $obj;
				}
			}
		}
		catch(\Exception $e) {
			echo $e->getMessage();
		}
		
		return $objects;
	}
	
	/**
	 * 
	 * @param \Keyhole\Registry\RegistryEntity $object
	 * @return NULL|\Keyhole\Registry\RegistryEntity
	 */
	public function save(RegistryEntity $object) {
		if(method_exists($object, 'getId') && null != $object->getId()) {
			return $this->update($object);	
		}
		else {
			return $this->insert($object);
		}
		
	}

	/**
	 * 
	 * @param \Keyhole\Registry\RegistryEntity $object
	 * @return boolean
	 */
	public function delete(RegistryEntity $object) {
		if(null != $object->getId()) {
			$qb = $this->conn->createQueryBuilder();
			$qb
				->delete($this->getTableName())
				->where('id = ?')
				->setParameter(0, $object->getId())
			;
			try {
				if($stmt = $qb->execute()) {
					return true;
				}
			}
			catch(\Exception $e) {
				
			}
			
		}
		
		return false;
	}
	
	private function insert(RegistryEntity $object) {
		
		$mapped = $this->mapUpdates($object);
		
		if(!empty($mapped['columns']) && !empty($mapped['parameters'])) {
			$qb = $this->conn->createQueryBuilder();
			$qb->insert($this->getTableName());
			foreach($mapped['columns'] as $column) {
				$qb->setValue($column, '?');
			}
			foreach($mapped['parameters'] as $index=>$param) {
				$qb->setParameter($index, $param);
			}
		}
		try {
			if($stmt = $qb->execute()) {
				if($id = $this->conn->lastInsertId()) {
					$object->setId($id);
				}
				return $object;
			}
		}
		catch(\Exception $e) {
			
		}
		
		return null;
	}
	
	private function update(RegistryEntity $object) {
		$mapped = $this->mapUpdates($object);
		
		if(!empty($mapped['columns']) && !empty($mapped['parameters'])) {
			
			$mapped['parameters'][] = $object->getId();
			
			$qb = $this->conn->createQueryBuilder();
			$qb->update($this->getTableName());
			foreach($mapped['columns'] as $column) {
				$qb->set($column, '?');
			}
			$qb->where('id = ?');
			
			foreach($mapped['parameters'] as $index=>$param) {
				$qb->setParameter($index, $param);
			}
		}
		try {
			if($stmt = $qb->execute()) {
				return $object;
			}
		}
		catch(\Exception $e) {
			
		}
		return null;
	}
	
	private function mapProperties($properties) {
		
		$className = $this->getMappedClass();
		
		if(class_exists($className)) {
			
			$obj = new $className;
			foreach($properties as $column=>$value) {
				$propertyname = $this->mapColumnToProperty($column);
				if(property_exists($obj, $propertyname)) {
					$method = 'set' . ucwords($propertyname);
					if(method_exists($obj, $method)) {
						$obj->{$method}($value);
					}
				}
			}
			return $obj;
			
		}
	}
	
	public function mapPropertyToColumn($property) {
		foreach($this->map as $item) {
			if(is_array($item) && $item['property'] == $property) {
				return $item['column'];
			}
		}
	}
	
	public function mapColumnToProperty($column) {
		foreach($this->map as $item) {
			if(is_array($item) && $item['column'] == $column) {
				return $item['property'];
			}
		}
	}
	
	private function mapUpdates(RegistryEntity $object) {
		$columns = array();
		$params = array();
		
		$object->observe('updatedAt', date('Y-m-d H:i:s')); // if this field exists, let's set it to the current time
		
		$properties = $object->getReadyToUpdate();
		foreach($properties as $property) {
			
			if($column = $this->mapPropertyToColumn($property)) {
				
				$method = 'get' . ucwords($property);
				if(method_exists($object, $method)) {
					$columns[] = $column;
					$params[] = $object->{$method}();
				}
			}
		}
		
		return array('columns'=>$columns, 'parameters'=>$params);
	}
	
}