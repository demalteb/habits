<?php

namespace model;

class User extends Entity {
    private $name;
    private $login;
    private $password;
    private $openedHabits;

    public function __construct($db) {
        parent::__construct($db);
        $this->name = null;
        $this->login = null;
        $this->password = null;
        $this->openedHabits = null;
        $this->dateStartSpan = null;
    }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    public function getLogin() { return $this->login; }
    public function setLogin($login) { $this->login = $login; }
    public function getPassword() { return $this->password; } 
    public function setPassword($password) { $this->password = $password; }
    public function setPasswordFromCleartext($cleartextPassword) {
        $this->setPassword(password_hash($cleartextPassword, PASSWORD_DEFAULT));
    }
    public function getOpenedHabits() { return $this->openedHabits; }
    public function setOpenedHabits($openedHabits) { $this->openedHabits = $openedHabits; }
    public function getDateStartSpan(): ?string { return $this->dateStartSpan; }
    public function setDateStartSpan(?string $dateStartSpan) { $this->dateStartSpan = $dateStartSpan; }

    public function save() {
        if ($this->getName() === null) {
            throw new \Exception('name must not be empty');
        }
        if ($this->getLogin() === null) {
            throw new \Exception('login must not be empty');
        }
        $this->_save('user');
    }

    protected function createRow() {
        $row = array(
            'name' => $this->name,
            'login' => \Util::nullIfEmpty($this->login),
            'password' => \Util::nullIfEmpty($this->password),
            'opened_habits' => \Util::nullIfEmpty($this->openedHabits),
            'date_start_span' => \Util::nullIfEmpty($this->dateStartSpan),
        );
        if ( is_numeric($this->id) ){
            $row['id'] = $this->id;
        }
        return $row;
    }

    public function toJson($withSubEntities) {
        $json = (object)array(
            'id' => $this->id, 
            'name' => $this->name, 
            'login' => $this->login,
            'password' => $this->password,
            'dateStartSpan' => $this->dateStartSpan,
        );
        return $json;
    }
}
