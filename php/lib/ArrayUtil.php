<?php

class ArrayUtil {
    public static function find($haystack, $func) {
        foreach ( $haystack as $straw ) {
            if ( $func($straw) ) {
                return $straw;
            }
        }
        return null;
    }
    public static function findIndex($haystack, $func) {
        for ( $i = 0; $i < count($haystack); ++$i ) {
            if ( $func($haystack[$i]) ) {
                return $i;
            }
        }
        return false;
    }
    public static function findAll($haystack, $func) {
        $found = [];
        foreach ( $haystack as $straw ) {
            if ( $func($straw) ) {
                $found[] = $straw;
            }
        }
        return $found;
    }

    public static function intersectById($list1, $list2) {
        $res = [];
        foreach ( $list1 as $l1El ) {
            if ( self::find($list2, function($l2El) use ($l1El) { return $l2El->getId() === $l1El->getId(); }) ) {
                $res[] = $l1El;
            }
        }
        return $res;
    }

    public static function diffById($list1, $list2) {
        $res = [];
        foreach ( $list1 as $l1El ) {
            if ( !self::find($list2, function($l2El) use ($l1El) { return $l2El->getId() === $l1El->getId(); }) ) {
                $res[] = $l1El;
            }
        }
        return $res;
    }
}
