<?php

namespace model;

class Habit extends Entity {
    private $user;
    private $name;
    private $seq;
    private $description;
    private $startDate;
    private $endDate;
    private $resolutionDates;
    private $isFulfilmentRelative;
    private $fulfilmentUnit;
    private $fulfilmentMax;
    private $weekDays;

    public function __construct($db) {
        parent::__construct($db);
        $this->user = null;
        $this->name = null;
        $this->description = null;
        $this->startDate = null;
        $this->endDate = null;
        $this->resolutionDates = null;
        $this->isFulfilmentRelative = true;
        $this->fulfilmentUnit = '%';
        $this->fulfilmentMax = 100;
        $this->weekDays = null;
        $this->pauses = null;
    }

    public function getUser() { return $this->user; }
    public function setUser($user) { $this->user = $user; }
    public function getSeq() { return $this->seq; }
    public function setSeq($seq) { $this->seq = $seq; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
    public function getStartDate() { return $this->startDate; } 
    public function getStartDateFormatted($format='Y-m-d') { return $this->startDate ? $this->startDate->format($format) : null; } 
    public function setStartDate($startDate) { $this->startDate = $startDate; } 
    public function getEndDate() { return $this->endDate; } 
    public function getEndDateFormatted($format='Y-m-d') { return $this->endDate ? $this->endDate->format($format) : null; } 
    public function setEndDate($endDate) { $this->endDate = $endDate; } 
    public function getIsFulfilmentRelative() { return $this->isFulfilmentRelative; }
    public function setIsFulfilmentRelative($isFulfilmentRelative) { $this->isFulfilmentRelative = $isFulfilmentRelative; }
    public function getFulfilmentUnit() { return $this->fulfilmentUnit; }
    public function setFulfilmentUnit($fulfilmentUnit) { $this->fulfilmentUnit = $fulfilmentUnit; }
    public function getFulfilmentMax() { return $this->fulfilmentMax; }
    public function setFulfilmentMax($fulfilmentMax) { $this->fulfilmentMax = $fulfilmentMax; }
    public function getWeekDays() {
        $this->lazyLoadWeekDays();
        return $this->weekDays;
    }
    public function setWeekDays(array $weekDays) {
        $this->weekDays = $weekDays;
    }

    public function hasWeekDay(int $dayNumber) {
        $this->lazyLoadWeekDays();
        if ( count($this->weekDays) === 0 ) {
            return true;
        }
        $found = array_values(array_filter($this->weekDays, function($wd) use ($dayNumber) { return $wd->getDayNumber() === $dayNumber; }));
        if ( count($found) === 1 ) {
            return true;
        }

        return false;
    }

    /**
     * @param int $dayNumber 0-6, where 0 = monday
     */
    public function createWeekDay(int $dayNumber) {
        $weekDay = new HabitWeekDay($this->db);
        $weekDay->setHabit($this);
        $weekDay->setDayNumber($dayNumber);
        $weekDay->save();
        $this->weekDays = EntityRepository::byName($this->db, 'habit_weekday')->findEntitiesByHabit($this);
    }

    public function removeWeekDay(int $dayNumber) {
        EntityRepository::byName($this->db, 'habit_weekday')->deleteEntityByHabitAndDayNumber($this, $dayNumber);
        $this->weekDays = EntityRepository::byName($this->db, 'habit_weekday')->findEntitiesByHabit($this);
    }

    public function createWeekDayIfNotExists(int $dayNumber) {
        $day = EntityRepository::byName($this->db, 'habit_weekday')->findEntityByHabitAndDayNumber($this, $dayNumber);
        if ( !$day ) {
            $this->createWeekDay($dayNumber);
        }
    }

    public function addWeekDay(HabitWeekDay $weekDay) {
        $this->weekDays[] = $weekDay;
    }

    private function lazyLoadWeekDays() {
        if ( $this->weekDays === null ) {
            $this->weekDays = EntityRepository::byName($this->db, 'habit_weekday')->findEntitiesByHabit($this);
        }
    }

    private function lazyLoadPauses() {
        if ( $this->pauses === null ) {
            $this->pauses = EntityRepository::byName($this->db, 'habit_pause')->findEntitiesByHabit($this);
        }
    }

    public function getPauses() {
        $this->lazyLoadPauses();
        return $this->pauses;
    }

    public function setPauses(array $habits) {
        $this->habits = $pauses;
    }

    public function addPause(Pause $pause) {
        $this->pauses[] = $pause;
    }

    public function createPause(\DateTime $startDate, \DateTime $endDate) {
        $pause = new model\HabitPause($this->db);
        $pause->setHabit($this);
        $pause->setStartDate($startDate);
        $pause->endDate($endDate);
        $pause->save();
        $this->pauses = EntityRepository::byName($this->db, 'habit_pause')->findEntitiesByHabit($this);
    }

    private function lazyLoadResolutions() {
        if ($this->resolutions === null) {
            $this->resolutions = EntityRepository::byName($this->db, 'resolution')->findEntitiesByHabit($this);
        }
    }

    public function getResolutionDates() {
        return EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabit($this);
    }

    public function getResolutionDatesJson(bool $includePauseInfo = true, ?\DateTime $startDate = null, ?\DateTime $endDate = null) {
        if ( $includePauseInfo ) {
            return array_map(
                function($rd) {
                    $json = $rd->toJson();
                    $json->isInPause = (!$this->hasWeekDay((int)($rd->getDate()->format('N')) - 1) || $this->isDateInPause($rd->getDate()));
                    return $json;
                },
                EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabitBetweenDates($this, $startDate, $endDate));
        } else {
            return array_map(
                function($rd) {
                    return $rd->toJson();
                },
                EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabitBetweenDates($this, $startDate, $endDate));
        }
    }

    public function getResolutions() {
        return EntityRepository::byName($this->db, 'resolution')->findEntitiesByHabit($this);
    }


    public function getMonthHeadings($includedYearAndMonth = null) {
        return EntityRepository::byName($this->db, 'resolutionsDates')->findMonthsByHabit($this, $includedYearAndMonth);
    }

    public function getResolutionsDatesForMonth($year, $month) {
        $monthLast = date("t", strtotime($year . '-' . $month . '-01'));
        $startDate = new \DateTime($year . '-' . $month . '-01');
        $endDate = new \DateTime($year . '-' . $month . '-' . $monthLast);
        $realEntities = EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabitBetweenDates($this, $startDate, $endDate);
        $entities = [];
        for ($dom = 1; $dom <= $monthLast; ++$dom) {
            $realEntitiesFound = array_values(array_filter($realEntities, function($e) use($dom) { return $e->getDate()->format('d') == $dom; }));
            if (count($realEntitiesFound) === 1) {
                $entities[] = $realEntitiesFound[0];
            } else {
                $rd = new ResolutionsDates($this->db);
                $rd->setDate(new \DateTime($year.'-'.$month.'-'.$dom));
                $entities[] = $rd;
            }
        }
        return $entities;
    }

    public function getResolutionDateForDate($date) {
        $entities = EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabitBetweenDates($this, $date, $date);
        if (count($entities) === 0) {
            $rd = new ResolutionsDates($this->db);
            $rd->setDate($date);
            return $rd;
        } else {
            return $entities[0];
        }
    }

    public function getResolutionDatesBetweenDates($startDate, $endDate) {
        $entities = [];
        for ($date = $startDate; $date <= $endDate; $date->add(new \DateInterval('P1D'))) {
            $entities[] = $this->getResolutionDateForDate(clone $date);
        }
        return $entities;
    }
    
    public function getFulfilmentPercent(float $fulfilment) {
        if ( $this->isFulfilmentRelative ) {
            return $fulfilment / $this->fulfilmentMax * 100;
        } else {
            throw new \Exception('fulfilment is not relative');
        }
    }
    public function getResolutionFulfilmentPercent(Resolution $resolution) {
        return $this->getFulfilmentPercent($resolution->getFulfilment());
    }

    public function getCurrentStreak($type) {
        switch ( $type ) {
        case 'weak':
            $minValue = 0.0001;
            break;
        case 'strong':
            $minValue = $this->fulfilmentMax;
            break;
        default:
            throw new \RuntimeException('wrong type');
        }

        $date = \Util::copyDate(new \DateTime());
        $streaks = array_values(
                array_filter(
                    $this->getStreaks($minValue),
                    function($s) use($date) {
                        return $s['endDate'] >= $date && $s['startDate'] <= $date;
                    }));
        if ( count($streaks) > 0 ) {
            return $streaks[0];
        } else {
            return null;
        }
    }

    public function getLatestStreak($type, $endDate = null) {
        switch ( $type ) {
        case 'weak':
            $minValue = 0.0001;
            break;
        case 'strong':
            $minValue = $this->fulfilmentMax;
            break;
        default:
            throw new \RuntimeException('wrong type');
        }

        $streaks = $this->getStreaks($minValue, 2, null, $endDate);
        if ( count($streaks) > 0 ) {
            return $streaks[count($streaks)-1];
        } else {
            return null;
        }
    }


    public function getLongestStreak($type, $startDate = null, $endDate = null) {
        switch ( $type ) {
        case 'weak':
            $minValue = 0.0001;
            break;
        case 'strong':
            $minValue = $this->fulfilmentMax;
            break;
        default:
            throw new \RuntimeException('wrong type');
        }

        $streaks = $this->getStreaks($minValue, 2, $startDate, $endDate);
        usort(
            $streaks,
            function($s1, $s2) {
                if ( $s1['days'] === $s2['days'] ) return 0;
                return ($s1['days'] < $s2['days']) ? 1 : -1;
            }
        );
        if ( count($streaks) > 0 ) {
            return $streaks[0];
        } else {
            return null;
        }
    }

    public function getStreaks($minValue = 1, $minDays = 2, ?\DateTime $startDate = null, ?\DateTime $endDate = null) {
        $this->lazyLoadWeekDays();

        $resolutionDates = EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabitBetweenDates($this, $startDate, $endDate);
        if ( count($resolutionDates) === 0 ) {
            return [];
        }

        $streaks = [];
        $currentStreak = 0;
        $lastDate = null;
        $startDate = null;
        $endDate = null;
        $firstStartDate = null;
        foreach ( $resolutionDates as $resolutionDate ) {
            if ( $this->getIsFulfilmentEnough($resolutionDate->getResolution(), $minValue) 
                && ( $lastDate === null || 
                    ( $startDate !== null && $this->areDatesInAStreak($lastDate, $resolutionDate->getDate()) ) 
                )
            ) {
                if ( $startDate === null ) {
                    $startDate = $resolutionDate->getDate();
                }
                if ( $firstStartDate === null ) {
                    $firstStartDate = $resolutionDate->getDate();
                }
                $lastDate = $resolutionDate->getDate();
                ++$currentStreak;
            } elseif ($startDate !== null && 
                (!$this->hasWeekDay((int)($resolutionDate->getDate()->format('N')) - 1) || $this->isDateInPause($resolutionDate->getDate()))
            ) {
                if ( $startDate === null ) {
                    $startDate = $resolutionDate->getDate();
                }
                if ( $firstStartDate === null ) {
                    $firstStartDate = $resolutionDate->getDate();
                }
                $lastDate = $resolutionDate->getDate();
                ++$currentStreak;
            } elseif ( $startDate !== null && $currentStreak >= $minDays )  {
                $streaks[] = [ 'days' => $currentStreak, 'startDate' => $startDate, 'endDate' => $lastDate ];
                $currentStreak = 0;
                $lastDate = null;
                $startDate = null;
            } else {
                $currentStreak = 0;
                $lastDate = null;
                $startDate = null;
            }

        }
        if ( $lastDate !== null && $currentStreak >= $minDays ) {
            $streaks[] = [ 'days' => $currentStreak, 'startDate' => $startDate, 'endDate' => $lastDate ];
        }
        return $streaks;
    }

    private function areDatesInAStreak(\DateTime $first, \DateTime $second) {
        if ( (int)$second->diff($first)->format('%a') === 1 ) {
            return true;
        }
        $date = new \DateTime($first->format('Y-m-d'));
        $date->add(new \DateInterval('P1D'));
        for ( ; $date->add(new \DateInterval('P1D')); $date < $second ) {
            if ( $this->hasWeekDay((int)$date->format('N') - 1) && !$this->isDateInPause($date) ) {
                return false;
            }
        }
        return true;
    }

    public function isDateInPause(\DateTime $date) {
        foreach ( $this->getPauses() as $pause ) {
            if ( $pause->getStartDate() <= $date && ($pause->getEndDate() === null || $pause->getEndDate() >= $date) ) {
                return true;
            }
        }

        return false;
    }

    private function getIsFulfilmentEnough(Resolution $resolution, $minValue) {
        if ( $this->isFulfilmentRelative ) {
            return $this->getResolutionFulfilmentPercent($resolution) >= $minValue;
        } else {
            return $resolution->getFulfilment() >= $minValue;
        }
    }


    public function save() {
        if ($this->getSeq() === null) {
            $this->setSeq($this->_getNextValue('habit', 'seq'));
        }
        $this->_save('habit');
    }

    public function findFirstResolution() {
        $resolutions = EntityRepository::byName($this->db, 'resolution')->findEntitiesByHabit($this);
        if (!$resolutions || count($resolutions) === 0) {
            throw new \Exception('no resolutions found for habit');
        }
        return $resolutions[0];
    }


    protected function createRow() {
        $row = array(
            'user_id' => $this->user->getId(),
            'seq' => $this->seq,
            'name' => $this->name,
            'description' => \Util::nullIfEmpty($this->description),
            'start_date' => $this->startDate ? $this->startDate->format('Y-m-d') : null,
            'end_date' => $this->endDate ? $this->endDate->format('Y-m-d') : null,
            'is_fulfilment_relative' => \Util::boolToInt($this->isFulfilmentRelative),
            'fulfilment_unit' => $this->fulfilmentUnit,
            'fulfilment_max' => $this->fulfilmentMax,
        );
        if ( is_numeric($this->id) ){
            $row['id'] = $this->id;
        }
        return $row;
    }


    public function toJson($withSubEntities = false, ?\DateTime $startDate = null, ?\DateTime $endDate = null) {
        $json = (object)array(
            'id' => (int)$this->id, 
            'name' => $this->name, 
            'description' => $this->description,
            'startDate' => $this->startDate ? $this->startDate->format('Y-m-d') : null,
            'endDate' => $this->endDate ? $this->endDate->format('Y-m-d') : null,
            'isFulfilmentRelative' => (bool)$this->isFulfilmentRelative,
            'fulfilmentUnit' => (string)$this->fulfilmentUnit,
            'fulfilmentMax' => (float)$this->fulfilmentMax,
        );
        if ($withSubEntities) {
            $strongStreaksMinValue = $this->fulfilmentMax;
            $weakStreaksMinValue = 0.0001;
            $json->resolutions = array_map(function($r) { return $r->toJson(); }, $this->getResolutions());
            $json->resolutionDates = array_map(function($rdj) { 
                if ( $this->isFulfilmentRelative ) {
                    $rdj->resolution->fulfilmentPercent = $this->getFulfilmentPercent($rdj->resolution->fulfilment);
                }
                return $rdj;
            }, $this->getResolutionDatesJson(true, $startDate, $endDate));
            $json->streaks = (object)[
                'strong' => array_map(function($s) { return (object)[
                    'days' => $s['days'],
                    'startDate' => $s['startDate']->format('Y-m-d'),
                    'endDate' => $s['endDate']->format('Y-m-d'),
                ]; }, $this->getStreaks($strongStreaksMinValue, 2, $startDate, $endDate)),
                'weak' => array_map(function($s) { return (object)[
                    'days' => $s['days'],
                    'startDate' => $s['startDate']->format('Y-m-d'),
                    'endDate' => $s['endDate']->format('Y-m-d'),
                ]; }, $this->getStreaks($weakStreaksMinValue, 2, $startDate, $endDate)),
            ];
        }
        return $json;
    }

    public function getAverageSuccess(\DateTime $startDate, \DateTime $endDate) {
        $entities = EntityRepository::byName($this->db, 'resolutionsDates')->findEntitiesByHabitBetweenDates($this, $startDate, $endDate);
        if ( count($entities) === 0 ) {
            return 0;
        } else {
            return array_reduce($entities, function($carry, $item) { return $carry + $this->getResolutionFulfilmentPercent($item->getResolution()); }, 0) / count($entities);
        }
    }
}
