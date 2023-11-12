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
    private array $menu = [];
    private int $currentPage = 0;
    private int $pageCount = 0;

    private array $dropDownMenu = [];

    private int $dropDownIndex = 0;

    private bool $dropDownIsOpen = false;

    private Box $box;
    private string $refresh;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->cursor = new Cursor($output);

        $terminal = new Terminal();

        $this->height = $terminal->getHeight();
        $this->width = $terminal->getWidth();

        $this->box = new Box($output);
    }

    public function render(): void
    {
        $this->box->render(0,0, $this->width, $this->height, 'Version ' . TOP_VERSION);

        $this->cursor->moveToPosition(1, 2);
        $this->output->write('┣' . str_repeat('━', $this->width - 2) . '┫');

        $this->cursor->moveToPosition(1, 4);
        $this->output->write('┣' . str_repeat('━', $this->width - 2) . '┫');

        $this->renderHeadline();
        $this->renderDropDownMenu();
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
        $offset = $this->getDropDownMenuWidth();

        $this->cursor->moveToPosition($offset + 4, 3);

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
        $this->cursor->moveToPosition(3, 1);
        $this->output->write('<info>' . $this->headline . '</info>');
    }

    public function setInfo(string $info): void
    {
        $this->info = $info;
        $this->renderInfo();
    }

    public function setRefresh(string $refresh): void
    {
        $this->refresh = $refresh;
        $this->renderRefresh();
    }

    private function renderRefresh(): void
    {
        $info = str_pad($this->refresh, 20, ' ', STR_PAD_LEFT);
        $this->cursor->moveToPosition($this->width - strlen($info) - 2, 3);
        $this->output->write($info);
    }

    private function renderInfo(): void
    {
        $info = str_pad($this->info, 20, ' ', STR_PAD_LEFT);
        $this->cursor->moveToPosition($this->width - strlen($info) - 2, 1);
        $this->output->write($info);
    }

    public function setDropDownMenu(array $menuArray): void
    {
        $this->dropDownMenu = $menuArray;
        $this->dropDownIndex = 0;

        $this->renderDropDownMenu();
    }

    public function setDropDownByInterval(int $interval): void
    {
        foreach ($this->dropDownMenu as $index => $dropDownMenu) {
            if ($dropDownMenu['value'] == $interval) {
                $this->dropDownIndex = $index;
            }
        }
        $this->renderDropDownMenu();
    }

    private function renderDropDownMenu(): void
    {
        $offset = $this->getDropDownMenuWidth();

        $this->cursor->moveToPosition($offset, 2);
        $this->output->write('┳');

        $this->cursor->moveToPosition($offset, 4);
        $this->output->write('┻');

        $this->cursor->moveToPosition(3, 3);

        if ($this->dropDownIsOpen) {
            $this->output->write(str_pad($this->dropDownMenu[$this->dropDownIndex]['caption'], $offset - 8, ' ') . '  ▲  ┃');

            foreach ($this->dropDownMenu as $index => $downMenu) {
                $this->cursor->moveToPosition(3, 4 + ($index + 1) * 2);

                if ($index == $this->dropDownIndex) {
                    $checkedBegin = '<info>';
                    $checkedEnd = '</info>';
                } else {
                    $checkedBegin = '';
                    $checkedEnd = '';
                }

                $this->output->write($checkedBegin . str_pad($this->dropDownMenu[$index]['caption'], $offset - 3, ' ') . $checkedEnd . '┃');
                $this->cursor->moveToPosition(3, 3 + ($index + 1) * 2);
                $this->output->write(str_pad('', $offset - 3, ' ') . '┃');
            }

            $this->cursor->moveToPosition($offset, 4);
            $this->output->write('╋');

            $this->cursor->moveToPosition(0, 5 + (count($this->dropDownMenu) * 2));
            $this->output->write('┃' . str_pad('', $offset - 2, ' ') . '┃');
            $this->cursor->moveToPosition(0, 6 + (count($this->dropDownMenu) * 2));
            $this->output->write('┗' . str_repeat('━', $offset - 2,) . '┛');
        } else {
            $this->output->write(str_pad($this->dropDownMenu[$this->dropDownIndex]['caption'], $offset - 8, ' ') . '  ▼  ┃');
        }
    }

    private function getDropDownMenuWidth(): int
    {
        $offset = 0;
        foreach ($this->dropDownMenu as $downMenu) {
            $offset = max(strlen($downMenu['caption']), $offset);
        }

        return $offset + 8;
    }

    public function openDropDown(): void
    {
        $this->dropDownIsOpen = true;
        $this->renderDropDownMenu();
    }

    public function closeDropDown(): void
    {
        $this->dropDownIsOpen = false;
        $this->renderDropDownMenu();
    }

    public function incDropDownIndex(): void
    {
        $this->dropDownIndex = min($this->dropDownIndex + 1, count($this->dropDownMenu) - 1);
        $this->renderDropDownMenu();
    }

    public function decDropDownIndex(): void
    {
        $this->dropDownIndex = max($this->dropDownIndex - 1, 0);
        $this->renderDropDownMenu();
    }

    public function isDropDownOpen(): bool
    {
        return $this->dropDownIsOpen;
    }

    public function getDropDownIndex(): int
    {
        return $this->dropDownIndex;
    }

    public function setFooter(string $footer): void
    {
        $this->footer = $footer;
        $this->renderFooter();
    }

    private function renderFooter(): void
    {
        $this->cursor->moveToPosition(4, $this->height - 1);
        $this->output->write(' ' . $this->footer . ' ');
    }

    public function setPage(int $current, int $count): void
    {
        $this->currentPage = $current;
        $this->pageCount = $count;

        $this->renderPage();
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function incCurrentPage(): void
    {
        $this->currentPage = min($this->currentPage + 1, $this->pageCount + 1);
    }

    public function decCurrentPage(): void
    {
        $this->currentPage = max($this->currentPage - 1, 0);
    }

    private function renderPage(): void
    {
        if ($this->pageCount != 0) {
            $label = '← ' . ($this->currentPage + 1) . ' / ' . $this->pageCount . ' →';
            $this->cursor->moveToPosition($this->width - strlen($label) + 2, 3);
            $this->output->write($label);
        }
    }

    public function getMaxNumberOfGraphs($graphHeight): int
    {
        return (int)($this->height / $graphHeight);
    }

    public function getWidth(): int
    {
        return $this->width;
    }
}
