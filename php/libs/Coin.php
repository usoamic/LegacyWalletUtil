<?php

class Coin {
    public static function toSat($coin) {
        return $coin*1e8;
    }

    public static function toCoin($sat) {
        return $sat/1e8;
    }

    public static function toCoinPlainString($sat) {
        return self::toPlainString(self::toCoin($sat));
    }

    public static function toPlainString($amount) {
        $strAmount = number_to_string($amount);
        $explodeAmount = explode('.', $strAmount);
        $beforePoint = $explodeAmount[0];
        $afterPoint = rtrim($explodeAmount[1], '0');
        return $beforePoint.((!is_empty($afterPoint)) ? ('.'.rtrim($afterPoint, '0')) : '');
    }
}
