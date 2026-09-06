<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\MenuVariantGroupPrice;

class MenuPriceResolver
{
    public function priceForMenu(Menu $menu, ?Customer $customer = null): int
    {
        $variant = $menu->relationLoaded('activeVariants')
            ? $menu->activeVariants->first()
            : $menu->activeVariants()->first();

        return $variant
            ? $this->priceForVariant($variant, $customer)
            : (int) $menu->selling_price;
    }

    public function priceForVariant(MenuVariant $variant, ?Customer $customer = null): int
    {
        $groupId = $customer?->customer_group_id;

        if (! $groupId || ! ($customer->group?->is_active ?? false)) {
            return (int) $variant->selling_price;
        }

        return (int) (MenuVariantGroupPrice::query()
            ->where('menu_variant_id', $variant->id)
            ->where('customer_group_id', $groupId)
            ->value('selling_price') ?? $variant->selling_price);
    }
}
