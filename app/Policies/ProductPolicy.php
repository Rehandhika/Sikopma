<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('lihat_produk');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can('lihat_produk');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('kelola_produk');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('kelola_produk');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Cannot delete product with existing sales
        if ($product->saleItems()->exists()) {
            return false;
        }

        return $user->can('kelola_produk');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->can('kelola_produk');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Cannot force delete product with existing sales
        if ($product->saleItems()->withTrashed()->exists()) {
            return false;
        }

        return $user->can('kelola_produk');
    }

    /**
     * Determine whether the user can manage product variants.
     */
    public function manageVariants(User $user, Product $product): bool
    {
        return $user->can('kelola_produk');
    }

    /**
     * Determine whether the user can adjust product stock.
     */
    public function adjustStock(User $user, Product $product): bool
    {
        return $user->can('kelola_stok');
    }

    /**
     * Determine whether the user can export products.
     */
    public function export(User $user): bool
    {
        return $user->can('ekspor_data') || $user->can('kelola_produk');
    }
}
