<?php

namespace Startwind\Top\Application;

use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class MainFrame
{
    private OutputInterface $output;
    private int $height;
    private int $width;

    private Cursor $cursor;

    private string $headline = "";

    private string $info = "";
    private string $footer = "";
    private array $menu;
    private int $currentPage = 0;
    private int $pageCount = 0;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->cursor = new Cursor($output);

        $this->height = (new Terminal())->getHeight();
        $this->width = (new Terminal())->getWidth();
    }

    public function render(): void
    {
        $this->cursor->moveToPosition(0, 0);
        $this->output->writeln('┏' . str_repeat('━', $this->width - 2) . '┓');

        for ($i = 1; $i < $this->height - 1; $i++) {
            $this->cursor->moveToPosition(0, $i);
            $this->output->writeln('┃' . str_repeat(' ', $this->width - 2) . '┃');
        }

        $this->cursor->moveToPosition(1, $this->height);
        $this->output->writeln('┗' . str_repeat('━', $this->width - 38) . ' Proof of concept by Nils Langner ━━┛');

        $this->cursor->moveToPosition(1, 1);
        $this->output->writeln('┣' . str_repeat('━', $this->width - 2) . '┫');

        $this->cursor->moveToPosition(1, 3);
        $this->output->writeln('┣' . str_repeat('━', $this->width - 2) . '┫');

        $this->renderHeadline();
        $this->renderInfo();
        $this->renderFooter();

        $this->renderMenu();
    }

    public function setMenu(array $menu): void
    {
        $this->menu = $menu;
        $this->renderMenu();
    }

    private function renderMenu(): void
    {
        $this->cursor->moveToPosition(3, 2);

        foreach ($this->menu as $menu) {
            $this->output->write($menu['label'] . '     ');
        }
    }

    public function setHeadline(string $headline): void
    {
        $this->headline = $headline;
        $this->renderHeadline();
    }

    private function renderHeadline(): void
    {
        $this->cursor->moveToPosition(3, 0);
        $this->output->write('<info>' . $this->headline . '</info>');
    }

    public function setInfo(string $info): void
    {
        $this->info = $info;
        $this->renderInfo();
    }

    private function renderInfo(): void
    {
        $info = str_pad($this->info, 20, ' ', STR_PAD_LEFT);
        $this->cursor->moveToPosition($this->width - strlen($info) - 2, 0);
        $this->output->write($info);
    }

    public function setFooter(string $footer): void
    {
        $this->footer = $footer;
        $this->renderFooter();
    }

    private function renderFooter(): void
    {
        $this->cursor->moveToPosition(4, $this->height - 2);
        $this->output->write(' ' . $this->footer . ' ');
    }

    public function setPage(int $current, int $count): void
    {
        $this->currentPage = $current;
        $this->pageCount = $count;

        $this->renderPage();
    }

    private function renderPage(): void
    {
        if ($this->pageCount != 0) {
            $label = '← ' . $this->currentPage . ' / ' . $this->pageCount . ' →';
            $this->cursor->moveToPosition($this->width - strlen($label) + 2, 2);
            $this->output->write($label);
        }
    }

    public function getMaxNumberOfGraphs($graphHeight): int
    {
        return (int)($this->height / $graphHeight) - 1;
    }

    public function getWidth(): int
    {
        return $this->width;
    }
}
