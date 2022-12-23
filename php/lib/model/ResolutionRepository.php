<?php

namespace model;

class ResolutionRepository extends EntityRepository {
    // loaders
    //
    // NOTE: loaders throw an exception when the thing is not found in the db
    public function loadEntityById($id) {
        $entity = $this->findEntityById($id);
        if ( $entity === null ) {
            throw new \Exception("unable to load resolution by id $id");
        }
        return $entity;
    }

    public function deleteEntityById($id) {
        $this->_deleteEntityById('resolution', $id);
    }   

    public function loadEntityByUserAndId(User $user, $id) {
        $entity = $this->loadEntityById($id);
        if ($entity->getHabit()->getUser()->getId() !== $user->getId()) {
            throw new \Exception('entity does not belong to this user.');
        }
        return $entity;
    }
    
    
    public function constructEntityFromRow($row) {
        $habit = \model\EntityRepository::byName($this->db, 'habit')->loadEntityById($row['habit_id']);

        $entity = new Resolution($this->db);
        $entity->setHabit($habit);
        $entity->setId($row['id']);
        $entity->setSeq($row['seq']);
        $entity->setName($row['name']);
        $entity->setDescription($row['description']);
        $entity->setAbbreviation($row['abbreviation']);
        $entity->setFulfilment($row['fulfilment']);
        $entity->setCreatedTime(new \DateTime($row['created_time']));
        return $entity;
    }

    private function getSelectClause($prefix = null) {
        $fields = array('id', 'habit_id', 'seq', 'name', 'description', 'abbreviation', 'fulfilment', 'created_time');
        return implode(', ', array_map(function($field) use($prefix) { if ( $prefix ) { return $prefix . '.' . '`'.$field.'`'; } else { return '`'.$field.'`'; } }, $fields));
    }

    // finders
    //
    // note: finders do not throw when the thing is not found in the db

    public function findEntityById($id) {
        $id = intval($id);
        $sql = 'SELECT ' . $this->getSelectClause() . ' FROM resolution WHERE id = ?';
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM resolution WHERE id = ?');
        $sth->execute(array($id));
        $row = $sth->fetch(\PDO::FETCH_ASSOC);

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findEntitiesByHabit(Habit $habit, ?string $orderby = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM resolution WHERE habit_id = ?  ORDER BY ' . $this->getOrderByClause($orderby));
        $sth->execute(array($habit->getId()));
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->constructEntityFromRow($row);
        }

        return $entities;
    }

    public function findAllEntities(?string $orderby = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM resolution ORDER BY ' . $this->getOrderByClause($orderby));
        $sth->execute();

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    private function getOrderByClause(?string $orderby, string $prefix = null) {
        switch ( $orderby ) {
        case 'id':
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
            return $this->withPrefix('name', $prefix);
        case 'name-desc':
            return $this->withPrefix('name', $prefix) . ' DESC';
        case 'seq':
        case 'seq-asc':
        default:
            return $this->withPrefix('seq', $prefix);
        case 'seq-desc':
            return $this->withPrefix('seq', $prefix) . ' DESC';
        }
    }

    public function changeSeqs($id, $direction) {
        $changeFromEntity = $this->findEntityById($id);
        $habit = $changeFromEntity->getHabit();
        $entities = $this->findEntitiesByHabit($habit);

        $this->_changeSeqs('resolution', $entities, $changeFromEntity, $direction);
    }
}
