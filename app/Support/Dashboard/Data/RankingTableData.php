<?php

namespace App\Support\Dashboard\Data;

readonly class RankingTableData
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<int, RankingRowData>  $rows
     */
    public function __construct(
        public string $id,
        public string $title,
        public array $columns,
        public array $rows,
    ) {}

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     columns: array<int, string>,
     *     rows: array<int, array{position: int, label: string, value: string|int|float, context: ?string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'columns' => $this->columns,
            'rows' => array_map(
                static fn (RankingRowData $row): array => $row->toArray(),
                $this->rows,
            ),
        ];
    }
}
