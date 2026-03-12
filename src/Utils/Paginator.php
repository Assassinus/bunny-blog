<?php

declare(strict_types=1);

namespace App\Utils;

final class Paginator
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $totalItems,
    ) {}

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getTotalPages(): int
    {
        if ($this->totalItems <= 0) {
            return 1;
        }
        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    public function getNextPage(): int
    {
        return min($this->currentPage + 1, $this->getTotalPages());
    }

    public function getPrevPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    /** @return list<int|null> */
    public function getPages(int $neighbours = 1): array
    {
        $total = $this->getTotalPages();
        if ($total <= 1) {
            return [1];
        }

        $pages = [];
        for ($i = 1; $i <= $total; $i++) {
            if ($i === 1 || $i === $total || abs($i - $this->currentPage) <= $neighbours) {
                $pages[] = $i;
            } elseif ($pages !== [] && $pages[array_key_last($pages)] !== null) {
                $pages[] = null;
            }
        }

        return $pages;
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param int $totalItems
     * @return self
     */
    public static function create(int $page, int $perPage, int $totalItems): self
    {
        $totalPages = $totalItems > 0 ? (int) ceil($totalItems / $perPage) : 1;
        $page = max(1, min($page, $totalPages));
        return new self($page, $perPage, $totalItems);
    }
}
