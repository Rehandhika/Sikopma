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
            'Ketua',
            'Wakil Ketua',
            'Sekretaris',
            'Bendahara Umum',
            'Bendahara Kegiatan',
            'Bendahara Toko',
            'Koordinator Toko',
            'Koordinator PSDA',
            'Koordinator Humsar',
            'Koordinator Produksi',
            'Koordinator IT',
            'Koordinator Desain',
            'Anggota' => 'admin.dashboard',
            default => 'admin.dashboard',
        };
    }
}
