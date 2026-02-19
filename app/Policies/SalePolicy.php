<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Sale Policy
 * 
 * PERMISSION MODEL:
 * - akses_kasir: Self-service permission for all authenticated users to create sales (POS)
 * - lihat_semua_penjualan: View all sales records (management view)
 * - kelola_penjualan: Manage all sales records - void, edit (management)
 */
class SalePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * This is for viewing ALL sales records (management).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('lihat_semua_penjualan');
    }

    /**
     * Determine whether the user can view the model.
     * Users can view their own sales (they were the cashier).
     * Viewing others' sales requires lihat_semua_penjualan.
     */
    public function view(User $user, Sale $sale): bool
    {
        // User can view their own sales (they were the cashier)
        if ($user->id === $sale->cashier_id) {
            return true;
        }

        // Viewing others requires management permission
        return $user->can('lihat_semua_penjualan');
    }

    /**
     * Determine whether the user can create models.
     * This is for POS transactions - all authenticated users with akses_kasir.
     */
    public function create(User $user): bool
    {
        return $user->can('akses_kasir');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        return $user->can('kelola_penjualan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): bool
    {
        // Cannot delete sales with SHU points already awarded
        if ($sale->shu_points_earned > 0) {
            return false;
        }

        return $user->can('kelola_penjualan');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $sale): bool
    {
        return $user->can('kelola_penjualan');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $sale): bool
    {
        return $user->can('kelola_penjualan');
    }

    /**
     * Determine whether the user can void a sale.
     */
    public function void(User $user, Sale $sale): bool
    {
        // Cannot void sales older than 24 hours
        if ($sale->created_at->lt(now()->subDay())) {
            return false;
        }

        return $user->can('kelola_penjualan');
    }

    /**
     * Determine whether the user can export sales.
     */
    public function export(User $user): bool
    {
        return $user->can('ekspor_data') || $user->can('lihat_laporan');
    }

    /**
     * Determine whether the user can view sales reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->can('lihat_laporan');
    }
}
