<?php

/**
 * An iteratable class (like doctrine's persistentCollection)
 */
class Collection implements Countable, IteratorAggregate, ArrayAccess
{
	public function count(): int
	{
		return count($this->array);
	}

	public function __construct(private array $array)
    {
    }

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->array);
	}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->array[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->array[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if(is_null($offset))
		{
			$this->array[] = $value;
		}
		else
		{
			$this->array[$offset] = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->array[$offset]);
	}
}
