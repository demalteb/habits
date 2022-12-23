<?php
namespace model;

/**
 * an entity repository is what otherwise would be static functions on the entity
 * its purpose is mainly to hold the db connection, so we can do dependency injection
 * on the testing cases
 */
abstract class EntityRepository {
    protected $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    // constructors
    abstract public function constructEntityFromRow($row); // note, a construct... function returns an object

    // loaders
    //
    // NOTE: loaders throw an exception when the thing is not found in the db, and returns an object

    abstract public function loadEntityById($id);
    abstract public function deleteEntityById($id);

    protected function _deleteEntityById($tableName, $id) {
        $this->db->prepare('DELETE FROM `' . $tableName . '` WHERE id = ?')->execute(array($id));
    }

    public function constructEntitiesFromRows($rows) {
        $entities = array();

        foreach ( $rows as $row ) {
            $entities[] = $this->constructEntityFromRow($row);
        }

        return $entities;
    }

    public static function byName($db, $name) {
        switch ($name) {
        case 'habit':
            return new HabitRepository($db);
            break;
        case 'resolution':
            return new ResolutionRepository($db);
            break;
        case 'resolutionsDates':
            return new ResolutionsDatesRepository($db);
            break;
        case 'user':
            return new UserRepository($db);
            break;
        case 'habit_weekday':
            return new HabitWeekDayRepository($db);
            break;
        case 'habit_pause':
            return new HabitPauseRepository($db);
            break;
        case 'touchcounter':
            return new TouchcounterRepository($db);
            break;
        default:
            throw new \Exception("unabe to find EntityRepository by name '$name'");
            break;
        }
    }


    protected function withPrefix($name, $prefix = null) {
        return ( $prefix ? ( $prefix . '.' . $name ) : $name );
    }

    protected function _getNextValue($tableName, $fieldName) {
        $sth = $this->db->prepare('SELECT MAX(`' . $fieldName . '`) + 1 FROM `' . $tableName . '`');
        $sth->execute();
        return $sth->fetchColumn();
    }

    protected function _changeSeqs(string $tableName, array $entities, Entity $changeFromEntity, string $direction) {
        $changeIdxFrom = array_search($changeFromEntity, $entities);

        switch ($direction) {
        case 'up':
            if ($changeIdxFrom === 0) {
                $changeIdxTo = count($entities) - 1;
                return;
            } else {
                $changeIdxTo = $changeIdxFrom - 1;
            }
            break;
        case 'down':
            if ($changeIdxFrom === count($entities) - 1) {
                $changeIdxTo = 0;
            } else {
                $changeIdxTo = $changeIdxFrom + 1;
            }
            break;
        default:
            die('hu');
            throw new \Exception('no such dir');
        }
        $tmpSeq = $this->_getNextValue($tableName, 'seq');
        $changeSeqFrom = $entities[$changeIdxFrom]->getSeq();
        $changeSeqTo = $entities[$changeIdxTo]->getSeq();
        $entities[$changeIdxTo]->setSeq($tmpSeq);
        $entities[$changeIdxTo]->save();
        $entities[$changeIdxFrom]->setSeq($changeSeqTo);
        $entities[$changeIdxFrom]->save();
        $entities[$changeIdxTo]->setSeq($changeSeqFrom);
        $entities[$changeIdxTo]->save();
    }
}

