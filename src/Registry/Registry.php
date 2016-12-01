<?php 

namespace Keyhole\Registry;

/**
 * This class checks for the existence and integrity of field_registry and table_registry tables
 * If not found, they are created.
 */
class Registry {
	
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;
	
	/**
	 * 
	 * @var string
	 */
	private $prefix;
	
	/**
	 * @var \Keyhole\Registry\Setup
	 */
	private $setup;
		
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredTableMapper
	 */
	private $tableMapper;
	
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredFieldMapper
	 */
	private $fieldMapper;
	
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredAccessMapper
	 */
	private $accessMapper;
	
	/**
	 * 
	 * @param \Doctrine\DBAL\Connection $conn
	 * @param string $prefix	optional string to prefix registry tables
	 */
	public function __construct($conn, $prefix = '') {
		
		$this->conn = $conn;
		$this->prefix = $prefix;
		$this->fieldMapper = new RegisteredFieldMapper($conn, $prefix);
		$this->tableMapper = new RegisteredTableMapper($conn, $prefix);
		$this->accessMapper = new RegisteredAccessMapper($conn, $prefix);
		$this->setup = new Setup($this->conn, $prefix, $this->fieldMapper, $this->tableMapper, $this->accessMapper);
		
		$this->updateSchema();
		
	}
	
	/**
	 * 
	 * @param string $accessor	the accessor value to match in the RegisteredAccess table
	 * @param string $accessLevel	the accessLevel value to match in the RegisteredAccess table
	 * @param array $conditions	an array of additional conditions for selecting tables
	 */
	public function accessTables($accessor = '', $accessLevel = '', $conditions = array()) {
		
		$conditions['join'] = !empty($conditions['join']) ? $conditions['join'] : array();
		$conditions['where'] = !empty($conditions['where']) ? $conditions['where'] : array();
		$conditions['params'] = !empty($conditions['params']) ? $conditions['params'] : array();
		
		if(!empty($accessor) || !empty($accessLevel)) {
			$conditions['join'][] = array('table'=>$this->accessMapper->getTableName(), 'on'=>$this->tableMapper->getTableName() . '.id = ' . $this->accessMapper->getTableName() . '.table_id');
			
			if(!empty($accessor)) {
				$conditions['where'][] = array('field'=>$this->accessMapper->getTableName() . '.' . $this->accessMapper->mapPropertyToColumn('accessor'), 'operator'=>'=', 'value'=>'?');
				$conditions['params'][] = $accessor;
			}
			
			if(!empty($accessLevel)) {
				$conditions['where'][] = array('field'=>$this->accessMapper->getTableName() . '.' . $this->accessMapper->mapPropertyToColumn('accessLevel'), 'operator'=>'=', 'value'=>'?');
				$conditions['params'][] = $accessLevel;
			}
		}
		
		return $this->listTables($conditions);
		
	}
	
	/**
	 * 
	 * @param string $accessor	the accessor value to match in the RegisteredAccess table
	 * @param string $accessLevel	the accessLevel value to match in the RegisteredAccess table
	 * @param array $conditions	an array of additional conditions for selecting fields
	 */
	public function accessFields($accessor = '', $accessLevel = '', $conditions = array()) {
		
		$conditions['join'] = !empty($conditions['join']) ? $conditions['join'] : array();
		$conditions['where'] = !empty($conditions['where']) ? $conditions['where'] : array();
		$conditions['params'] = !empty($conditions['params']) ? $conditions['params'] : array();
		
		if(!empty($accessor) || !empty($accessLevel)) {
			$conditions['join'][] = array('table'=>$this->accessMapper->getTableName(), 'on'=>$this->fieldMapper->getTableName() . '.table_id = ' . $this->accessMapper->getTableName() . '.table_id');
			
			if(!empty($accessor)) {
				$conditions['where'][] = array('field'=>$this->accessMapper->getTableName() . '.' . $this->accessMapper->mapPropertyToColumn('accessor'), 'operator'=>'=', 'value'=>'?');
				$conditions['params'][] = $accessor;
			}
			
			if(!empty($accessLevel)) {
				$conditions['where'][] = array('field'=>$this->accessMapper->getTableName() . '.' . $this->accessMapper->mapPropertyToColumn('accessLevel'), 'operator'=>'=', 'value'=>'?');
				$conditions['params'][] = $accessLevel;
			}
		}
		
		return $this->listFields($conditions);
		
	}
	
	public function listTables($conditions = array()) {
		return $this->tableMapper->selectAll($conditions);
	}
	
	public function getTable($id) {
		return $this->tableMapper->findById($id);
	}
		
	public function listFields($conditions = array()) {
		return $this->fieldMapper->selectAll($conditions);
	}
	
	public function getField($id) {
		return $this->fieldMapper->findById($id);
	}
	
	public function listAccess($conditions = array()) {
		return $this->accessMapper->selectAll($conditions);
	}
	
	public function getAccess($id) {
		return $this->accessMapper->findById($id);
	}
	
	public function getPrefix() {
		return $this->prefix;
	}
	
	public function getTableMapper() {
		return $this->tableMapper;
	}
	
	public function getFieldMapper() {
		return $this->fieldMapper;
	}
	
	public function getAccessMapper() {
		return $this->accessMapper;
	}
	
	public function getTableManager($id) {
		return new \Keyhole\Table\TableManager($this->conn, $id, $this);
	}
	
	public function getNewTable() {
		return new RegisteredTable();
	}
	
	public function getNewField($id) {
		$field = new RegisteredField();
		$field->observe('tableId', $id);
		return $field;
	}
	
	public function getNewAccess($id) {
		$access = new RegisteredAccess;
		$access->observe('tableId', $id);
		return $access;
	}
	public function saveTable(RegisteredTable $table) {
		$table->setName($this->normalizeName($table->getName()));
		return $this->tableMapper->save($table);
	}
	
	public function saveField(RegisteredField $field) {
		$field->setName($this->normalizeName($field->getName()));
		return $this->fieldMapper->save($field);
	}
	
	public function saveAccess(RegisteredAccess $access) {
		return $this->accessMapper->save($access);
	}
	
	public function deleteTable(RegisteredTable $table) {
		return $this->tableMapper->delete($table);
	}
	
	public function deleteField(RegisteredField $field) {
		return $this->fieldMapper->delete($field);
	}
	
	public function deleteAccess(RegisteredAccess $access) {
		return $this->accessMapper->delete($access);
	}
	
	private function updateSchema() {
		if($this->setup->migrationsNeeded()) {
			$this->setup->doMigrations();
		}
	}
	
	public function normalizeName($name) {
		$pattern = '/[^a-zA-Z0-9_]/';
		$name = preg_replace($pattern, '', $name);
		$name = substr($name, 0, 127);
		return $name;
	}
	
}

?>