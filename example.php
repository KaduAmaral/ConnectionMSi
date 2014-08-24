<?php 
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);



function __autoload($class){
   $dir = './';
   $ext = '.php';
   if (file_exists($dir.$class.$ext)) require_once $dir.$class.$ext;
   else exit('Coul\'d open '.$class.'!');
}


$con = new ConnectionMSi('localhost','root','','test');
$con->DebugIn();

?><!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>ConnectionMSi Test</title>
   <style>
      body {
         margin: 0;
         padding: 0;
         background-color: #757575;
      }
      main {
         display: block;
         margin: 0 auto;
         width: 620px;
      }
      label {
         display: block;
         padding: 10px 20px;
         margin: 50px auto 0;
         background-color: #F0F0F0;
         color: #06C;
         font-family: Verdana, Arial, sans-serif;
         font-size: 24px;
         box-shadow: 2px 3px 7px rgba(0,0,0,0.7);
      }
      pre {
         display: block;
         margin: 0 auto 50px;
         padding: 20px;
         border-top: 1px solid #FFF;
         max-width: 100%;
         background-color: #F8F8F8;
         color:#333;
         white-space: pre-wrap;       /* css-3 */
         white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
         white-space: -pre-wrap;      /* Opera 4-6 */
         white-space: -o-pre-wrap;    /* Opera 7 */
         word-wrap: break-word;       /* Internet Explorer 5.5+ */
         box-shadow: 2px 3px 7px rgba(0,0,0,0.7);
      }
      table {
         border-collapse: collapse;
         width: 100%;
      }
      th{
         font-weight: bold;
      }
      table, th, td {
          padding: 4px;
          border-color: #999;
      }
   </style>
</head>
<body>
<main>
   <?php

   echo '<label>Drop Statement</label>';
   echo '<pre>';
   echo $con->Drop('tab_teste');
   echo '</pre>';

   $fields = Array(
      'id' => Array(
         'type' => 'int',
         'size' => '4',
         'comment' => 'first key',
         'auto' => true
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
   echo '<label>Create Statement</label>';
   echo '<pre>';
   echo $con->Create('tab_teste',$fields,'id','InnoDB',false);
   echo '</pre>';

   echo '<label>Insert Statement</label>';
   echo '<pre>';
   echo $con->Insert('tab_teste',Array('id'=>1,'name' => 'First Record', 'col3' => 'test '));
   echo PHP_EOL;
   echo $con->Insert('tab_teste',Array('id'=>2,'name' => 'Second Record', 'col3' => 'test '));
   echo PHP_EOL;
   echo $con->Insert('tab_teste',Array('id'=>3,'name' => 'Third Record', 'col3' => 'test '));
   echo PHP_EOL;
   echo $con->Insert('tab_teste',Array('name' => 'Quarto', 'col3' => '4 '));
   echo PHP_EOL;
   echo $con->Insert('tab_teste',Array('name' => 'Quinto', 'col3' => '5 '));
   echo PHP_EOL;
   echo $con->Insert('tab_teste',Array('name' => 'Sexto', 'col3' => '6 '));
   echo '</pre>';

   echo '<label>Delete Statement</label>';
   echo '<pre>';
   echo $con->Delete('tab_teste', Array('id'=>1));
   echo '</pre>';

   echo '<label>Update Statement</label>';
   echo '<pre>';
   echo $con->Update('tab_teste',Array('name' => 'Now this is the first record', 'col3' => 'First record '), Array('id'=>2));
   echo '</pre>';

   $where = Array(
      'id' => array('NOT' => array(1,'>>>',6,array(3,5))),
      'OR',
      'col3' => array('LIKE' => 'recor')
   );

   echo '<label>INSERT Statement</label>';
   echo '<pre>';
   echo $con->Select('tab_teste',$where);
   echo '</pre>';


   $con->DebugOut();


   echo '<label>Select Statement</label>';
   $res = $con->Select('tab_teste',$where);
   $tab = $res->fetch_all(MYSQLI_ASSOC);
   if (is_array($tab)){
      $_cols = Array();
      foreach ($tab as $key => $row) {
         foreach ($row as $col => $val) {
            if (count($_cols) != count($row)) $_cols[] = $col;
            else break;
         }
         break;
      }
      echo '<pre><table cellpadding="0" cellspacing="0" border="1"><thead><tr>';
      foreach ($_cols as $colun) {
         echo "<th>{$colun}</th>";
      }
      echo '</tr></thead><tbody>';

      foreach ($tab as $key => $row) {
         echo '<tr>';
         foreach ($row as $col => $val) {
            echo "<td>{$val}</td>";
         }
         echo '</tr>';
      }
      echo '</tbody></table></pre>';
   } else {
      echo '<pre>'.$con->_lastSql.'</pre>';
   }

   ?>
</main>
</body>
</html>
