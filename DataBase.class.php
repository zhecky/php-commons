<?php
/**
 * User: zhecky
 * Date: 10/27/13
 * Time: 11:07 PM
 */

class DataBase {

    private $host = '';
    private $user = '';
    private $pass = '';
    private $base = '';
    private $link;
    private $result;

    private $stack = array();

    function __construct() {
        $this->connect() && $this->select_db() && $this->query("SET NAMES UTF-8");
    }

    function __destruct() {
        $this->close();
    }

    private function connect() {
        return ($this->link = @mysql_connect($this->host, $this->user, $this->pass, TRUE));
    }

    function getLink() {
        return $this->link;
    }

    private function select_db() {
        return mysql_select_db($this->base, $this->link);
    }

    function query($sql) {
        return ($this->result = mysql_query($sql, $this->link));
    }

    function insertItemQuery($table, $data) {
        $buffer = "INSERT INTO `{$table}` (";
        $values = "";

        $needComma = false;
        foreach($data as $key=>$value){
            if($needComma){
                $buffer .= ", ";
                $values .= ", ";
            }
            $buffer .= "`{$key}`";
            $value = $this->escapeString($value);
            $values .= "'{$value}'";
            $needComma = true;
        }
        $buffer .= ") VALUES ({$values});";

        return $this->query($buffer);
    }

    function updateQueryUnsafe($table, $update, $condition) {
        $buffer = "UPDATE `{$table}` SET ";

        $needComma = false;
        foreach($update as $key=>$value){
            if($needComma){
                $buffer .= ", ";
            }
            $buffer .= "`{$key}` = {$value}";
            $needComma = true;
        }
        if(sizeof($condition) > 0){
            $buffer .= ' WHERE ';

            $needAnd = false;
            foreach($condition as $key=>$value){
                if($needAnd){
                    $buffer .= " AND ";
                }
                $buffer .= "`{$key}` = {$value}";
                $needAnd = true;
            }
        }
        $buffer .= ';';


        return $this->query($buffer);
    }

    /**
     * Return current row and set result-set pointer to next one
     *
     * @return array current row and go to next
     */
    function getRow() {
        return mysql_fetch_assoc($this->result);
    }

    function numRows() {
        return mysql_num_rows($this->result);
    }

    function affected() {
        return mysql_affected_rows($this->link);
    }

    function close() {
	    if($this->link){
	      return mysql_close($this->link);
      }
    }

    function isConnected() {
        return $this->link ? true : false;
    }

    function getError() {
        return mysql_error();
    }

    function getInsertedId() {
        return mysql_insert_id($this->link);
    }

    function escapeString($str) {
        return mysql_real_escape_string($str, $this->link);
    }

    function pushResult(){
        array_push($this->stack, $this->result);
    }

    function popResult(){
        $this->result = array_pop($this->stack);
    }

}
