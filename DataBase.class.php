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
        $this->connect() && $this->select_db();
        $this->query("SET NAMES UTF8");
    }

    function __destruct() {
        $this->close();
    }

    private function connect() {
        return ($this->link = @mysqli_connect($this->host, $this->user, $this->pass));
    }

    function getLink() {
        return $this->link;
    }

    private function select_db() {
        return mysqli_select_db($this->link, $this->base);
    }

    function query($sql) {
        $res = ($this->result = mysqli_query($this->link, $sql));
        if(!$res) {
            $err = $this->getError();
            if($err){
                error_log("[mysql] ".$err);
            }
        }
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

    function updateQueryScalar($table, $update, $condition) {
        $buffer = "UPDATE `{$table}` SET ";

        $needComma = false;
        foreach($update as $key=>$value){
            if($needComma){
                $buffer .= ", ";
            }
            $buffer .= "`{$key}` = '".$this->escapeString($value)."'";
            $needComma = true;
        }
        if(sizeof($condition) > 0){
            $buffer .= ' WHERE ';

            $needAnd = false;
            foreach($condition as $key=>$value){
                if($needAnd){
                    $buffer .= " AND ";
                }
                $buffer .= "`{$key}` = '".$this->escapeString($value)."'";

                $needAnd = true;
            }
        }
        $buffer .= ';';

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
        return mysqli_fetch_assoc($this->result);
    }

    function numRows() {
        return mysqli_num_rows($this->result);
    }

    function affected() {
        return mysqli_affected_rows($this->link);
    }

    function close() {
        return mysqli_close($this->link);
    }

    function isConnected() {
        return $this->link ? true : false;
    }

    function getError() {
        return mysqli_error($this->link);
    }

    function getInsertedId() {
        return mysqli_insert_id($this->link);
    }

    function escapeString($str) {
        return mysqli_real_escape_string($this->link, $str);
    }

    function pushResult(){
        array_push($this->stack, $this->result);
    }

    function popResult(){
        $this->result = array_pop($this->stack);
    }

}
