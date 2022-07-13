<?php

declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

use ArrayIterator;
use InvalidArgumentException;

class Seeds implements \ArrayAccess, \IteratorAggregate
{
    public function __construct(
        /**@var class-string */
        private readonly string $class,
        private readonly array $items = []
    ) {
    }

    /**
     * Get all of the items in the seed.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Execute a callback over each item.
     */
    public function each(callable $callback): self
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Chunk the seeds into chunks of the given size.
     */
    public function chunk(int $size): self
    {
        if ($size <= 0) {
            return new static($this->class);
        }

        $chunks = [];

        foreach (\array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($this->class, $chunk);
        }

        return new static($this->class, $chunks);
    }

    public function first(): mixed
    {
        foreach ($this->items as $item) {
            return $item;
        }

        return null;
    }

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @param int|null $number
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function random(int $number = null): self
    {
        $requested = \is_null($number) ? 1 : $number;
        $count = \count($this->items);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (\is_null($number)) {
            $array = [$this->items[\array_rand($this->items)]];
        } else {
            if ($number === 0) {
                $array = [];
            } else {
                $keys = \array_rand($this->items, $number);

                $array = [];

                foreach ((array)$keys as $key) {
                    $array[] = $this->items[$key];
                }
            }
        }

        return new static($this->class, $array);
    }

    /**
     * Reverse items order.
     */
    public function reverse(): self
    {
        return new static($this->class, \array_reverse($this->items, true));
    }

    /**
     * Shuffle the items in the seed.
     */
    public function shuffle(int $seed = null): self
    {
        if (\is_null($seed)) {
            \shuffle($this->items);
        } else {
            \mt_srand($seed);
            \shuffle($this->items);
            \mt_srand();
        }

        return new static($this->class, $this->items);
    }

    /**
     * Slice the underlying seed array.
     */
    public function slice(int $offset, int $length = null): self
    {
        return new static(
            $this->class, \array_slice($this->items, $offset, $length, true)
        );
    }

    /**
     * Skip the first {$count} items.
     */
    public function skip(int $count): self
    {
        return $this->slice($count);
    }

    /**
     * Take the first or last {$limit} items.
     */
    public function take(int $limit): self
    {
        if ($limit < 0) {
            return $this->slice($limit, \abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the seed.
     */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet(mixed $offset): array
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}
