<?php

namespace model;

class HabitPause extends Entity {
    private $habit = null;
    private $startDate = null;
    private $endDate = null;
    private $description = null;

    public function __construct($db) {
        parent::__construct($db);
        $this->startDate = new \DateTime();
    }

    public function getHabit() { return $this->habit; } 
    public function setHabit(Habit $habit) { $this->habit = $habit; } 
    public function getStartDate() { return $this->startDate; } 
    public function getStartDateFormatted($format='Y-m-d') { return $this->startDate ? $this->startDate->format($format) : null; } 
    public function setStartDate(?\DateTime $startDate) { $this->startDate = $startDate; }
    public function getEndDate() { return $this->endDate; } 
    public function getEndDateFormatted($format='Y-m-d') { return $this->endDate ? $this->endDate->format($format) : null; } 
    public function setEndDate(?\DateTime $endDate) { $this->endDate = $endDate; }
    public function getDescription() { return $this->description; }
    public function setDescription(?string $description) { $this->description = $description; }

    public function save() {
        $this->_save('habit_pause');
    }

    protected function createRow() {
        $row = array(
            'habit_id' => $this->habit->getId(),
            'start_date' => $this->getStartDateFormatted(),
            'end_date' => $this->getEndDateFormatted(),
            'description' => \Util::nullIfEmpty($this->description),
            // 'created_time' => (new DateTime())->format('Y-m-d h:i:s'),
        );
        if ( is_numeric($this->id) ){
            $row['id'] = $this->id;
        }
        return $row;
    }


    public function toJson() {
        return (object)array(
            'id' => (int)$this->id, 
            'startDate' => $this->getStartDateFormatted(),
            'endDate' => $this->getEndDateFormatted(),
            'description' => $this->description,
        );
    }
}
