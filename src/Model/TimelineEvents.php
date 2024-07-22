<?php

namespace App\Model;

class TimelineEvents
{
    private int $id;
    private int $published;
    private int $createdBy;
    private int $lastEdit;
    private int $lastEditBy;
    private int $timelineId;
    private string $headline;
    private string $text;
    private int $calendarId;
    private int $day;
    private int $month;
    private int $year;

    /**
     * @param int $createdBy
     * @param int $lastEdit
     * @param int $lastEditBy
     * @param int $timelineId
     * @param string $headline
     * @param string $text
     * @param int $calendarId
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function __construct(int $createdBy, int $lastEdit, int $lastEditBy, int $timelineId, string $headline, string $text, int $calendarId, int $day, int $month, int $year)
    {
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->timelineId = $timelineId;
        $this->headline = $headline;
        $this->text = $text;
        $this->calendarId = $calendarId;
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
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
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    /**
     * @param int $createdBy
     */
    public function setCreatedBy(int $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return int
     */
    public function getLastEdit(): int
    {
        return $this->lastEdit;
    }

    /**
     * @param int $lastEdit
     */
    public function setLastEdit(int $lastEdit): void
    {
        $this->lastEdit = $lastEdit;
    }

    /**
     * @return int
     */
    public function getLastEditBy(): int
    {
        return $this->lastEditBy;
    }

    /**
     * @param int $lastEditBy
     */
    public function setLastEditBy(int $lastEditBy): void
    {
        $this->lastEditBy = $lastEditBy;
    }

    /**
     * @return int
     */
    public function getTimelineId(): int
    {
        return $this->timelineId;
    }

    /**
     * @param int $timelineId
     */
    public function setTimelineId(int $timelineId): void
    {
        $this->timelineId = $timelineId;
    }

    /**
     * @return string
     */
    public function getHeadline(): string
    {
        return $this->headline;
    }

    /**
     * @param string $headline
     */
    public function setHeadline(string $headline): void
    {
        $this->headline = $headline;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getCalendarId(): int
    {
        return $this->calendarId;
    }

    /**
     * @param int $calendarId
     */
    public function setCalendarId(int $calendarId): void
    {
        $this->calendarId = $calendarId;
    }

    /**
     * @return int
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @param int $day
     */
    public function setDay(int $day): void
    {
        $this->day = $day;
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @param int $month
     */
    public function setMonth(int $month): void
    {
        $this->month = $month;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }
}