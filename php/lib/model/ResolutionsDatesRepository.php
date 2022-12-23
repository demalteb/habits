<?php

namespace model;

class ResolutionsDatesRepository extends EntityRepository {
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

    public function findMonthsByHabit($habit, $includedYearAndMonth = null) {
        $sth = $this->db->prepare('select year(`start_date`) as `start_year`, month(`start_date`) as `start_month`, year(`end_date`) as `end_year`, month(`start_date`) as `end_month` from habit where id = ?');
        $sth->execute([$habit->getId()]);
        $habitRow =  $sth->fetch(\PDO::FETCH_ASSOC);

        $sth = $this->db->prepare('select year(min(rd.`date`)) as `start_year`, 
            month(min(rd.`date`)) as `start_month`,
            year(max(rd.`date`)) as `end_year`, 
            month(max(rd.`date`)) as `end_month`
            from resolutions_dates rd, resolution r where rd.resolution_id = r.id and r.habit_id = ?');
        $sth->execute([$habit->getId()]);
        $resolutionsRow =  $sth->fetch(\PDO::FETCH_ASSOC);

        $startYear = $habitRow['start_year'];
        $startMonth = $habitRow['start_month'];
        $endYear = $habitRow['end_year'];
        $endMonth = $habitRow['end_month'];

        if (!$startYear) {
            $startYear = $resolutionsRow['start_year'];
            $startMonth = $resolutionsRow['start_month'];
        }
        if (!$endYear) {
            $endYear = $resolutionsRow['end_year'];
            $endMonth = $resolutionsRow['end_month'];
        }

        $includedYear = $includedYearAndMonth['year'] ?? null;
        $includedMonth = $includedYearAndMonth['month'] ?? null;

        if (!$startYear || $startYear * 12 + $startMonth > $includedYear * 12 + $includedMonth) {
            $startYear = $includedYear;
            $startMonth = $includedMonth;
        }

        if (!$endYear || $endYear * 12 + $endMonth < $includedYear * 12 + $includedMonth) {
            $endYear = $includedYear;
            $endMonth = $includedMonth;
        }

        $entities = [];
        for ($absMonth = $startYear * 12 + $startMonth; $absMonth <= $endYear * 12 + $endMonth; ++$absMonth) {
            $year = floor(($absMonth - 1) / 12);
            $month = (($absMonth - 1) % 12) + 1;
            $entities[] = [ 'year' => $year, 'month' => $month ];
        }

        return $entities;
    }

    public function findEntitiesByHabit($habit) {
        return $this->findEntitiesByHabitBetweenDates($habit);
    }

    public function findEntitiesByHabitBetweenDates($habit, $startDate = null, $endDate = null) {
        $sql = 'SELECT '. $this->getSelectClause('rd') . ' FROM resolutions_dates rd, resolution r 
                                    WHERE r.id = rd.resolution_id AND r.habit_id = ?';
        if ( $startDate ) {
            $sql .= sprintf(" AND rd.`date` >= '%s'", $startDate->format('Y-m-d'));
        }
        if ( $endDate ) {
            $sql .= sprintf(" AND rd.`date` <= '%s'", $endDate->format('Y-m-d'));
        }
        $sql .= ' ORDER BY rd.`date`';

        $sth = $this->db->prepare($sql);
        $sth->execute([$habit->getId()]);

        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $entities = $this->constructEntitiesFromRows($rows);
        return $entities;
    }
    
    public function constructEntityFromRow($row) {
        $entity = new ResolutionsDates($this->db);
        $resolution = \model\EntityRepository::byName($this->db, 'resolution')->loadEntityById($row['resolution_id']);
        $entity->setId($row['id']);
        $entity->setResolution($resolution);
        $entity->setComment($row['comment']);
        $entity->setDate(new \DateTime($row['date']));
        $entity->setCreatedTime(new \DateTime($row['created_time']));
        return $entity;
    }

    private function getSelectClause($prefix = null) {
        $fields = array('id', 'resolution_id', 'comment', 'date', 'created_time');
        return implode(', ', array_map(function($field) use($prefix) { if ( $prefix ) { return $prefix . '.' . $field; } else { return $field; } }, $fields));
    }

    // finders
    //
    // note: finders do not throw when the thing is not found in the db

    public function findEntityById($id) {
        $id = intval($id);
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() . ' FROM resolutions_dates WHERE id = ?');
        $sth->execute(array($id));
        $row =  $sth->fetch(\PDO::FETCH_ASSOC);

        $entity = $this->constructEntityFromRow($row);
        return $entity;
    }

    public function findAllEntities() {
        $sth = $this->db->prepare('SELECT ' . $this->getSelectClause() .' FROM resolutions_dates');
        $sth->execute();

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
        default:
            return $this->withPrefix('name', $prefix);
        case 'name-desc':
            return $this->withPrefix('name', $prefix) . ' DESC';
        }
    }
}
