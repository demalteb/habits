<?php

namespace model;

class HabitWeekDayRepository extends EntityRepository {
    // loaders
    //
    // NOTE: loaders throw an exception when the thing is not found in the db
    public function loadEntityById($id) {
        $entity = $this->findEntityById($id);
        if ( $entity === null ) {
            throw new \Exception("unable to load weekday by id $id");
        }
        return $entity;
    }

    public function deleteEntityById($id) {
        $job = $this->loadEntityById($id);
        $this->_deleteEntityById('habit_weekday', $id);
    }   

    public function deleteEntityByHabitAndDayNumber(Habit $habit, int $dayNumber) {
        $this->db->prepare('DELETE FROM habit_weekday WHERE habit_id = ? AND day_number = ?')->execute([ $habit->getId(), $dayNumber ]);
    }

    public function constructEntityFromRow($row) {
        $entity = new HabitWeekDay($this->db);
        $habit = EntityRepository::byName($this->db, 'habit')->loadEntityById($row['habit_id']);
        $entity->setHabit($habit);
        $entity->setId((int)$row['id']);
        $entity->setDayNumber((int)$row['day_number']);
        $entity->setCreatedTime(new \DateTime($row['created_time']));
        $entity->setChangedTime(new \DateTime($row['changed_time']));
        return $entity;
    }

    private function getSelectClause($prefix = null) {
        $fields = array('id', 'habit_id', 'day_number', 'created_time', 'changed_time');
        return implode(', ', array_map(function($field) use($prefix) { if ( $prefix ) { return $prefix . '.' . $field; } else { return $field; } }, $fields));
    }

    // finders
    //
    // note: finders do not throw when the thing is not found in the db

    public function findEntityById($id) {
        $id = intval($id);
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM habit_weekday WHERE id = ?');
        $sth->execute(array($id));
        $row =  $sth->fetch(\PDO::FETCH_ASSOC);

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findAllEntities($orderby = 'seq', $prefix = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit_weekday ORDER BY '. $this->getOrderByClause($orderby, $prefix));
        $sth->execute();

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    public function findEntitiesByHabit(Habit $habit, $orderby = 'daynumber', $prefix = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit_weekday WHERE habit_id = ? ORDER BY '. $this->getOrderByClause($orderby, $prefix));
        $sth->execute([ $habit->getId() ]);

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    public function findEntityByHabitAndDayNumber(Habit $habit, int $dayNumber, $prefix = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit_weekday WHERE habit_id = ? AND day_number = ?');
        $sth->execute([ $habit->getId(), $dayNumber ]);

        $row =  $sth->fetch(\PDO::FETCH_ASSOC);
         if ( $row ) {
            $entity = $this->constructEntityFromRow($row);
            return $entity;
         }

        return null;
    }

    private function getOrderByClause($orderby, $prefix = null) {
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
        case 'daynumber':
        case 'daynumber-asc':
        default:
            return $this->withPrefix('day_number', $prefix) . ' ASC';
        case 'daynumber-desc':
            return $this->withPrefix('day_number', $prefix) . ' DESC';
        }
    }

    /** creates and saves a new habit weekday
     */
    public function createEntity(Habit $habit, int $dayNumber) {
        $entity = new HabitWeekDay($this->db);

        $entity->setHabit($habit);
        $entity->setDayNumber($dayNumber);
        $entity->save();

        return $entity;
    }
}
