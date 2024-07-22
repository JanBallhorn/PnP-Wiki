<?php

namespace App\Model;

class CalendarYears
{
    private int $id;
    private int $published;
    private int $calendar;
    private string $yearDefinition;
    private string $yearDefinitionAbbreviation;
    private int $daysPerYear;
    private int $gapYear;
    private int $monthsPerYear;
    private int $daysPerMonth;
    private int $daysPerWeek;

    /**
     * @param int $calendar
     * @param string $yearDefinition
     * @param string $yearDefinitionAbbreviation
     * @param int $daysPerYear
     * @param int $gapYear
     * @param int $monthsPerYear
     * @param int $daysPerMonth
     * @param int $daysPerWeek
     * @param int $hoursPerDay
     */
    public function __construct(int $calendar, string $yearDefinition, string $yearDefinitionAbbreviation, int $daysPerYear, int $gapYear, int $monthsPerYear, int $daysPerMonth, int $daysPerWeek, int $hoursPerDay)
    {
        $this->calendar = $calendar;
        $this->yearDefinition = $yearDefinition;
        $this->yearDefinitionAbbreviation = $yearDefinitionAbbreviation;
        $this->daysPerYear = $daysPerYear;
        $this->gapYear = $gapYear;
        $this->monthsPerYear = $monthsPerYear;
        $this->daysPerMonth = $daysPerMonth;
        $this->daysPerWeek = $daysPerWeek;
        $this->hoursPerDay = $hoursPerDay;
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
     * @return string
     */
    public function getYearDefinition(): string
    {
        return $this->yearDefinition;
    }

    /**
     * @param string $yearDefinition
     */
    public function setYearDefinition(string $yearDefinition): void
    {
        $this->yearDefinition = $yearDefinition;
    }

    /**
     * @return string
     */
    public function getYearDefinitionAbbreviation(): string
    {
        return $this->yearDefinitionAbbreviation;
    }

    /**
     * @param string $yearDefinitionAbbreviation
     */
    public function setYearDefinitionAbbreviation(string $yearDefinitionAbbreviation): void
    {
        $this->yearDefinitionAbbreviation = $yearDefinitionAbbreviation;
    }

    /**
     * @return int
     */
    public function getDaysPerYear(): int
    {
        return $this->daysPerYear;
    }

    /**
     * @param int $daysPerYear
     */
    public function setDaysPerYear(int $daysPerYear): void
    {
        $this->daysPerYear = $daysPerYear;
    }

    /**
     * @return int
     */
    public function getGapYear(): int
    {
        return $this->gapYear;
    }

    /**
     * @param int $gapYear
     */
    public function setGapYear(int $gapYear): void
    {
        $this->gapYear = $gapYear;
    }

    /**
     * @return int
     */
    public function getMonthsPerYear(): int
    {
        return $this->monthsPerYear;
    }

    /**
     * @param int $monthsPerYear
     */
    public function setMonthsPerYear(int $monthsPerYear): void
    {
        $this->monthsPerYear = $monthsPerYear;
    }

    /**
     * @return int
     */
    public function getDaysPerMonth(): int
    {
        return $this->daysPerMonth;
    }

    /**
     * @param int $daysPerMonth
     */
    public function setDaysPerMonth(int $daysPerMonth): void
    {
        $this->daysPerMonth = $daysPerMonth;
    }

    /**
     * @return int
     */
    public function getDaysPerWeek(): int
    {
        return $this->daysPerWeek;
    }

    /**
     * @param int $daysPerWeek
     */
    public function setDaysPerWeek(int $daysPerWeek): void
    {
        $this->daysPerWeek = $daysPerWeek;
    }

    /**
     * @return int
     */
    public function getHoursPerDay(): int
    {
        return $this->hoursPerDay;
    }

    /**
     * @param int $hoursPerDay
     */
    public function setHoursPerDay(int $hoursPerDay): void
    {
        $this->hoursPerDay = $hoursPerDay;
    }
    private int $hoursPerDay;
}