<?php

namespace Aberdeener\Koss\Queries;

use Closure;
use Aberdeener\Koss\Util\Util;

abstract class Query
{
    protected array $_where = [];

    /**
     * Preform an AND WHERE operation in this query.
     *
     * @param string $column Name of column to use.
     * @param string $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     *
     * @return static This instance of Query.
     */
    public function where(string $column, string $operator, ?string $matches = null): static
    {
        $append = Util::handleWhereOperation($column, $operator, $matches);

        if (!is_null($append)) {
            $this->_where[] = $append;
        }

        return $this;
    }

    /**
     * Preform an OR WHERE operation in this query.
     *
     * @param string $column Name of column to use.
     * @param string $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     *
     * @return static This instance of Query.
     */
    public function orWhere(string $column, string $operator, ?string $matches = null): static
    {
        $append = Util::handleWhereOperation($column, $operator, $matches, 'OR');

        if (!is_null($append)) {
            $this->_where[] = $append;
        }

        return $this;
    }

    /**
     * Add an AND LIKE statement to this query.
     * Rereoutes to `where()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     *
     * @return static This instance of Query.
     */
    public function like(string $column, string $like): static
    {
        return $this->where($column, 'LIKE', $like);
    }

    /**
     * Add an OR LIKE statement to this query.
     * Rereoutes to `orWhere()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     *
     * @return static This instance of Query.
     */
    public function orLike(string $column, string $like): static
    {
        return $this->orWhere($column, 'LIKE', $like);
    }

    /**
     * Run a Koss function only when the specified $expression is true.
     *
     * @param Closure|bool $expression Function or boolean value to eval.
     * @param Closure $callback Function to run when $expression is true.
     * @param Closure|null $fallback Function to run when $expression is false.
     *
     * @return static This instance of Query.
     */
    public function when(Closure | bool $expression, Closure $callback, ?Closure $fallback = null): static
    {
        if (is_callable($expression) ? $expression() : $expression) {
            $callback($this);
        } else {
            if (is_callable($fallback)) {
                $fallback($this);
            }
        }

        return $this;
    }

    /**
     * Execute repsective query and store result.
     */
    abstract public function execute(): array | int;

    /**
     * Assemble clauses into matching MySQL statement.
     */
    abstract public function build(): string;

    /**
     * Reset query strings and arrays.
     */
    abstract public function reset(): void;

    /**
     * Output the built string of all queries so far.
     *
     * @codeCoverageIgnore
     */
    final public function __toString(): string
    {
        return $this->build();
    }
}
