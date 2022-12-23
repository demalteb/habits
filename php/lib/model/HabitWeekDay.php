<?php

namespace model;

class HabitWeekDay extends Entity {
    private $habit;
    private $dayNumber;

    public function __construct($db) {
        parent::__construct($db);
        $this->habit = null;
        $this->dayNumber = null;
    }

    public function getHabit() { return $this->habit; }
    public function setHabit($habit) { $this->habit = $habit; }
    public function getDayNumber() { return (int)$this->dayNumber; }
    public function setDayNumber(int $dayNumber) { $this->dayNumber = $dayNumber; }

    protected function createRow() {
        $row = array(
            'habit_id' => $this->habit->getId(),
            'day_number' => $this->dayNumber,
        );
        if ( is_numeric($this->id) ){
            $row['id'] = $this->id;
        }
        return $row;
    }

    public function save() {
        $this->_save('habit_weekday');
    }

    public function toJson($withSubEntities = false) {
        $json = (object)array(
            'id' => (int)$this->id, 
            'dayNumber' => $this->dayNumber, 
        );
        if ($withSubEntities) {
            $json->habit = $this->habit->toJson(false);
        }
        return $json;
    }
}
