<?php
/**
 * Připomněnka - WorkdayCalculator
 *
 * Výpočet pracovních dní s podporou českých státních svátků
 */

declare(strict_types=1);

namespace Services;

class WorkdayCalculator
{
    private array $workdays;

    /**
     * @param array $workdays Pole pracovních dní (1=Po, 7=Ne), výchozí Po-Pá
     */
    public function __construct(array $workdays = [1, 2, 3, 4, 5])
    {
        $this->workdays = $workdays;
    }

    /**
     * Vrátit datum X pracovních dní PŘED daným datem
     */
    public function subtractWorkdays(string $fromDate, int $days): string
    {
        $timestamp = strtotime($fromDate);
        $daysBack = 0;

        while ($daysBack < $days) {
            $timestamp = strtotime('-1 day', $timestamp);

            if ($this->isWorkday($timestamp)) {
                $daysBack++;
            }
        }

        // Pokud výsledné datum spadá na nepraco. den, posun ještě zpět
        while (!$this->isWorkday($timestamp)) {
            $timestamp = strtotime('-1 day', $timestamp);
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * Zkontrolovat, zda je daný timestamp pracovní den
     */
    public function isWorkday(int $timestamp): bool
    {
        $dayOfWeek = (int) date('N', $timestamp);

        if (!in_array($dayOfWeek, $this->workdays)) {
            return false;
        }

        // Zkontrolovat české státní svátky
        if ($this->isHoliday($timestamp)) {
            return false;
        }

        return true;
    }

    /**
     * Zkontrolovat, zda je datum český státní svátek
     */
    public function isHoliday(int $timestamp): bool
    {
        $day = (int) date('j', $timestamp);
        $month = (int) date('n', $timestamp);
        $year = (int) date('Y', $timestamp);

        // Pevné svátky
        $fixedHolidays = [
            [1, 1],   // Nový rok / Den obnovy samostatného českého státu
            [1, 5],   // Svátek práce
            [8, 5],   // Den vítězství
            [5, 7],   // Den slovanských věrozvěstů Cyrila a Metoděje
            [6, 7],   // Den upálení mistra Jana Husa
            [28, 9],  // Den české státnosti
            [28, 10], // Den vzniku samostatného československého státu
            [17, 11], // Den boje za svobodu a demokracii
            [24, 12], // Štědrý den
            [25, 12], // 1. svátek vánoční
            [26, 12], // 2. svátek vánoční
        ];

        foreach ($fixedHolidays as [$hDay, $hMonth]) {
            if ($day === $hDay && $month === $hMonth) {
                return true;
            }
        }

        // Velikonoce — pohyblivý svátek
        $easter = $this->getEasterDate($year);
        $goodFriday = strtotime('-2 days', $easter);
        $easterMonday = strtotime('+1 day', $easter);

        $dateStr = date('Y-m-d', $timestamp);

        if ($dateStr === date('Y-m-d', $goodFriday) || $dateStr === date('Y-m-d', $easterMonday)) {
            return true;
        }

        return false;
    }

    /**
     * Výpočet data Velikonoc (Neděle) — Butcher's algorithm
     */
    private function getEasterDate(int $year): int
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * Získat všechny české svátky pro daný rok
     */
    public function getHolidays(int $year): array
    {
        $holidays = [
            ['day' => 1, 'month' => 1, 'name' => 'Nový rok'],
            ['day' => 1, 'month' => 5, 'name' => 'Svátek práce'],
            ['day' => 8, 'month' => 5, 'name' => 'Den vítězství'],
            ['day' => 5, 'month' => 7, 'name' => 'Den Cyrila a Metoděje'],
            ['day' => 6, 'month' => 7, 'name' => 'Den upálení Jana Husa'],
            ['day' => 28, 'month' => 9, 'name' => 'Den české státnosti'],
            ['day' => 28, 'month' => 10, 'name' => 'Den vzniku Československa'],
            ['day' => 17, 'month' => 11, 'name' => 'Den boje za svobodu a demokracii'],
            ['day' => 24, 'month' => 12, 'name' => 'Štědrý den'],
            ['day' => 25, 'month' => 12, 'name' => '1. svátek vánoční'],
            ['day' => 26, 'month' => 12, 'name' => '2. svátek vánoční'],
        ];

        // Velikonoce
        $easter = $this->getEasterDate($year);
        $goodFriday = strtotime('-2 days', $easter);
        $easterMonday = strtotime('+1 day', $easter);

        $holidays[] = [
            'day' => (int) date('j', $goodFriday),
            'month' => (int) date('n', $goodFriday),
            'name' => 'Velký pátek',
        ];
        $holidays[] = [
            'day' => (int) date('j', $easterMonday),
            'month' => (int) date('n', $easterMonday),
            'name' => 'Velikonoční pondělí',
        ];

        return $holidays;
    }
}
