<?php

namespace model;

class UserRepository extends EntityRepository {
    // loaders
    //
    // NOTE: loaders throw an exception when the thing is not found in the db
    public function loadEntityById($id) {
        $entity = $this->findEntityById($id);
        if ( $entity === null ) {
            throw new \Exception("unable to load user by id $id");
        }
        return $entity;
    }

    public function deleteEntityById($id) {
        $job = $this->loadEntityById($id);
        $this->_deleteEntityById('user', $id);
    }   
    
    public function constructEntityFromRow($row) {
        $entity = new User($this->db);
        $entity->setId($row['id']);
        $entity->setName($row['name']);
        $entity->setLogin($row['login']);
        $entity->setPassword($row['password']);
        $entity->setOpenedHabits($row['opened_habits']);
        $entity->setDateStartSpan($row['date_start_span']);
        $entity->setCreatedTime(new \DateTime($row['created_time']));
        $entity->setChangedTime(new \DateTime($row['changed_time']));
        return $entity;
    }

    private function getSelectClause($prefix = null) {
        $fields = array('id', 'name', 'login', 'password', 'opened_habits', 'date_start_span', 'created_time', 'changed_time');
        return implode(', ', array_map(function($field) use($prefix) { if ( $prefix ) { return $prefix . '.' . $field; } else { return $field; } }, $fields));
    }

    // finders
    //
    // note: finders do not throw when the thing is not found in the db

    public function findEntityById($id) {
        $id = intval($id);
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM user WHERE id = ?');
        $sth->execute(array($id));
        $row =  $sth->fetch(\PDO::FETCH_ASSOC);

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findEntityByLoginAndCleartextPassword($login, $cleartextPassword) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM user WHERE login = ?');
        $sth->execute([ $login ]);
        $row =  $sth->fetch(\PDO::FETCH_ASSOC);

        if (!password_verify($cleartextPassword, $row['password'])) {
            throw new \Exception('unable to login');
        }

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findAllEntities($orderby = 'id', $prefix = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM user ORDER BY '. $this->getOrderByClause($orderby, $prefix));
        $sth->execute();

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    private function getOrderByClause($orderby, $prefix = null) {
        switch ( $orderby ) {
        case 'id':
        default:
            return $this->withPrefix('id', $prefix);
        case 'created':
        case 'created-asc':
            return $this->withPrefix('created_time', $prefix);
        case 'created-desc':
            return $this->withPrefix('created_time', $prefix) . ' DESC';
        case 'changed':
        case 'changed-asc':
            return $this->withPrefix('changed_time', $prefix);
        case 'changed-desc':
            return $this->withPrefix('changed_time', $prefix) . ' DESC';
        case 'name':
        case 'name-asc':
            return $this->withPrefix('name', $prefix) . ' ASC';
        case 'name-desc':
            return $this->withPrefix('name', $prefix) . ' DESC';
        }
    }
}
