<?php

namespace Startwind\Top\Application;

use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;

class Box
{
    const UPPER_LEFT = '┏';
    const UPPER_RIGHT = '┓';
    const LOWER_LEFT = '┗';
    const LOWER_RIGHT = '┛';

    const HORIZONTAL = '━';
    const VERTICAL = '┃';

    private OutputInterface $output;

    private Cursor $cursor;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->cursor = new Cursor($output);
    }

    function render(int $x, int $y, int $width, int $height, string $boxLabel = ""): void
    {
        $this->cursor->moveToPosition($x, $y);

        $this->output->write(self::UPPER_LEFT . str_repeat(self::HORIZONTAL, $width - 2) . self::UPPER_RIGHT);

        for ($i = 1; $i < $height - 1; $i++) {
            $this->cursor->moveToPosition($x, $y + $i);
            $this->output->write(self::VERTICAL . str_repeat(' ', $width - 2) . self::VERTICAL);
        }

        $this->cursor->moveToPosition($x, $y + $height);

        if ($boxLabel) {
            $label = '  ' . $boxLabel . '  ';
            $this->output->write(self::LOWER_LEFT . str_repeat(self::HORIZONTAL, $width - 4 - strlen($label)) . $label . '━━' . self::LOWER_RIGHT);
        } else {
            $this->output->write(self::LOWER_LEFT . str_repeat(self::HORIZONTAL, $width - 2) . self::LOWER_RIGHT);
        }

    }
}
