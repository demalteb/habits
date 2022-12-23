<?php

namespace model;

class HabitRepository extends EntityRepository {
    // loaders
    //
    // NOTE: loaders throw an exception when the thing is not found in the db
    public function loadEntityById($id) {
        $entity = $this->findEntityById($id);
        if ( $entity === null ) {
            throw new \Exception("unable to load habit by id $id");
        }
        return $entity;
    }

    public function deleteEntityById($id) {
        $entity = $this->loadEntityById($id);
        $this->_deleteEntityById('habit', $id);
    }   

    public function loadEntityByUserAndId(User $user, $id) {
        $entity = $this->loadEntityById($id);
        if ($entity->getUser()->getId() !== $user->getId()) {
            throw new \Exception('entity does not belong to this user.');
        }
        return $entity;
    }
    
    public function constructEntityFromRow($row) {
        $entity = new Habit($this->db);
        $user = EntityRepository::byName($this->db, 'user')->loadEntityById($row['user_id']);
        $entity->setUser($user);
        $entity->setId($row['id']);
        $entity->setSeq($row['seq']);
        $entity->setName($row['name']);
        $entity->setDescription($row['description']);
        $entity->setStartDate(new \DateTime($row['start_date']));
        $entity->setEndDate(isset($row['end_date']) ? new \DateTime($row['end_date']) : null);
        $entity->setIsFulfilmentRelative(\Util::intToBool((int)$row['is_fulfilment_relative']));
        $entity->setFulfilmentUnit($row['fulfilment_unit']);
        $entity->setFulfilmentMax((int)$row['fulfilment_max']);
        $entity->setCreatedTime(new \DateTime($row['created_time']));
        $entity->setChangedTime(new \DateTime($row['changed_time']));
        return $entity;
    }

    private function getSelectClause($prefix = null) {
        $fields = array('id', 'user_id', 'seq', 'name', 'description', 'start_date', 'end_date', 'is_fulfilment_relative', 'fulfilment_unit', 'fulfilment_max', 'created_time', 'changed_time');
        return implode(', ', array_map(function($field) use($prefix) { if ( $prefix ) { return $prefix . '.' . $field; } else { return $field; } }, $fields));
    }

    // finders
    //
    // note: finders do not throw when the thing is not found in the db

    public function findEntityById($id) {
        $id = intval($id);
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM habit WHERE id = ?');
        $sth->execute(array($id));
        $row =  $sth->fetch(\PDO::FETCH_ASSOC);

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findAllEntities($orderby = 'seq', $prefix = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit ORDER BY '. $this->getOrderByClause($orderby, $prefix));
        $sth->execute();

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    public function findEntitiesByUser(User $user, $orderby = 'seq', $prefix = null) {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM habit WHERE user_id = ? ORDER BY '. $this->getOrderByClause($orderby, $prefix));
        $sth->execute([ $user->getId() ]);

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
    }

    public function findActiveEntitiesByUserAndDates(User $user, ?\DateTime $startDate, ?\DateTime $endDate, $orderby = 'seq', $prefix = null) {
        return $this->findEntitiesByUserAndDates($user, $startDate, $endDate, true, $orderby, $prefix);
    }

    public function findEntitiesByUserAndDates(User $user, ?\DateTime $startDate, ?\DateTime $endDate, ?bool $isActive = true, $orderby = 'seq', $prefix = null) {
        $sql = 'SELECT ' . $this->getSelectClause() .' FROM habit WHERE user_id = ? ';
        $params = [ $user->getId() ];

        if ( $startDate ) {
            $sql .= ' AND (end_date >= ? OR end_date IS NULL) ';
            $params[] = $startDate->format('Y-m-d');
        }

        if ( $endDate ) {
            $sql .= ' AND (start_date <= ? OR start_date IS NULL) ';
            $params[] = $endDate->format('Y-m-d');
        }

        if ( !is_null($isActive) ) {
            $sql .= ' AND (is_active = ?) ';
            $params[] = $isActive ? 1 : 0;
        }
       
       
        $sql .= ' ORDER BY '. $this->getOrderByClause($orderby, $prefix);
        $sth = $this->db->prepare($sql);
        $sth->execute($params);

        $rows =  $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $this->constructEntitiesFromRows($rows);
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
        case 'name':
        case 'name-asc':
            return $this->withPrefix('name', $prefix) . ' ASC';
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
        $entities = $this->findAllEntities();
        $changeFromEntity = $this->findEntityById($id);

        $this->_changeSeqs('habit', $entities, $changeFromEntity, $direction);
    }

    /** creates and saves a new habit
     */
    public function createEntity(User $user, string $name, 
        ?string $description, ?\DateTime $startDate, ?\DateTime $endDate, 
        bool $isFulfilmentRelative, string $fulfilmentUnit, int $fulfilmentMax
    ) {
        $habit = new Habit($this->db);
        $habit->setUser($user);
        $seq = $this->_getNextValue('habit', 'seq');
        $habit->setSeq($seq);
        $habit->setName($name);
        $habit->setDescription($description);
        $habit->setStartDate($startDate ?? (new \DateTime()));
        $habit->setEndDate($endDate);
        $habit->setIsFulfilmentRelative($isFulfilmentRelative);
        $habit->setFulfilmentUnit($fulfilmentUnit);
        $habit->setFulfilmentMax($fulfilmentMax);
        $habit->save();
        $resolution = new Resolution($this->db);
        $resolution->setHabit($habit);
        $resolution->setSeq($this->_getNextValue('resolution', 'seq'));
        $resolution->setAbbreviation('X');
        $resolution->setName('done');
        $resolution->setFulfilment($fulfilmentMax);
        $resolution->save();
        $resolution = new Resolution($this->db);
        $resolution->setHabit($habit);
        $resolution->setSeq($this->_getNextValue('resolution', 'seq'));
        $resolution->setAbbreviation('0');
        $resolution->setName('not done');
        $resolution->setFulfilment(0);
        $resolution->save();

        return $habit;
    }
}
