ConnectionMSi
========


ConnectionMSi is a PHP class to carry out the management of the database in a more practical way.


ConnectionMSi é uma classe PHP para realizar a gestão do banco de dados de uma forma mais prática.


Methods

  - [Drop](#drop)
  - [Create](#create)
  - [Insert](#insert)
  - [Update](#update)
  - [Delete](#delete)
  - [Select](#select)
  - ExecuteSQL (generic query)
  - [Transaction](#insert) (Begin, Rollback, Commit)


Connection
-------
```php
$host = 'localhost';
$user = 'root';
$pass = '';
$base = 'test';

$con = new ConnectionMSi($host,$user,$pass,$base);
```


DROP
----
```php
$con->Drop('tab_teste');
```

CREATE
-----
```php
$fields = Array(
      'id' => Array(
         'type' => 'int',
         'size' => '4',
         'comment' => 'first key'
      ),
      'name' => Array(
         'type' => 'varchar',
         'size' => '60',
         'comment' => 'test name'
      ),
      'col3' => Array(
         'type' => 'varchar',
         'size' => '60',
         'default' => NULL,
         'comment' => 'test name'
      )
   );
   $con->Create('tab_teste',$fields,'id','InnoDB',false);
```
The fifth parameter is to drop the table if it exists.

O quinto parâmetro é para eliminar a tabela, se existir.


INSERT
------
```php
$con->Begin();
$data = Array('id'=>1,'name' => 'First Record', 'col3' => 'test ');
$con->Insert('tab_teste',$data);
$data = Array('id'=>2,'name' => 'Second Record', 'col3' => 'test ');
$con->Insert('tab_teste',$data);
$data = Array('id'=>3,'name' => 'Third Record', 'col3' => 'test ');
$con->Insert('tab_teste',$data);
$con->Commit();
```

DELETE
------
```php
$where = Array('id'=>1);
$con->Delete('tab_teste', $where);
```


UPDATE
------
```php
$data = Array(
   'name' => 'Now this is the first record', 
   'col3' => 'First record'
);
$where = Array('id'=>2);
$con->Update('tab_teste',$data, $where);
```


SELECT
------
```php
$where = Array('id' => Array('BETWEEN'=>Array(2,3)));
$res = $con->Select('tab_teste',$where);
$tab = $res->fetch_all(MYSQLI_ASSOC);
```

**Select Result for `$tab`**
```
------------------------------------------------------
| id | name                           | col3         |
------------------------------------------------------
| 2  | Now this is the first record   | First record |
------------------------------------------------------
| 3  | Third Record                   | test         |
------------------------------------------------------
```

WHERE
-----
```php
$where = 'id = 1'; 
// Result: id = 1

$where = Array('id' => 1); 
// Result: id = 1

$where = Array('id' => Array(1,'>>>',10, Array(3,6,8))); 
// Result: id IN (1,2,4,5,7,9,10)

$where = Array('id' => Array('BETWEEN' => Array(1,10)));
// Result:  id BETWEEN 1 AND 10

$where = Array('id' => Array('NOT' => Array(1,2,3,12,45)));
// Result: id NOT IN (1,2,3,12,45)

$where = Array('id' => Array('NOT' => Array(1,'>>>',10, Array(3,6,8)))); 
// Result: id NOT IN (1,2,4,5,7,9,10)
```


Version
----

1.0.0


License
----

GPL v2



Author
------
[Kaduamaral](http://linkedin.com/in/kaduamaral)

[Devcia: http://devcia.com](http://devcia.com)

[GitHub](http://github.com/kaduamaral)
