<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * TimeZoneHelper provides utility methods for timezone operations.
 *
 * This helper offers simple methods to work with timezones,
 * including conversion and formatting for display purposes.
 */
class TimeZoneHelper
{
    /**
     * Default timezone used when no timezone is configured.
     */
    private const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Default date format for display.
     */
    private const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * Get the local application timezone.
     *
     * Returns the configured application timezone or defaults to UTC.
     *
     * @return string The timezone identifier (e.g., 'UTC', 'America/New_York')
     */
    public function getLocalTimezone(): string
    {
        return config('app.timezone', self::DEFAULT_TIMEZONE);
    }

    /**
     * Convert a datetime string to UTC.
     *
     * Takes a datetime string in the application's local timezone
     * and converts it to UTC format.
     *
     * @param string $datetime The datetime string to convert (format: Y-m-d H:i:s)
     * @return string The datetime string in UTC format
     * @throws InvalidArgumentException If the datetime string is invalid
     */
    public function convertToUTC(string $datetime): string
    {
        if (empty($datetime)) {
            throw new InvalidArgumentException('Datetime string cannot be empty');
        }

        $localTimezone = $this->getLocalTimezone();
        
        try {
            $date = new DateTime($datetime, new DateTimeZone($localTimezone));
            $date->setTimezone(new DateTimeZone('UTC'));
            
            return $date->format(self::DEFAULT_FORMAT);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid datetime string: %s', $datetime),
                0,
                $e
            );
        }
    }

    /**
     * Format a datetime string for display.
     *
     * Converts a UTC datetime to the local timezone and formats
     * it for user-friendly display.
     *
     * @param string $datetime The UTC datetime string to format (format: Y-m-d H:i:s)
     * @param string|null $format Optional custom format (defaults to Y-m-d H:i:s)
     * @return string The formatted datetime string in local timezone
     * @throws InvalidArgumentException If the datetime string is invalid
     */
    public function formatForDisplay(string $datetime, ?string $format = null): string
    {
        if (empty($datetime)) {
            throw new InvalidArgumentException('Datetime string cannot be empty');
        }

        $localTimezone = $this->getLocalTimezone();
        $displayFormat = $format ?? self::DEFAULT_FORMAT;
        
        try {
            $date = new DateTime($datetime, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone($localTimezone));
            
            return $date->format($displayFormat);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid datetime string: %s', $datetime),
                0,
                $e
            );
        }
    }
}
