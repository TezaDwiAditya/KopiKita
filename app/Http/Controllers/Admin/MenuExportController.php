<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Response;

class MenuExportController extends Controller
{
    public function __invoke(): Response
    {
        $rows = Menu::query()
            ->with(['category', 'variants'])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Menu $menu) {
                if ($menu->variants->isEmpty()) {
                    return [[
                        'category' => $menu->category?->name ?? '-',
                        'menu' => $menu->name,
                        'variant' => 'Regular',
                        'selling_price' => (int) $menu->selling_price,
                        'cost_price' => (int) $menu->cost_price,
                        'profit' => (int) $menu->selling_price - (int) $menu->cost_price,
                        'menu_status' => $menu->is_active ? 'Aktif' : 'Nonaktif',
                        'variant_status' => $menu->is_active ? 'Aktif' : 'Nonaktif',
                    ]];
                }

                return $menu->variants
                    ->sortBy('sort_order')
                    ->map(fn ($variant): array => [
                        'category' => $menu->category?->name ?? '-',
                        'menu' => $menu->name,
                        'variant' => $variant->name,
                        'selling_price' => (int) $variant->selling_price,
                        'cost_price' => (int) $variant->cost_price,
                        'profit' => (int) $variant->selling_price - (int) $variant->cost_price,
                        'menu_status' => $menu->is_active ? 'Aktif' : 'Nonaktif',
                        'variant_status' => $variant->is_active ? 'Aktif' : 'Nonaktif',
                    ]);
            })
            ->values();

        return response()
            ->view('menus.exports.list', [
                'rows' => $rows,
                'generatedAt' => now(),
            ])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="list-menu-varian-'.now()->format('Ymd-His').'.xls"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
