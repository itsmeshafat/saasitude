<?php

namespace Tests\Feature;

use App\Support\SvgChart;
use PHPUnit\Framework\TestCase;

class SvgChartTest extends TestCase
{
    public function test_line_path_starts_with_move_and_has_curve_segments(): void
    {
        $d = SvgChart::linePath([10, 40, 25, 60]);

        $this->assertStringStartsWith('M ', $d);
        $this->assertSame(3, substr_count($d, ' C '));
    }

    public function test_area_path_closes_to_baseline(): void
    {
        $d = SvgChart::areaPath([10, 40, 25], 600, 180, 8);

        $this->assertStringEndsWith('L 592 172 L 8 172 Z', $d);
    }

    public function test_empty_series_returns_empty_string(): void
    {
        $this->assertSame('', SvgChart::linePath([]));
        $this->assertSame('', SvgChart::areaPath([]));
    }

    public function test_flat_series_draws_at_vertical_center(): void
    {
        $d = SvgChart::linePath([5, 5, 5], 600, 180, 8);

        $this->assertStringContainsString('90', $d); // height/2 = 90
    }

    public function test_higher_values_map_to_smaller_y(): void
    {
        $d = SvgChart::linePath([0, 100], 600, 180, 0);

        // First point y=180 (bottom), second y=0 (top).
        $this->assertStringContainsString('M 0 180', $d);
        $this->assertStringContainsString('600 0', $d);
    }

    public function test_donut_segments_cover_full_circle(): void
    {
        $segments = SvgChart::donutSegments([75, 25]);

        $this->assertCount(2, $segments);
        $this->assertSame(100.0, $segments[0]['pct'] + $segments[1]['pct']);
        $this->assertSame(0.0, $segments[0]['offset']);
        $this->assertEqualsWithDelta(188.5, $segments[1]['offset'], 0.5);
    }

    public function test_donut_with_zero_total_returns_empty(): void
    {
        $this->assertSame([], SvgChart::donutSegments([0, 0]));
    }

    public function test_bar_heights_relative_to_max_with_floor(): void
    {
        $heights = SvgChart::barHeights([10, 5, 0]);

        $this->assertSame(100.0, $heights[0]);
        $this->assertSame(50.0, $heights[1]);
        $this->assertSame(2.0, $heights[2]); // visible floor
    }
}
