<?php

namespace App\FizzBuzz;

/**
 * Class FizzBuzz
 * ToDo: implement the interface FizzBuzzInterface here
 * @package App\FizzBuzz
 */
class FizzBuzz implements FizzBuzzInterface
{

    protected static $sayFizz = 3;
    protected static $sayBuzz = 5;

    public static function getSpecific(int $nr):string 
    {
        switch($nr) {
            case $nr % self::$sayFizz === 0 && $nr % self::$sayBuzz === 0:
                return "FizzBuzz";
                break;
            case $nr % self::$sayFizz === 0:
                return "Fizz";
                break;
            case $nr % self::$sayBuzz === 0:
                return "Buzz";
                break;
            default:
                return strval($nr);
        }
    }

    public static function getRange(int $start, int $end, int $step=1): array
    {
        $result = array();
        for($x=$start; $x<=$end; $x+=$step)
        {
            $result[] = self::getSpecific($x);
        }
        return $result;
    }
}
