<?php

class Util {
    public static function jsonReturn($status = 'ok', $info = []) {
        $info['status'] = $status;
        header('Content-type: text/json');
        die(json_encode($info));
    }

    public static function nullIfEmpty($val) {
        if ( $val && strlen($val) ) return $val;
        else return null;
    }

    public static function defaultIfEmpty($val, $default = null) {
        if ( $val && strlen($val) ) return $val;
        else return $default;
    }

    public static function dateOrNull($val) {
        if ( $val && strlen($val) ) return new \DateTime($val);
        else return null;
    }
    public static function isCurrentMonthHeading($monthHeading) {
        $now = new \DateTime();
        return ($monthHeading['year'] == $now->format('Y') && $monthHeading['month'] == $now->format('m'));
    }

    public static function intToBool(int $val) {
        return (1 === $val);
    }

    public static function boolToInt(bool $val) {
        return $val ? 1 : 0;
    }

    public static function copyDate(\DateTime $d) {
        return new \DateTime($d->format('Y-m-d'));
    }

    public static function formatDate(?\DateTime $date, string $format='Y-m-d') {
        return $date ? $date->format($format) : '';
    }

    public const weekDayNames = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
}
