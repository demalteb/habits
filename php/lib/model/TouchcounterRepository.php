<?php

namespace model;

class TouchcounterRepository extends EntityRepository {
    public function constructEntityFromRow($row) {
        throw new \RuntimeException('invalid');
    }
    public function loadEntityById($id)  {
        throw new \RuntimeException('invalid');
    }
    public function deleteEntityById($id) {
        throw new \RuntimeException('invalid');
    }
    public function loadTouches() {
        $sth = $this->db->prepare('SELECT id, created_time FROM touch_counter');
        $sth->execute([]);
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }
    public function addTouch() {
        $sth = $this->db->prepare('INSERT INTO touch_counter (id, created_time) VALUES(NULL, NOW())');
        $sth->execute([]);
    }

    public function findAllBetweenDates($startDate, $endDate) {
        $sth = $this->db->prepare('SELECT id, created_time FROM touch_counter WHERE created_time BETWEEN ? AND ?');
        $params = [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')];
        $sth->execute($params);
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }

    public function findDaySumsBetweenDates($startDate, $endDate) {
        $sth = $this->db->prepare('SELECT DATE(created_time) AS created_date, COUNT(*) AS count FROM touch_counter WHERE created_time BETWEEN ? AND ? GROUP BY DATE(created_time)');
        $params = [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')];
        $sth->execute($params);
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }
    public function findSumBetweenDates($startDate, $endDate) {
        $sth = $this->db->prepare('SELECT COUNT(*) AS c FROM touch_counter WHERE created_time BETWEEN ? AND ?');
        $params = [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')];
        $sth->execute($params);
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        if (count($rows) === 0) {
            return 0;
        }
        return $rows[0]['c'];
    }
    public function findAverageBetweenDates($startDate, $endDate) {
        $sum = $this->findSumBetweenDates($startDate, $endDate);
        $all = $this->findDaySumsBetweenDates($startDate, $endDate);
        if (count($all) === 0) {
            return 0;
        }
        return $sum / count($all);
    }
}
