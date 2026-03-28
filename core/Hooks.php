<?php
namespace GlassPress\Core;

/**
 * WordPress-inspired hooks/actions/filters system.
 */
class Hooks
{
    private array $actions = [];
    private array $filters = [];

    /**
     * Register an action hook.
     */
    public function addAction(string $tag, callable $callback, int $priority = 10): void
    {
        $this->actions[$tag][$priority][] = $callback;
    }

    /**
     * Execute an action.
     */
    public function doAction(string $tag, mixed ...$args): void
    {
        if (!isset($this->actions[$tag])) {
            return;
        }

        $hooks = $this->actions[$tag];
        ksort($hooks);

        foreach ($hooks as $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * Register a filter hook.
     */
    public function addFilter(string $tag, callable $callback, int $priority = 10): void
    {
        $this->filters[$tag][$priority][] = $callback;
    }

    /**
     * Apply filters and return the result.
     */
    public function applyFilters(string $tag, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$tag])) {
            return $value;
        }

        $hooks = $this->filters[$tag];
        ksort($hooks);

        foreach ($hooks as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = call_user_func_array($callback, array_merge([$value], $args));
            }
        }

        return $value;
    }

    /**
     * Check if an action has been registered.
     */
    public function hasAction(string $tag): bool
    {
        return isset($this->actions[$tag]) && !empty($this->actions[$tag]);
    }

    /**
     * Check if a filter has been registered.
     */
    public function hasFilter(string $tag): bool
    {
        return isset($this->filters[$tag]) && !empty($this->filters[$tag]);
    }

    /**
     * Remove all callbacks for a given action.
     */
    public function removeAction(string $tag): void
    {
        unset($this->actions[$tag]);
    }

    /**
     * Remove all callbacks for a given filter.
     */
    public function removeFilter(string $tag): void
    {
        unset($this->filters[$tag]);
    }
}
