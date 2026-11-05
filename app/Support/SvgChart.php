<?php

namespace App\Support;

/**
 * Zero-dependency SVG chart math, following Tyro's inline-SVG chart pattern.
 * Paths are rendered in Blade with CSS-var colors so charts auto-theme
 * (light/dark) without any JS charting library.
 */
class SvgChart
{
    /**
     * Smooth line path for a series, scaled into a viewBox.
     *
     * @param  array<int, float|int>  $values
     * @return string SVG path data starting with "M"
     */
    public static function linePath(array $values, int $width = 600, int $height = 180, int $padding = 8): string
    {
        $points = self::points($values, $width, $height, $padding);

        if ($points === []) {
            return '';
        }

        // Single point: a dot-sized horizontal segment.
        if (count($points) === 1) {
            [$x, $y] = $points[0];

            return sprintf('M %s %s L %s %s', $x, $y, $x + 0.1, $y);
        }

        $d = sprintf('M %s %s', self::n($points[0][0]), self::n($points[0][1]));

        for ($i = 1; $i < count($points); $i++) {
            [$px, $py] = $points[$i - 1];
            [$x, $y] = $points[$i];
            $midX = ($px + $x) / 2;

            $d .= sprintf(' C %s %s, %s %s, %s %s', self::n($midX), self::n($py), self::n($midX), self::n($y), self::n($x), self::n($y));
        }

        return $d;
    }

    /** Line path closed to the baseline, for gradient/area fills. */
    public static function areaPath(array $values, int $width = 600, int $height = 180, int $padding = 8): string
    {
        $line = self::linePath($values, $width, $height, $padding);

        if ($line === '') {
            return '';
        }

        return sprintf('%s L %s %s L %s %s Z', $line, self::n($width - $padding), self::n($height - $padding), self::n($padding), self::n($height - $padding));
    }

    /**
     * Donut segments as [offset, dasharray] pairs for stroke-dasharray circles
     * (r assumed 40, circumference ≈ 251.33 — the component fixes the radius).
     *
     * @param  array<int, float|int>  $values  non-negative series
     * @return array<int, array{offset: float, dash: float, pct: float}>
     */
    public static function donutSegments(array $values, float $radius = 40): array
    {
        $total = array_sum($values);

        if ($total <= 0) {
            return [];
        }

        $circumference = 2 * M_PI * $radius;
        $segments = [];
        $offset = 0.0;

        foreach ($values as $value) {
            $fraction = $value / $total;
            $segments[] = [
                'offset' => round($offset, 2),
                'dash' => round($fraction * $circumference, 2),
                'pct' => round($fraction * 100, 1),
            ];
            $offset += $fraction * $circumference;
        }

        return $segments;
    }

    /**
     * Bar heights as percentages of the tallest value (0 → 2% so empty bars stay visible).
     *
     * @param  array<int, float|int>  $values
     * @return array<int, float>
     */
    public static function barHeights(array $values): array
    {
        $max = max($values ?: [0]);

        if ($max <= 0) {
            return array_map(fn () => 2.0, $values);
        }

        return array_map(fn ($v) => max(2.0, round($v / $max * 100, 1)), $values);
    }

    /**
     * @param  array<int, float|int>  $values
     * @return array<int, array{0: float, 1: float}>
     */
    private static function points(array $values, int $width, int $height, int $padding): array
    {
        $count = count($values);

        if ($count === 0) {
            return [];
        }

        $max = max($values);
        $min = min($values);

        // Flat series: draw at vertical center.
        if ((float) $max === (float) $min) {
            $y = $height / 2;

            return array_map(fn ($i) => [
                $padding + $i * (($width - 2 * $padding) / max(1, $count - 1)),
                $y,
            ], array_keys($values));
        }

        $span = $max - $min;

        return array_map(function ($value, $i) use ($count, $width, $height, $padding, $min, $span) {
            $x = $padding + $i * (($width - 2 * $padding) / max(1, $count - 1));
            // Invert: higher value = smaller y.
            $y = $padding + (1 - ($value - $min) / $span) * ($height - 2 * $padding);

            return [$x, $y];
        }, array_values($values), array_keys($values));
    }

    private static function n(float $value): string
    {
        return (string) round($value, 2);
    }
}
