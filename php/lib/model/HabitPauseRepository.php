<?php

namespace model;

class HabitPauseRepository extends EntityRepository {
    // loaders
    //
    // NOTE: loaders throw an exception when the thing is not found in the db
    public function loadEntityById($id) {
        $entity = $this->findEntityById($id);
        if ( $entity === null ) {
            throw new \Exception("unable to load habit pause by id $id");
        }
        return $entity;
    }

    public function deleteEntityById($id) {
        $this->_deleteEntityById('habit_pause', $id);
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

        $entity = new HabitPause($this->db);
        $entity->setHabit($habit);
        $entity->setId($row['id']);
        $entity->setStartDate(new \DateTime($row['start_date']));
        $entity->setEndDate(\Util::dateOrNull($row['end_date']));
        $entity->setDescription($row['description']);
        $entity->setCreatedTime(new \DateTime($row['created_time']));
        $entity->setChangedTime(new \DateTime($row['changed_time']));
        return $entity;
    }

    private function getSelectClause($prefix = null) {
        $fields = array('id', 'habit_id', 'start_date', 'end_date', 'description', 'created_time', 'changed_time');
        return implode(', ', array_map(function($field) use($prefix) { if ( $prefix ) { return $prefix . '.' . '`'.$field.'`'; } else { return '`'.$field.'`'; } }, $fields));
    }

    // finders
    //
    // note: finders do not throw when the thing is not found in the db

    public function findEntityById($id) {
        $id = intval($id);
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM habit_pause WHERE id = ?');
        $sth->execute(array($id));
        $row = $sth->fetch(\PDO::FETCH_ASSOC);

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findEntitiesByHabit(Habit $habit, ?string $orderby = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM habit_pause WHERE habit_id = ?  ORDER BY ' . $this->getOrderByClause($orderby));
        $sth->execute(array($habit->getId()));
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->constructEntityFromRow($row);
        }

        return $entities;
    }

    public function findAllEntities(?string $orderby = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit_pause ORDER BY ' . $this->getOrderByClause($orderby));
        $sth->execute();

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    public function isDateInPause(Habit $habit, \DateTime $date) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit_pause WHERE habit_id = ? AND start_date <= ? AND (end_date IS NULL OR end_date >= ?)');
        $sth->execute([ $habit->getId(), $date->format('Y-m-d'), $date->format('Y-m-d') ]);
        $row = $sth->fetch(\PDO::FETCH_ASSOC);
        return ( isset($row) );
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
        default:
        case 'startdate':
        case 'startdate-asc':
            return $this->withPrefix('start_date', $prefix);
        }
    }

}
