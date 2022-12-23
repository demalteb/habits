<?php

namespace model;

class ResolutionsDates extends Entity {
    private $resolution = null;
    private $date = null;
    private $comment = null;

    public function __construct($db) {
        parent::__construct($db);
    }

    public function getResolution() { return $this->resolution; }
    public function setResolution($resolution) { $this->resolution = $resolution; }
    public function getDate() { return $this->date; } 
    public function setDate($date) { $this->date = $date; } 
    public function getComment() { return $this->comment; }
    public function setComment($comment) { $this->comment = $comment; }
    public function getCreatedTime() { return $this->createdTime; } 

    public function loadNextResolution() {
        $habit = $this->getResolution()->getHabit();
        $sth = $this->db->prepare('SELECT id FROM resolution WHERE habit_id = ? AND id <> ? AND seq > ? ORDER BY seq LIMIT 1');
        $sth->execute([$habit->getId(), $this->getResolution()->getId(), $this->getResolution()->getSeq()]);
        $row =  $sth->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $sth = $this->db->prepare('SELECT id FROM resolution WHERE habit_id = ? AND id <> ? ORDER BY seq LIMIT 1');
            $sth->execute([$habit->getId(), $this->getResolution()->getid()]);
            $row = $sth->fetch(\PDO::FETCH_ASSOC);
        }
        $resId = $row['id'];
        $this->resolution = \model\EntityRepository::byName($this->db, 'resolution')->loadEntityById($resId);
    }


    public function save() {
        $this->_save('resolutions_dates');
    }

    protected function createRow() {
        $row = array(
            'resolution_id' => $this->resolution->getId(),
            'date' => $this->date->format('Y-m-d'),
            'comment' => \Util::nullIfEmpty($this->comment),
        );
        return $row;
    }


    public function toJson() {
        return (object)array(
            'id' => (int)$this->id, 
            'resolution' => $this->resolution->toJson(),
            'comment' => $this->comment,
            'date' => $this->date->format('Y-m-d'),
            'createdTime' => $this->createdTime ? $this->createdTime->format('Y-m-d h:i:s') : null,
        );
    }
}
