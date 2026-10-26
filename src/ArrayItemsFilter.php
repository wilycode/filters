<?php

declare(strict_types=1);

namespace WilyCode\Filters;

use WilyCode\Filter\FilterInterface;

class ArrayItemsFilter implements FilterInterface
{
    /** @var list<FilterInterface> */
    private array $filters;

    public function __construct(FilterInterface ...$filters)
    {
        $this->filters = $filters;
    }

    public function getFilteredValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $index => $item) {
            foreach ($this->filters as $filter) {
                $item = $filter->getFilteredValue($item);
            }

            $value[$index] = $item;
        }

        return $value;
    }

    /** @return list<FilterInterface> */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function __clone(): void
    {
        $this->filters = array_map(
            static fn(FilterInterface $filter): FilterInterface => clone $filter,
            $this->filters,
        );
    }
}
