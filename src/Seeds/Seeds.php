<?php
declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

use ArrayIterator;
use InvalidArgumentException;

class Seeds implements \ArrayAccess, \IteratorAggregate
{
    private string $class;
    private array $items;

    public function __construct(string $class, array $items = [])
    {
        $this->class = $class;
        $this->items = $items;
    }

    /**
     * Get all of the items in the seed.
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Execute a callback over each item.
     *
     * @param callable $callback
     * @return $this
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
     *
     * @param int $size
     * @return static
     */
    public function chunk(int $size): self
    {
        if ($size <= 0) {
            return new static($this->class);
        }

        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($this->class, $chunk);
        }

        return new static($this->class, $chunks);
    }

    public function first()
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
        $requested = is_null($number) ? 1 : $number;
        $count = count($this->items);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($number)) {
            $array = [$this->items[array_rand($this->items)]];
        } else if ($number === 0) {
            $array = [];
        } else {
            $keys = array_rand($this->items, $number);

            $array = [];

            foreach ((array)$keys as $key) {
                $results[] = $this->items[$key];
            }
        }

        return new static($this->class, $array);
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse(): self
    {
        return new static($this->class, array_reverse($this->items, true));
    }

    /**
     * Shuffle the items in the seed.
     *
     * @param int|null $seed
     * @return static
     */
    public function shuffle(int $seed = null): self
    {
        if (is_null($seed)) {
            shuffle($this->items);
        } else {
            mt_srand($seed);
            shuffle($this->items);
            mt_srand();
        }

        return new static($this->class, $this->items);
    }

    /**
     * Slice the underlying seed array.
     *
     * @param int $offset
     * @param int|null $length
     * @return static
     */
    public function slice(int $offset, int $length = null): self
    {
        return new static(
            $this->class, array_slice($this->items, $offset, $length, true)
        );
    }

    /**
     * Skip the first {$count} items.
     *
     * @param int $count
     * @return static
     */
    public function skip(int $count): self
    {
        return $this->slice($count);
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param int $limit
     * @return static
     */
    public function take(int $limit): self
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the seed.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param int $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param int $key
     * @return array
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
