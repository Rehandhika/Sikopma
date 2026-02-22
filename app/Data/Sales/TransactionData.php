<?php

namespace App\Data\Sales;

use App\Data\BaseData;

class TransactionData extends BaseData
{
    public int $cashier_id;
    public array $cart_items; // Array of CartItemData
    public string $payment_method;
    public int $payment_amount;
    public ?string $student_nim = null;
    public ?string $notes = null;
}
