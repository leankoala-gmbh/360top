<?php

namespace Startwind\Top\Application;

class Graph
{
    const UNIT_PERCENT = 'percent';

    private array $timeSeries = [];

    private string $headline = '';

    private string $unit = '';
    private int $intervalInMinutes;

    public function __construct(array $timeSeries, string $headline, int $intervalInMinutes, string $unit = '')
    {
        $this->timeSeries = $timeSeries;
        $this->headline = $headline;
        $this->unit = $unit;
        $this->intervalInMinutes = $intervalInMinutes;
    }

    public function getTimeSeries(): array
    {
        return $this->timeSeries;
    }

    public function getHeadline(): string
    {
        return $this->headline;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @return int
     */
    public function getIntervalInMinutes(): int
    {
        return $this->intervalInMinutes;
    }
}
