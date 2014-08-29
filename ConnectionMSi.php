<?php
/**
* ConnectionMSi - Classe de conexão mysqli
* Manuseio de dados, preparação de querys e etc
*
* @author         kaduamaral - @profile http://linkedin.com/in/kaduamaral
* @repository     https://github.com/KaduAmaral/ConnectionMSi.git
* @date           2014-07-11
* @site           http://devcia.com/
*
*/
class ConnectionMSi extends Mysqli
{
   private $_link;
   private $_host       = '127.0.0.1';
   private $_username   = 'root';
   private $_passwd     = '';
   private $_dbname     = 'test';
   private $_port       = '3306';
   private $_socket     = NULL;
   private $_data       = Array();
   private $_fields     = '';
   private $_values     = '';
   private $_resultData = Array();
   private $_logFile    = './ConnectionMSi_errors.log';
   public $_lastSql    = '';
   private $_lastError  = '';
   private $_debug      = FALSE;


   function __construct($host = NULL, $username = NULL, $passwd = NULL, $dbname = NULL, $port = NULL, $socket = NULL)
   {
      $this->_host      = (is_null($host)      ? $this->_host      : $host);
      $this->_username  = (is_null($username)  ? $this->_username  : $username);
      $this->_passwd    = (is_null($passwd)    ? $this->_passwd    : $passwd);
      $this->_dbname    = (is_null($dbname)    ? $this->_dbname    : $dbname);
      $this->_port      = (is_null($port)      ? $this->_port      : $port);
      $this->_socket    = (is_null($socket)    ? $this->_socket    : $socket);
      parent::__construct($this->_host, $this->_username, $this->_passwd, $this->_dbname, $this->_port);
      return $this;
   }

   function __destruct(){
      $this->close();
   }

   function DebugIn(){
      $this->_debug = TRUE;
   }
   function DebugOut(){
      $this->_debug = FALSE;
   }

   

   public function Insert($table, $data = NULL)
   {
      if (!is_array($data)) return $this->setError('Error: Can\'t prepare data!');

      $this->prepareInsertData($this->saveData($data));

      $sql = "INSERT INTO `{$table}` ({$this->_fields}) VALUES ($this->_values);";
      return $this->ExecuteSQL($sql);
   }


   public function Update($table, $data = NULL, $where = NULL)
   {
      if (!is_array($data)) return $this->setError('Error: Can\'t prepare data!');

      $this->prepareUpdateData($this->saveData($data));

      $where = $this->prepareWhere($where);

      $sql = "UPDATE `{$table}` SET {$this->_fields}{$where};";
      return $this->ExecuteSQL($sql);
   }

   public function Select($table, $where = NULL, $cols = '*', $limit = NULL){
      $_where = $this->prepareWhere($where);
      $_limit = (!is_null($limit) ? 'LIMIT '.$limit : '' );
      $_sql = "SELECT {$cols} FROM `{$table}`{$_where}{$_limit};";
      return $this->ExecuteSQL($_sql);
   }

   public function Delete($table, $where = NULL){
      $_where = $this->prepareWhere($where);
      $_sql = "DELETE FROM {$table}{$_where};";
      return $this->ExecuteSQL($_sql);
   }

   public function Drop($table){
      if (!is_string($table) || (is_string($table) && $table == '')) return $this->setError('Error: Invalid table name for drop statement!');
      return $this->ExecuteSQL("DROP TABLE IF EXISTS `{$table}`;\n");
   }

   public function ExecuteSQL($sql){
      $this->_lastSql = $sql;
      return ($this->_debug ? $sql : $this->query($sql));
   }


   /**
    * @method  :: Create
    * @param   :: table
    *    @type    :: string
    * @param   :: fields
    *    @type    :: array
    *    @structure :: Array ( 
    *                    'fieldname' => Array(
    *                                     'type'      => 'varchar',
    *                                     'size'      => '250,0',
    *                                     'pk'        => false,
    *                                     'null'      => false
    *                                     'deafult'   => '',
    *                                     'comment'   => ''
    *                                   )
    *                  )
   **/

   public function Create($table, $fields, $primaryKey = NULL, $engine = 'MyISAM', $drop = FALSE, $charset = 'utf8', $collate = 'utf8_bin'){

      if (is_null($table) || !is_string($table) || (is_string($table) && $table == '')) return $this->setError('Error: First parameter `table name` is invalid for method `Create`!');
      if (!is_array($fields)) return $this->setError('Error: Second parameter `table fields` is invalid for method `Create`!');

      if (is_array($primaryKey)) {
         $pks = Array();
         foreach ($primaryKey as $key)
            $pks[] = "`{$key}`";
         $_pk = 'PRIMARY KEY ('.implode(',', $pks).')';
      } else if (is_string($primaryKey) && $primaryKey != ''){
         $_pk = "PRIMARY KEY (`{$primaryKey}`)";
      } else {
         $_pk = '';
      }

      $_fields = '';
      foreach ($fields as $field => $values) {
         $_fields .= "`{$field}` ";
         if (isset($values['type'])) $_fields .= $values['type'];
         if (isset($values['size'])) $_fields .= '('.$values['size'].') ';
         if (isset($values['pk']) && $values['pk']) $_fields .= 'PRIMARY KEY ';
         if (isset($values['auto']) && $values['auto']) $_fields .= 'AUTO_INCREMENT ';
         if (isset($values['null']) && $values['null']) $_fields .= 'NULL '; else $_fields .= 'NOT NULL ';
         if (isset($values['deafult'])) $_fields .= 'DEFAULT '.(is_string($values['deafult']) ? '\''.$values['deafult'].'\'' : $values['deafult']).' ';
         if (isset($values['comment'])) $_fields .= "COMMENT '{$values['comment']}' ";

         $_fields .= ', '.PHP_EOL;
      }

      if ($_pk === '')
         $_fields = substr($_fields, 0, strlen($_fields) -2);
      else 
         $_fields .= $_pk;

      $_enginne = '';
      if (is_string($engine) && $engine != '') {
         $_enginne = "ENGINE = {$engine} ";
      }

      $_charset = '';
      if (is_string($charset) && $charset != '' ){
         $_charset = "DEFAULT CHARACTER SET = {$charset} ";
      }

      if (is_string($collate) && $collate != '' ){
         $_collate = "COLLATE = {$collate} ";
      }

      if ($drop) {
         $_sql = "CREATE TABLE `{$table}` (";
      } else {
         $_sql = "CREATE TABLE IF NOT EXISTS `{$table}` (";
      }

      $_sql .= PHP_EOL.$_fields.PHP_EOL.") {$_enginne}{$_charset}{$_collate};";

      if ($drop) $this->Drop($table); //("DROP TABLE IF EXISTS `{$table}`;\n");

      return $this->ExecuteSQL($_sql);

      echo $_sql;

   }

   /**
    **
    **  Can you use too: $mysqli->autocommit(FALSE); $mysqli->Rollback(); $mysqli->Commit();
    **
    **/
   public function Begin(){
      $this->begin_transaction();
   }


   private function prepareInsertData($data)
   {
      $this->_values = '';
      $this->_fields = Array();
      $fields = Array();
      $values = Array();
      foreach ($data as $key => $val)
      {
         $fields[] = "`{$key}`";
         $values[] = (is_null($val) ? 'NULL' : "'{$val}'");
      }
      $this->_fields = implode(', ', $fields);
      $this->_values = implode(', ', $values);

      return $this;
   }

   private function prepareInWhere($in){
      if (array_key_exists('NOT', $in)){
         $in = $in['NOT'];
         $where = ' NOT IN (';
      } else $where = ' IN (';
      if (in_array('>>>', $in)){
         for ($i=$in[0]; $i <= $in[2]; $i++){
            if (isset($in[3]) && in_array($i, $in[3])) continue;
            $where .= $i.', ';
         }
      } else {
         foreach ($in as $vals)
            $where .= ((is_string($vals)) ? "'{$vals}', ": $vals.', ');
      }
      return substr($where, 0, strlen($where) -2).')';
   }

   private function prepareWhere($where) {
      if (is_null($where)) return '';
      if (is_string($where) && $where != '') return ' WHERE '.$where;

      $_where = ' WHERE ';
      foreach ($where as $col => $value) {
         if ($value === 'OR'){
            $_where = substr($_where, 0, strlen($_where)-5).' OR ';
            continue;
         }
         $_where .= "`{$col}`";
         if (is_string($value)) $_where .= " = '{$value}'";
         elseif (is_numeric($value)) $_where .= " = {$value}";
         elseif (is_null($value)) $_where .= " IS NULL";
         elseif (is_array($value)) {
            if (array_key_exists('NOT', $value)){
               $_where .= $this->prepareInWhere($value);//substr($_where, -2);
            } else if (array_key_exists('BETWEEN', $value)){
               if (count($value['BETWEEN']) !== 2) return $this->setError('Error: `where` clause BETWEEN data size is invalid!');
               $_where .= " BETWEEN {$value['BETWEEN'][0]} AND {$value['BETWEEN'][1]}";
            } else if (array_key_exists('LIKE', $value)){
               $_where .= " LIKE '%{$value['LIKE']}%'";
            } else { // IN
               $_where .= $this->prepareInWhere($value);
            }
         }

         $_where = rtrim($_where).' AND ';
      }
      return substr($_where, 0, strlen($_where)-5);
   }

   private function prepareUpdateData($data)
   {
      $this->_values = '';
      $this->_fields = Array();
      $fields = Array();
      $value = '';
      
      foreach ($data as $key => $val)
      {
         $value = (is_null($val) ? 'NULL' : "'{$val}'");
         $fields[] = "`{$key}` = {$value}";
      }

      $this->_fields = implode(', ', $fields);
      return $this;
   }

   protected function saveData($data)
   {
      foreach ($data as $key => $val)
      {
         if (is_null($val)) continue;
         $data[$key] = $this->escape_string($val);
      }
      return $data;
   }

   protected function setError($error)
   {
      $this->_lastError = $error;
      throw new Exception($error, 1);
      error_log($error, 3, $this->_logFile);
      return false;
   }
   public function getError()
   {
      return $this->_lastError;
   }

   protected function setData(array $data)
   {
      $this->_data = $data;
      return $this;
   }

}
