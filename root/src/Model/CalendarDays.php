<?php

namespace App\Model;

class CalendarDays
{
    private int $id;
    private int $published;
    private int $calendar;
    private int $day_number;
    private string $day_name;

    /**
     * @param int $id
     * @param int $published
     * @param int $calendar
     * @param int $day_number
     * @param string $day_name
     */
    public function __construct(int $id, int $published, int $calendar, int $day_number, string $day_name)
    {
        $this->id = $id;
        $this->published = $published;
        $this->calendar = $calendar;
        $this->day_number = $day_number;
        $this->day_name = $day_name;
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
    public function getDayNumber(): int
    {
        return $this->day_number;
    }

    /**
     * @param int $day_number
     */
    public function setDayNumber(int $day_number): void
    {
        $this->day_number = $day_number;
    }

    /**
     * @return string
     */
    public function getDayName(): string
    {
        return $this->day_name;
    }

    /**
     * @param string $day_name
     */
    public function setDayName(string $day_name): void
    {
        $this->day_name = $day_name;
    }
}