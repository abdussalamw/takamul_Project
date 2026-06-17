<?php
/**
 * HijriDate.php
 * Utility class to convert and format Hijri and Gregorian dates.
 */

class HijriDate {

    /**
     * Parse any date string and return standard Gregorian YYYY-MM-DD
     */
    public static function normalizeToGregorian($dateStr) {
        if (empty(trim($dateStr))) return null;
        
        $dateStr = str_replace('\\', '/', $dateStr);
        $dateStr = str_replace('-', '/', $dateStr);
        $parts = explode('/', $dateStr);
        
        if (count($parts) != 3) return null; // Invalid format
        
        // Ensure parts are integers
        $p1 = (int)$parts[0];
        $p2 = (int)$parts[1];
        $p3 = (int)$parts[2];
        
        // Identify Year
        $year = 0; $month = 0; $day = 0;
        if ($p1 > 1000) { // YYYY/MM/DD
            $year = $p1;
            $month = $p2;
            $day = $p3;
        } elseif ($p3 > 1000) { // DD/MM/YYYY
            $day = $p1;
            $month = $p2;
            $year = $p3;
        } else {
            return null; // Cannot determine year
        }
        
        if ($year >= 1400 && $year <= 1500) {
            // It's a Hijri Date, convert to Gregorian
            return self::hijriToGregorian($year, $month, $day);
        } elseif ($year >= 1900 && $year <= 2100) {
            // It's already Gregorian, just format it
            return sprintf("%04d-%02d-%02d", $year, $month, $day);
        }
        
        return null;
    }

    /**
     * Get Hijri Date from Gregorian YYYY-MM-DD
     */
    public static function getHijri($gregorianDateStr) {
        if (empty($gregorianDateStr)) return null;
        // Assume input is already standardized Gregorian
        return self::gregorianToHijri($gregorianDateStr);
    }
    
    /**
     * Get both representations for display
     * Returns ['hijri' => '...', 'gregorian' => '...']
     */
    public static function getDatesForDisplay($gregorianDateStr) {
        if (empty($gregorianDateStr) || $gregorianDateStr == '0000-00-00') {
            return ['hijri' => '', 'gregorian' => ''];
        }
        
        $hijri = self::getHijri($gregorianDateStr);
        $g_formatted = date('d/m/Y', strtotime($gregorianDateStr));
        
        return [
            'hijri' => $hijri . 'هـ',
            'gregorian' => $g_formatted . 'م'
        ];
    }

    /**
     * Core Conversion: Gregorian to Hijri (Kuwaiti Algorithm)
     */
    private static function gregorianToHijri($date) {
        $y = (int)date('Y', strtotime($date));
        $m = (int)date('m', strtotime($date));
        $d = (int)date('d', strtotime($date));

        if (($y > 1582) || (($y == 1582) && ($m > 10)) || (($y == 1582) && ($m == 10) && ($d > 14))) {
            $jd = (int)((1461 * ($y + 4800 + (int)(($m - 14) / 12))) / 4) +
                  (int)((367 * ($m - 2 - 12 * ((int)(($m - 14) / 12)))) / 12) -
                  (int)((3 * ((int)(($y + 4900 + (int)(($m - 14) / 12)) / 100))) / 4) +
                  $d - 32075;
        } else {
            $jd = 367 * $y - (int)((7 * ($y + 5001 + (int)(($m - 9) / 7))) / 4) +
                  (int)((275 * $m) / 9) + $d + 1729777;
        }

        $l = $jd - 1948440 + 10632;
        $n = (int)(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = ((int)((10985 - $l) / 5316)) * ((int)((50 * $l) / 17719)) +
             ((int)($l / 5670)) * ((int)((43 * $l) / 15238));
        $l = $l - ((int)((30 - $j) / 15)) * ((int)((17719 * $j) / 50)) -
             ((int)($j / 16)) * ((int)((15238 * $j) / 43)) + 29;

        $m = (int)((24 * $l) / 709);
        $d = $l - (int)((709 * $m) / 24);
        $y = 30 * $n + $j - 30;

        return sprintf("%02d/%02d/%04d", $d, $m, $y);
    }

    /**
     * Core Conversion: Hijri to Gregorian
     */
    private static function hijriToGregorian($y, $m, $d) {
        $jd = (int)((11 * $y + 3) / 30) + 354 * $y + 30 * $m - (int)(($m - 1) / 2) + $d + 1948440 - 385;

        if ($jd > 2299160) {
            $l = $jd + 68569;
            $n = (int)((4 * $l) / 146097);
            $l = $l - (int)((146097 * $n + 3) / 4);
            $i = (int)((4000 * ($l + 1)) / 1461001);
            $l = $l - (int)((1461 * $i) / 4) + 31;
            $j = (int)((80 * $l) / 2447);
            $d = $l - (int)((2447 * $j) / 80);
            $l = (int)($j / 11);
            $m = $j + 2 - 12 * $l;
            $y = 100 * ($n - 49) + $i + $l;
        } else {
            $j = $jd + 1402;
            $k = (int)(($j - 1) / 1461);
            $l = $j - 1461 * $k;
            $n = (int)(($l - 1) / 365) - (int)($l / 1461);
            $i = $l - 365 * $n + 30;
            $j = (int)((80 * $i) / 2447);
            $d = $i - (int)((2447 * $j) / 80);
            $i = (int)($j / 11);
            $m = $j + 2 - 12 * $i;
            $y = 4 * $k + $n + $i - 4716;
        }

        return sprintf("%04d-%02d-%02d", $y, $m, $d);
    }
}
