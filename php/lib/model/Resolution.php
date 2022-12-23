<?php

namespace model;

class Resolution extends Entity {
    private $name = null;
    private $habit = null;
    private $description = null;
    private $abbreviation = null;
    private $fulfilment= 100;
    private $seq = 1;

    public function __construct($db) {
        parent::__construct($db);
    }

    public function getHabit() { return $this->habit; } 
    public function setHabit(Habit $habit) { $this->habit = $habit; } 
    public function getSeq() { return (int)$this->seq; } 
    public function setSeq($seq) { $this->seq = (int)$seq; } 
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    public function getDescription() { return $this->description; }
    public function setDescription(?string $description) { $this->description = $description; }
    public function getAbbreviation() { return $this->abbreviation; }
    public function setAbbreviation(?string $abbreviation) { $this->abbreviation = $abbreviation; }
    public function getFulfilment() { return (float)$this->fulfilment; } 
    public function setFulfilment(float $fulfilment) { $this->fulfilment = (float)$fulfilment; } 

    public function save() {
        if ($this->getSeq() === null) {
            $this->setSeq($this->_getNextValue('resolution', 'seq'));
        }
        $this->_save('resolution');
    }

    protected function createRow() {
        $row = array(
            'seq' => $this->seq,
            'name' => $this->name,
            'description' => \Util::nullIfEmpty($this->description),
            'description' => \Util::nullIfEmpty($this->description),
            'abbreviation' => $this->abbreviation,
            'fulfilment' => $this->fulfilment,
            'habit_id' => $this->habit->getId(),
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
            'habit' => $this->habit->toJson(false), 
            'name' => $this->name, 
            'description' => $this->description,
            'abbreviation' => $this->abbreviation,
            'fulfilment' => (float)$this->fulfilment,
        );
    }
}
