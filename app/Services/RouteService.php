<?php

namespace App\Services;

use App\Models\User;

class RouteService
{
    /**
     * Get user's dashboard route based on role
     */
    public function getDashboardRoute(User $user): string
    {
        $role = $user->getPrimaryRole();

        // Dashboard routes based on Wirus Angkatan 66 roles
        return match ($role) {
            'Super Admin',
            'ketua',
            'wakil-ketua',
            'sekretaris',
            'bendahara',
            'koordinator-toko',
            'koordinator-psda',
            'koordinator-humsar',
            'koordinator-produksi',
            'koordinator-desain',
            'anggota' => 'admin.dashboard',
            default => 'admin.dashboard',
        };
    }
}
