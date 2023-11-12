<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Page
{
    const METRIC_HEIGHT = 10;

    const UNIT_PERCENT = 'percent';

    protected function getData(Server $server, $metric = Server::METRIC_MEMORY, int $durationInMinutes = 30): array
    {
        return $server->getMetric($metric, $durationInMinutes);
    }

    private function getLabelType($duration): string
    {
        if ($duration > 60 * 24 * 2) {
            $labelType = 'd';
        } else if ($duration > 60 * 23) {
            $labelType = 'h';
        } else {
            $labelType = 'm';
        }

        return $labelType;
    }

    protected function renderGraph(OutputInterface $output, string $headline, int $positionX, int $positionY, array $data, string $unit = "", int $width = 30, $durationInSeconds = 30): void
    {
        $height = self::METRIC_HEIGHT;

        $labelType = $this->getLabelType($durationInSeconds);

        $cursor = new Cursor($output);

        $cursor->moveToPosition($positionX, $positionY - $height);

        $output->write('<comment>' . $headline . '</comment>');

        $preparedDataArray = $this->prepareData($data, $width, $unit);

        $labelMaxLength = 0;

        $steps = $preparedDataArray['steps'];

        foreach ($steps as $i => $label) {
            $cursor->moveToPosition($positionX, $positionY - $i + 2);
            $output->write($label);
            $labelMaxLength = max($labelMaxLength, strlen($label));
        }

        $x = 0;

        foreach ($preparedDataArray['data'] as $timestamp => $value) {

            for ($i = 0; $i < $height; $i++) {
                $cursor->moveToPosition($x + $positionX + $labelMaxLength + 3, $positionY - $height + $i + 2);
                if ($value >= $height - $i) {
                    $output->write('██');
                }
            }

            switch ($labelType) {
                case 'm':
                    $hour = date('i', $timestamp);
                    break;
                case 'h':
                    $hour = date('H', $timestamp);
                    break;
                case 'd':
                    $hour = date('d', $timestamp);
                    break;
                default:
                    $hour = '??';
            }

            $cursor->moveToPosition($x + $positionX + $labelMaxLength + 3, $positionY + 3);
            $output->write($hour);

            $x += 3;
        }
    }

    private function prepareData(array $data, $numberOfBars = 30, $unit = ""): array
    {
        $height = self::METRIC_HEIGHT;

        $maxValue = 0;

        $preparedData = [];

        $elementCount = count($data);

        $groupSize = max(1, ceil($elementCount / $numberOfBars));

        if ($unit === self::UNIT_PERCENT) {
            $step = (int)(100 / $height);
            $unitString = ' %';
            $padLength = 3;
        } else {
            $unitString = '';
            $padLength = 0;
            foreach ($data as $value) {
                $maxValue = max($value, $maxValue);
            }
            $step = round($maxValue / 10, 2);
        }

        if ($step == 0) $step = 1;

        $steps = [];

        for ($i = 1; $i <= $height; $i++) {
            if ($padLength > 0) {
                $number = str_pad((string)($step * $i), $padLength, ' ', STR_PAD_LEFT);
            } else {
                $number = $step * $i;
                if ($number > 10) {
                    $number = (int)$number;
                }
            }


            $steps[$i] = $number . $unitString;
        }

        $elementValue = 0;
        $count = 0;

        foreach ($data as $timestamp => $value) {
            $elementValue += $value;
            if ($count % $groupSize === 0) {
                $preparedData[$timestamp] = (int)(($elementValue / $groupSize) / $step);
                $elementValue = 0;
            }
            $count++;
        }

        return [
            'data' => $preparedData,
            'step' => $step,
            'steps' => $steps,
        ];
    }

    protected function renderTable(OutputInterface $output, array $header, array $array): void
    {
        $cursor = new Cursor($output);

        $cursor->moveToPosition(3, 5);

        foreach ($header as $value) {
            $output->write($value . '      ');
        }

        foreach ($array as $index => $values) {
            $cursor->moveToPosition(3, 7 + $index);
            if ($values['status'] === 'open') {
                $start = '<error>';
                $end = '</error>';
            } else {
                $start = '';
                $end = '';
            }

            $row = $start;

            foreach ($values['data'] as $value) {
                $row .= $value . '    ';
            }

            $row .= $end;

            $output->write($row);
        }
    }

    protected function getPageOptions(MainFrame $mainFrame, array $timeSeries): array
    {
        $max = $mainFrame->getMaxNumberOfGraphs(self::METRIC_HEIGHT + 7);

        $pageCount = (int)((count($timeSeries) / $max) + 1);

        $currentPage = min($pageCount - 1, $mainFrame->getCurrentPage());

        $start = $max * $currentPage;
        $end = $max * ($currentPage + 1);

        if (count($timeSeries) > $max) {
            $mainFrame->setPage($currentPage, $pageCount);
        }

        return [
            'start' => $start,
            'end' => $end
        ];
    }
}
