<?php

/**
 * An iteratable class (like doctrine's persistentCollection)
 */
class Collection implements Countable, IteratorAggregate, ArrayAccess
{
	public function count()
	{
		return count($this->array);
	}

	public function __construct(private array $array)
    {
    }

	public function getIterator()
	{
		return new ArrayIterator($this->array);
	}

	public function offsetExists($offset)
	{
		isset($this->array[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->array[$offset] ?? null;
	}

	public function offsetSet($offset, $value)
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

	public function offsetUnset($offset)
	{
		unset($this->container[$offset]);
	}
}
