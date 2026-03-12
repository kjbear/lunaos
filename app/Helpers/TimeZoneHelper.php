<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * TimeZoneHelper provides centralized timezone conversion utilities.
 *
 * This helper handles all timezone-related operations for LunaOS,
 * ensuring consistent datetime handling across the application.
 *
 * @package App\Helpers
 * @author LunaOS Team
 * @since 1.0.0
 */
class TimeZoneHelper
{
    /**
     * Default timezone used when local timezone cannot be determined.
     */
    private const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Get the local timezone for the current application context.
     *
     * Returns the configured application timezone, falling back to UTC
     * if not configured. This method caches the result for performance.
     *
     * @return string The timezone identifier (e.g., 'America/New_York', 'UTC')
     */
    public function getLocalTimezone(): string
    {
        static $cachedTimezone = null;

        if ($cachedTimezone !== null) {
            return $cachedTimezone;
        }

        $cachedTimezone = config('app.timezone', self::DEFAULT_TIMEZONE);

        // Validate the timezone is valid
        if (!in_array($cachedTimezone, timezone_identifiers_list(), true)) {
            $cachedTimezone = self::DEFAULT_TIMEZONE;
        }

        return $cachedTimezone;
    }

    /**
     * Convert a datetime to UTC timezone.
     *
     * Accepts various datetime formats and converts them to UTC.
     * Handles strings, Carbon instances, and DateTimeInterface objects.
     *
     * @param DateTimeInterface|string $datetime The datetime to convert
     * @param string|null $sourceTimezone Optional source timezone (defaults to local)
     * @return Carbon The datetime in UTC timezone
     * @throws InvalidArgumentException If datetime format is invalid
     */
    public function convertToUTC(DateTimeInterface|string $datetime, ?string $sourceTimezone = null): Carbon
    {
        $sourceTz = $sourceTimezone ?? $this->getLocalTimezone();

        try {
            if ($datetime instanceof Carbon) {
                return $datetime->copy()->utc();
            }

            if ($datetime instanceof DateTimeInterface) {
                return Carbon::instance($datetime)->utc();
            }

            // Parse string datetime
            $parsed = Carbon::parse($datetime, $sourceTz);
            return $parsed->utc();
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Invalid datetime format provided: {$datetime}. Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Format a datetime for display in the local timezone.
     *
     * Converts the datetime to local timezone and formats it using
     * the application's configured display format.
     *
     * @param DateTimeInterface|string $datetime The datetime to format
     * @param string|null $format Custom format string (optional)
     * @return string The formatted datetime string
     */
    public function formatForDisplay(DateTimeInterface|string $datetime, ?string $format = null): string
    {
        $localTimezone = $this->getLocalTimezone();

        try {
            $carbon = $datetime instanceof DateTimeInterface
                ? Carbon::instance($datetime)
                : Carbon::parse($datetime);

            // Convert to local timezone
            $local = $carbon->setTimezone($localTimezone);

            // Use provided format or default display format
            $displayFormat = $format ?? config('app.datetime_format', 'Y-m-d H:i:s T');

            return $local->format($displayFormat);
        } catch (\Exception $e) {
            // Return a safe fallback if parsing fails
            return (string) $datetime;
        }
    }
}
