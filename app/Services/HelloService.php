<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Hello Service
 *
 * A helper service class for generating greeting messages.
 * This service can be injected via Laravel's service container
 * and is designed to be easily testable and extensible.
 *
 * @package App\Services
 * @author LunaOS Team
 * @since 1.0.0
 */
class HelloService
{
    /**
     * Default greeting prefix.
     *
     * @var string
     */
    private const DEFAULT_GREETING = 'Hello';

    /**
     * Default recipient name when none is provided.
     *
     * @var string
     */
    private const DEFAULT_RECIPIENT = 'World';

    /**
     * Generate a greeting message.
     *
     * Creates a personalized greeting for the given recipient.
     * If no recipient is provided, defaults to 'World'.
     *
     * @param string|null $recipient The name of the recipient to greet.
     * @return string The formatted greeting message.
     *
     * @example
     * $service->greet();           // Returns: "Hello, World!"
     * $service->greet('LunaOS');    // Returns: "Hello, LunaOS!"
     */
    public function greet(?string $recipient = null): string
    {
        $name = $recipient ?? self::DEFAULT_RECIPIENT;
        
        return sprintf('%s, %s!', self::DEFAULT_GREETING, $name);
    }

    /**
     * Generate a personalized greeting with a custom message.
     *
     * Allows customization of both the greeting prefix and recipient.
     * Useful for creating varied greeting styles.
     *
     * @param string $greeting The greeting prefix to use.
     * @param string|null $recipient The name of the recipient.
     * @return string The formatted greeting message.
     *
     * @example
     * $service->greetCustom('Welcome', 'User');  // Returns: "Welcome, User!"
     */
    public function greetCustom(string $greeting, ?string $recipient = null): string
    {
        $name = $recipient ?? self::DEFAULT_RECIPIENT;
        
        return sprintf('%s, %s!', $greeting, $name);
    }

    /**
     * Check if the service is available.
     *
     * A utility method for health checks or service availability
     * verification in more complex implementations.
     *
     * @return bool Always returns true for this basic implementation.
     */
    public function isAvailable(): bool
    {
        return true;
    }
}
