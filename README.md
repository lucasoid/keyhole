# Keyhole
## An implementation of Doctrine DBAL designed to simplify user-managed database tables

### What do we have here?

Keyhole is an interface to allow users to create database tables and entries. What you, and they, do with that is TBD.

* Build a registry of tables and columns.
* Define access rules.
* Dynamically create or update tables that have been registered.
* Save, delete, and retrieve data from the created tables.

### Install:
```composer require keyhole/keyhole```

### Get started:
```php
<?php

//get an instance of Doctrine\DBAL\Connection

$connectionParams = array(
  'dbname'=>'my_db',
  'user'=>'my_user',
  'password'=>'my_pw',
  'host'=>'localhost',
  'driver'=>'pdo_mysql'
);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, new \Doctrine\DBAL\Configuration());

//instantiate a registry

$prefix = 'prefix_'; //optional string prefixed to all tables
$registry = new \Keyhole\Registry\Registry($conn, $prefix); //the registry's constructor will build the registry tables if needed
```

### Registry

```php
//Accessing the registry:

$conditions = array(); //array of options for constructing a parameterized query

$tables = $registry->listTables($conditions); //returns an array of all registered tables matching the conditions

$authorized_tables = $registry->accessTables('public', 'view', $conditions); //returns an array of all registered tables matching the given access level and conditions

$my_table = $registry->getTable($id); //retrieve a specific table by ID

$fields = $registry->listFields($conditions); //returns an array of all registered fields matching the conditions

$authorized_fields = $registry->accessFields('public', 'view', $conditions); //returns an array of all registered fields matching the given access level and conditions

$my_field = $registry->getField($id);


//Registering tables, fields, and access rules:

//TABLE
$table = new \Keyhole\Registry\RegisteredTable();
$table->observe('name', 'my_table');
$table->observe('label', 'My Table');
$mapper = new \Keyhole\Registry\RegisteredTableMapper();
if($mapper->save($table)) {
  echo 'table saved!';
}

//FIELD
$field = new \Keyhole\Registry\RegisteredField();
$field->observe('name', 'my_field');
$field->observe('label', 'My Field');
$field->observe('fieldtype', 'string');
$field->observe('tableId', 1);
$mapper = new \Keyhole\Registry\RegisteredFieldMapper();
if($mapper->save($field)) {
  echo 'field saved!';
}

//ACCESS
$access = new \Keyhole\Registry\RegisteredAccess();
$access->observe('tableId', 1);
$access->observe('accessor', 'public');
$access->observe('accessLevel', 'view');
$mapper = new \Keyhole\Registry\RegisteredAccessMapper();
if($mapper->save($access)) {
  echo 'access level saved!';
}
```

### Tables

```php
$table = new \Keyhole\Table\Table($conn, $registeredTable->getId(), $registry);

//MIGRATE
if($table->migrationsNeeded()) {
  $table->doMigrations();
}

//SELECT
$conditions = array();
$conditions['where'] = array('field'=>'my_field', 'operator'='LIKE', 'value'=>'?');
$conditions['params'] = array('%searchterm%');
$rows = $table->select($conditions);
foreach($rows as $row) {
  $data = $row->getData(); //
}

//UPDATE
$row = $rows[0];
$data = $row->getData();
$data['my_field'] = 'text replaced';
$rows[0]->setData($data);
$table->save($row);

//INSERT
$row = new \Keyhole\Table\Row();
$data = array('my_field'=>'new row');
$row->setData($row);
$table->save($row);

//DELETE
$row = $rows[0];
$table->delete($row);

```


