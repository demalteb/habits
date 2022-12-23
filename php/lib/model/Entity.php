<?php
namespace model;

abstract class Entity {
    protected $db = null;
    protected $id = null; // every Entity has an id in the db so we can determine whether to insert or update
    protected $createdTime = null;
    protected $changedTime = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    // save the entity into the db
    abstract public function save();

    // create an sql-row array from entity data
    abstract protected function createRow();

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getCreatedTime() { return $this->createdTime; }
    public function setCreatedTime($createdTime) { $this->createdTime = $createdTime; }

    public function getChangedTime() { return $this->changedTime; }
    public function setChangedTime($changedTime) { $this->changedTime = $changedTime; }

    protected function _save($tableName) {
        try {
            $row = $this->createRow();
            if ( is_numeric($this->id) ) {
                $this->_update($tableName, $row);
            } else {
                $this->_insert($tableName, $row);
            }
        } catch (\Throwable $e) {
            die($e->getMessage());
        }
    }

    protected function _update($tableName, $row) {
        $sth = $this->db->prepare('UPDATE `' . $tableName . '` SET ' . implode(', ', array_map(function($key) { return '`' . $key . '` = ? '; }, array_keys($row))) . ', changed_time = NOW() WHERE id = ?');
        $sth->execute(array_merge(array_values($row), array($this->id)));
    }

    protected function _insert($tableName, $row) {
        $sth = $this->db->prepare('INSERT INTO `' . $tableName . '` (' . implode(', ', array_map(function($key) { return '`' . $key . '`'; }, array_keys($row))) . ', created_time) VALUES(' . implode(', ', array_map(function($key) { return '?'; }, array_keys($row))) . ', NOW())');
        $sth->execute(array_values($row));
        $sth = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $sth->execute();
        $this->id = $sth->fetchColumn();
    }

    protected function _getNextValue($tableName, $fieldName) {
        $sth = $this->db->prepare('SELECT MAX(`' . $fieldName . '`) + 1 FROM `' . $tableName . '`');
        $sth->execute();
        return $sth->fetchColumn();
    }

}

