<?php

namespace GW\Value;

/**
 * @template TValue
 */
interface Stack
{
    /**
     * @param TValue $value
     * @return Stack<TValue>
     */
    public function unshift($value): Stack;

    /**
     * @return Stack<TValue>
     */
    public function shift(): Stack;

    /**
     * @param TValue $value
     * @return Stack<TValue>
     */
    public function push($value): Stack;

    /**
     * @return Stack<TValue>
     */
    public function pop(): Stack;
}
