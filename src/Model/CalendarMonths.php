<?php

namespace App\Model;

class CalendarMonths extends Model
{
    private int $id;
    private int $published;
    private int $calendar;
    private int $monthNumber;
    private string $monthName;
    private int $monthDurationInDays;
    private int $gapMonth;
    private int $gapMonthAfter;
    private int $gapYearDays;
    private int $gapYearInterval;

    /**
     * @param int $calendar
     * @param int $monthNumber
     * @param string $monthName
     * @param int $monthDurationInDays
     * @param int $gapMonth
     * @param int $gapMonthAfter
     * @param int $gapYearDays
     * @param int $gapYearInterval
     */
    public function __construct(int $calendar, int $monthNumber, string $monthName, int $monthDurationInDays, int $gapMonth, int $gapMonthAfter, int $gapYearDays, int $gapYearInterval)
    {
        $this->calendar = $calendar;
        $this->monthNumber = $monthNumber;
        $this->monthName = $monthName;
        $this->monthDurationInDays = $monthDurationInDays;
        $this->gapMonth = $gapMonth;
        $this->gapMonthAfter = $gapMonthAfter;
        $this->gapYearDays = $gapYearDays;
        $this->gapYearInterval = $gapYearInterval;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPublished(): int
    {
        return $this->published;
    }

    /**
     * @param int $published
     */
    public function setPublished(int $published): void
    {
        $this->published = $published;
    }

    /**
     * @return int
     */
    public function getCalendar(): int
    {
        return $this->calendar;
    }

    /**
     * @param int $calendar
     */
    public function setCalendar(int $calendar): void
    {
        $this->calendar = $calendar;
    }

    /**
     * @return int
     */
    public function getMonthNumber(): int
    {
        return $this->monthNumber;
    }

    /**
     * @param int $monthNumber
     */
    public function setMonthNumber(int $monthNumber): void
    {
        $this->monthNumber = $monthNumber;
    }

    /**
     * @return string
     */
    public function getMonthName(): string
    {
        return $this->monthName;
    }

    /**
     * @param string $monthName
     */
    public function setMonthName(string $monthName): void
    {
        $this->monthName = $monthName;
    }

    /**
     * @return int
     */
    public function getMonthDurationInDays(): int
    {
        return $this->monthDurationInDays;
    }

    /**
     * @param int $monthDurationInDays
     */
    public function setMonthDurationInDays(int $monthDurationInDays): void
    {
        $this->monthDurationInDays = $monthDurationInDays;
    }

    /**
     * @return int
     */
    public function getGapMonth(): int
    {
        return $this->gapMonth;
    }

    /**
     * @param int $gapMonth
     */
    public function setGapMonth(int $gapMonth): void
    {
        $this->gapMonth = $gapMonth;
    }

    /**
     * @return int
     */
    public function getGapMonthAfter(): int
    {
        return $this->gapMonthAfter;
    }

    /**
     * @param int $gapMonthAfter
     */
    public function setGapMonthAfter(int $gapMonthAfter): void
    {
        $this->gapMonthAfter = $gapMonthAfter;
    }

    /**
     * @return int
     */
    public function getGapYearDays(): int
    {
        return $this->gapYearDays;
    }

    /**
     * @param int $gapYearDays
     */
    public function setGapYearDays(int $gapYearDays): void
    {
        $this->gapYearDays = $gapYearDays;
    }

    /**
     * @return int
     */
    public function getGapYearInterval(): int
    {
        return $this->gapYearInterval;
    }

    /**
     * @param int $gapYearInterval
     */
    public function setGapYearInterval(int $gapYearInterval): void
    {
        $this->gapYearInterval = $gapYearInterval;
    }

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = $conn->prepare("INSERT INTO `calendar_months` (`calendar`, `month_number`, `month_name`, `month_duration_in_days`, `gap_month`, `gap_month_after`, `gap_year_days`, `gap_year_interval`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $conn->execute_query($stmt, [$this->calendar, $this->monthNumber, $this->monthName, $this->monthDurationInDays, $this->gapMonth, $this->gapMonthAfter, $this->gapYearDays, $this->gapYearInterval]);
        $this->closeConnection($conn);
    }
}