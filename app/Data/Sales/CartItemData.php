<?php

namespace App\Data\Sales;

use App\Data\BaseData;

class CartItemData extends BaseData
{
    public int $product_id;
    public ?int $variant_id;
    public string $name;
    public int $price;
    public int $quantity;
    public int $stock;
    public ?string $image;
}
