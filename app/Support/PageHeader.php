<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * The one header every staff page gets from the layout (not the Dashboard): title and an optional
 * line under it on the left, breadcrumb on the right (Home › Section › This page). Pages set it with
 * partials.page-header; otherwise the title and crumbs come from the address. Same pattern as scharge.
 */
class PageHeader
{
    /** Routes that keep their own top (the dashboards). */
    public const SKIP = ['dashboard'];

    private static ?array $override = null;

    public static function set(array $values): void
    {
        self::$override = array_merge(self::$override ?? [], array_filter($values, fn ($v) => $v !== null && $v !== ''));
    }

    public static function shows(): bool
    {
        return ! request()->routeIs(...self::SKIP);
    }

    /** @return array{title: string, crumbs: array<string, string>, sub: ?string} */
    public static function resolve(): array
    {
        $o = self::$override ?? [];
        self::$override = null;   // one page per request
        $segments = array_values(array_filter(request()->segments(), fn ($s) => $s !== ''));

        return [
            'title' => $o['title'] ?? self::fromSegments($segments),
            'crumbs' => $o['crumbs'] ?? self::crumbs($segments),
            'sub' => $o['sub'] ?? null,
        ];
    }

    private const LABELS = ['hr' => 'HR', 'stays' => 'Bookings', 'room-types' => 'Room types', 'night-audits' => 'Night audit', 'purchase-orders' => 'Purchase orders'];

    public static function label(string $segment): string
    {
        return self::LABELS[strtolower($segment)] ?? Str::ucfirst(str_replace(['-', '_'], ' ', $segment));
    }

    private static function fromSegments(array $segments): string
    {
        $last = end($segments) ?: 'Home';
        if (ctype_digit((string) $last) && count($segments) > 1) {
            return Str::singular(self::label($segments[count($segments) - 2])) . ' #' . $last;
        }

        return self::label((string) $last);
    }

    /** Each earlier part of the address that is itself a page becomes a crumb (ids are skipped). */
    private static function crumbs(array $segments): array
    {
        $crumbs = [];
        $path = '';
        foreach (array_slice($segments, 0, -1) as $segment) {
            $path .= '/' . $segment;
            if (ctype_digit($segment)) {
                continue;
            }
            try {
                $route = Route::getRoutes()->match(\Illuminate\Http\Request::create($path, 'GET'));
                if ($route && ! in_array($route->getName(), self::SKIP, true)) {
                    $crumbs[self::label($segment)] = url($path);
                }
            } catch (\Throwable) {
                // not a page of its own
            }
        }

        return $crumbs;
    }
}
