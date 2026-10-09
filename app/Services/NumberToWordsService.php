<?php

namespace App\Services;

class NumberToWordsService
{
    private static array $dictionary = [
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety',
    ];

    public static function convert(float $amount, string $currency = 'INR'): string
    {
        $amount = round($amount, 2);
        $fraction = round(($amount - floor($amount)) * 100);
        $number = (int) floor($amount);

        if ($number === 0 && $fraction === 0) {
            return 'Zero Rupees Only';
        }

        $words = self::convertNumberToIndianSystem($number);

        $result = $words ? $words . ($currency === 'INR' ? ' Rupees' : ' ' . $currency) : '';

        if ($fraction > 0) {
            $fractionWords = self::convertNumberToIndianSystem($fraction);
            $result .= ($result ? ' and ' : '') . $fractionWords . ($currency === 'INR' ? ' Paise' : ' Cents');
        }

        return trim($result) . ' Only';
    }

    private static function convertNumberToIndianSystem(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        if ($number < 20) {
            return self::$dictionary[$number];
        }

        if ($number < 100) {
            $tens = (int) (floor($number / 10) * 10);
            $units = $number % 10;
            return self::$dictionary[$tens] . ($units ? ' ' . self::$dictionary[$units] : '');
        }

        if ($number < 1000) {
            $hundreds = (int) floor($number / 100);
            $remainder = $number % 100;
            return self::$dictionary[$hundreds] . ' Hundred' . ($remainder ? ' ' . self::convertNumberToIndianSystem($remainder) : '');
        }

        if ($number < 100000) {
            $thousands = (int) floor($number / 1000);
            $remainder = $number % 1000;
            return self::convertNumberToIndianSystem($thousands) . ' Thousand' . ($remainder ? ' ' . self::convertNumberToIndianSystem($remainder) : '');
        }

        if ($number < 10000000) {
            $lakhs = (int) floor($number / 100000);
            $remainder = $number % 100000;
            return self::convertNumberToIndianSystem($lakhs) . ' Lakh' . ($remainder ? ' ' . self::convertNumberToIndianSystem($remainder) : '');
        }

        $crores = (int) floor($number / 10000000);
        $remainder = $number % 10000000;
        return self::convertNumberToIndianSystem($crores) . ' Crore' . ($remainder ? ' ' . self::convertNumberToIndianSystem($remainder) : '');
    }
}
