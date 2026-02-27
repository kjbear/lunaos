<?php

namespace App\Agents\Strategies;

use App\Agents\Strategies\DevelopStrategy;
use App\Agents\Strategies\QAStrategy;
use App\Agents\Strategies\DeployStrategy;

/**
 * Strategy Registry
 * 
 * Maps strategy names to their implementations.
 * Used by GenericWorker to instantiate the correct strategy.
 */
class StrategyRegistry
{
    /**
     * Map of strategy names to class names.
     */
    protected static array $strategies = [
        'develop' => DevelopStrategy::class,
        'qa' => QAStrategy::class,
        'deploy' => DeployStrategy::class,
    ];
    
    /**
     * Custom strategies registered at runtime.
     */
    protected static array $customStrategies = [];
    
    /**
     * Get a strategy instance by name.
     * 
     * @param string $name Strategy name
     * @return WorkerStrategy
     * @throws \InvalidArgumentException If strategy not found
     */
    public static function get(string $name): WorkerStrategy
    {
        // Check custom strategies first
        if (isset(self::$customStrategies[$name])) {
            $class = self::$customStrategies[$name];
            return new $class();
        }
        
        // Check built-in strategies
        if (!isset(self::$strategies[$name])) {
            throw new \InvalidArgumentException("Strategy '{$name}' not found. Available: " . implode(', ', self::keys()));
        }
        
        $class = self::$strategies[$name];
        return new $class();
    }
    
    /**
     * Register a custom strategy.
     * 
     * @param string $name Strategy name
     * @param string $class Strategy class (must implement WorkerStrategy)
     * @return void
     * @throws \InvalidArgumentException If class doesn't implement WorkerStrategy
     */
    public static function register(string $name, string $class): void
    {
        if (!is_subclass_of($class, WorkerStrategy::class)) {
            throw new \InvalidArgumentException("Class '{$class}' must implement " . WorkerStrategy::class);
        }
        
        self::$customStrategies[$name] = $class;
    }
    
    /**
     * Get all available strategy names.
     * 
     * @return array List of strategy names
     */
    public static function keys(): array
    {
        return array_keys(array_merge(self::$strategies, self::$customStrategies));
    }
    
    /**
     * Get all available strategies.
     * 
     * @return array Map of name => class
     */
    public static function all(): array
    {
        return array_merge(self::$strategies, self::$customStrategies);
    }
    
    /**
     * Check if a strategy exists.
     * 
     * @param string $name Strategy name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(self::$strategies[$name]) || isset(self::$customStrategies[$name]);
    }
}
