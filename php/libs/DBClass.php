<?php

class DBClass {
    const
        INCREASE = 1,
        DECREASE = 2;

    private
        $connection,
        $encryptor,

        $dbUser,
        $dbName,
        $dbPassword,
        $dbHost,
        $dbEncryption;
    /*
     * Public
     */

    public function __construct($db_user = DB_USER, $db_name = DB_NAME, $db_password = DB_PASSWORD, $db_host = DB_HOST, $db_encryption = DB_ENCRYPTION) {
        $this->dbUser = $db_user;
        $this->dbName = $db_name;
        $this->dbPassword = $db_password;
        $this->dbHost = $db_host;
        $this->dbEncryption = $db_encryption;
        if($this->dbEncryption) {
            $this->encryptor = new EncryptionClass();
        }
        $this->connect();
    }

    private function dbLog($err = null) { }

    private function error($query, $err = null) {
        if($err != null) {
            $this->dbLog($err);
        }
        print_r($query);
        die_redirect(DATABASE_ERROR);
    }

    private function connect() {
        try {
            $this->connection = new PDO('mysql:host='.$this->dbHost.';dbname='.$this->dbName, $this->dbUser, $this->dbPassword);
        } catch (PDOException $e) {
            $this->error(null, $e->getMessage());
        }
    }

    public function deleteRow($table, $key, $value) {
        $sql = "DELETE FROM `".$table."`".$this->setCondition($key, $value);
        $this->query($sql);
    }

    public function getConnectionStatus() {
        return $this->connection->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }

    public function getServerInfo() {
        return $this->connection->getAttribute(PDO::ATTR_SERVER_INFO);
    }

    public function clearTable($table) {
        $sql = 'TRUNCATE '.$table;
        $this->query($sql);
    }

    public function getValue($table, $cond_key, $cond_value, $key) {
        $row = $this->getRow($table, $cond_key, $cond_value, $key);
        return (is_array($row) && array_key_exists($key, $row)) ? $row[$key] : '';
    }

    public function getRow($table, $cond_key = 'id', $cond_value = '1', $columns = '*') {
        return $this->getRows($table, $cond_key, $cond_value, $columns, false);
    }

    public function getRowByConditions($table, $conditions = array(), $columns = "*", $operator = "AND") {
        return $this->getRowsByConditions($table, $conditions, $columns, $operator, false);
    }

    public function getRows($table, $cond_key = "", $cond_value = "", $columns = "*", $all = true) {
        if(!compare($columns, "*")) {
            $columns = $this->stringWithGravis($columns);
        }
        $sql = "SELECT ".$columns." FROM `".$table."`".$this->setCondition($cond_key, $cond_value);

        return $this->get($sql, $all);
    }

    public function getRowsByConditions($table, $conditions = array(), $columns = "*", $operator = "AND", $all = true) {
        if(!compare($columns, "*")) {
            $columns = $this->stringWithGravis($columns);
        }
        $sql = "SELECT ".$columns." FROM `".$table."`".$this->setConditions($conditions, $operator);
        return $this->get($sql, $all);
    }

    public function get($get_query, $all = true) {
        $this->connect();
        try {
            $result = null;
            $query = $this->connection->query($get_query);
            if(!$query) {
                $this->error($get_query);
            }
            $result = (($all) ? $query->fetchAll(PDO::FETCH_ASSOC) : $query->fetch(PDO::FETCH_ASSOC));

            if(!is_array($result)) {
                $result = array();
            }
            if(!is_empty($result) && $this->dbEncryption) {
                $result = $this->encryptor->decryptArray($result);
            }
            return $result;
        } catch (PDOException $e) {
            $this->error($get_query, $e->getMessage());
        }
    }

    public function query($insert_query) {
        $this->connect();
        try {
            if(!$this->connection->query($insert_query)) {
                $this->error($insert_query);
            }
        } catch (PDOException $e) {
            $this->error($insert_query, $e->getMessage());
        }
    }

    public function checkValuesInDB($table, $conditions = array()) {
        $sql = "SELECT * FROM `".$table."`".$this->setConditions($conditions);

        return (count($this->get($sql)) > 0);
    }

    public function checkTableInDB($table) {
        return $this->checkValuesInDB($table);
    }

    public function checkValueInDB($table, $key = '', $value = '', &$result = null) {
        $result = $this->getRows($table, $key, $value);
        return (count($result) > 0);
    }

    public function checkValueInDBByConditions($table, $conditions = array(), &$result = null) {
        $result = $this->getRowsByConditions($table, $conditions);
        return (count($result) > 0);
    }

    public function updateValueInKey($table, $key, $c_value, $n_value) {
        $this->updateValue($table, $key, $n_value, $key, $c_value);
    }

    public function updateValue($table, $key, $value, $cond_key, $cond_value) {
        $this->updateValues($table, array($key => $value), $cond_key, $cond_value);
    }

    public function insertIf($table, $values, $cond_key, $cond_value) {
        if(!$this->checkValueInDB($table, $cond_key, $cond_value)) {
            $this->insert($table, $values);
            return true;
        }
        return false;
    }

    public function insertIfNotExist($table, $values) {
        if(!$this->checkValueInDBByConditions($table, $values)) {
            $this->insert($table, $values);
            return true;
        }
        return false;
    }

    public function insertOrUpdateValueByConditions($table, $values, $conditions = array()) {
        if($this->checkValueInDBByConditions($table, $conditions)) {
            $this->updateValuesByConditions($table, $values, $conditions);
        }
        else {
            $this->insert($table, $values);
        }
    }

    public function insertOrUpdateValue($table, $values, $cond_key = "id", $cond_value = "1") {
        if($this->checkValueInDB($table, $cond_key, $cond_value)) {
            $this->updateValues($table, $values, $cond_key, $cond_value);
        }
        else {
            $this->insert($table, $values);
        }
    }

    public function changeValue($table, $cond_key, $cond_value, $key, $value, $action) {
        if(!is_numeric($value) || (($action != DBClass::INCREASE) && ($action != DBClass::DECREASE))) return false;
        $oldValue = $this->getValue($table, $cond_key, $cond_value, $key);
        if(is_empty($oldValue) || !is_numeric($oldValue)) return false;
        
        $newValue = ($action == $this::INCREASE) ? ($oldValue + $value) : ($oldValue - $value);
        $this->updateValue($table, $key, $newValue, $cond_key, $cond_value);

        $newDbValue = $this->getValue($table, $cond_key, $cond_value, $key);
        return ($newValue == $newDbValue);
    }

    public function decreaseValue($table, $cond_key, $cond_value, $key, $value = 1) {
        return $this->changeValue($table, $cond_key, $cond_value, $key, $value, $this::DECREASE);
    }

    public function increaseValue($table, $cond_key, $cond_value, $key, $value = 1) {
        return $this->changeValue($table, $cond_key, $cond_value, $key, $value, $this::INCREASE);
    }

    public function updateValues($table, $values, $cond_key, $cond_value) {
        $sql = "UPDATE `".$table."` SET".$this->setUpdatePdo($values).$this->setCondition($cond_key, $cond_value);
        $this->query($sql);
    }

    public function updateValueByConditions($table, $key, $value, $conditions = array(), $operator = "AND") {
        $this->updateValuesByConditions($table, array($key => $value), $conditions, $operator);
    }

    public function updateValuesByConditions($table, $values, $conditions = array(), $operator = "AND", $by = "") {
        $sql = "UPDATE `".$table."` SET".$this->setUpdatePdo($values).$this->setConditions($conditions, $operator).$by;
        $this->query($sql);
    }


    public function insert($table, $values) {
        $sql = "INSERT INTO `".$table."`".$this->setInsertPdo($values);
        $this->query($sql);
    }

    public function close() {
        $this->connection = null;
    }

    /*
     * Private
     */

    private function getSign(&$key) {
        $sign = "=";
        $signArr = array(">", "<");
        $lastCharacter = substr($key, -1);

        if(in_array($lastCharacter, $signArr)) {
            $sign = $lastCharacter;
            $key = substr($key, 0, -1);
        }
        return $sign;
    }

    private function setUpdatePdo($values) {
        $sql = "";

        foreach ($values as $key => $value) {
            if($this->dbEncryption) $value = $this->encryptor->encryptArrayElement($key, $value);
            $sql .= " `".$key."` = ".$this->connection->quote($value).", ";
        }
        return $this->deleteComma($sql);
    }

    private function setInsertPdo($values) {
        $sql_keys = " (";
        $sql_values = ") VALUES (";

        foreach ($values as $key => $value) {
            $sql_keys .= "`".$key."`, ";

            if($this->dbEncryption) $value = $this->encryptor->encryptArrayElement($key, $value);

            $sql_values .= ((is_null($value)) ? 'NULL' : $this->connection->quote($value)).", ";
        }
        $sql = $this->deleteComma($sql_keys).$this->deleteComma($sql_values).");";
        return $sql;
    }

    private function setConditions($conditions, $operator = "AND") {
        if(is_empty($conditions) || !is_array($conditions)) return "";

        $sql = ' WHERE';
        foreach ($conditions as $key => $condition) {
            $sign = ((is_empty($condition)) ?  '' : ' '.$this->getSign($key));

            if($this->dbEncryption) $condition = $this->encryptor->encryptArrayElement($key, $condition);

            $condition = (is_empty($condition)) ? 'is NULL' : ' '.$this->connection->quote($condition);
            $sql .= " `".$key."`".$sign." ".$condition." ".$operator;
        }
        return $this->deleteAND($sql);
    }

    private function setCondition($cond_key, $cond_value) {
        if(!(is_empty($cond_key) && is_empty($cond_value))) {
            return $this->setConditions(array($cond_key => $cond_value));
        }
    }

    private function stringWithGravis($str) {
        $arr = $pieces = explode(" ", $str);
        $columns = " ";
        foreach ($arr as $element) {
            $columns .= "`".$element."`, ";
        }
        return $this->deleteComma($columns);
    }

    private function deleteComma($sql) {
        return substr($sql, 0, -2);
    }


    private function deleteAND($sql) {
        return substr($sql, 0, -3);
    }
}
?>