<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola pengiriman kredensial user
 */
class UserCredentialController extends Controller
{
    protected CredentialService $credentialService;

    public function __construct(CredentialService $credentialService)
    {
        $this->credentialService = $credentialService;
    }

    /**
     * Kirim kredensial ke single user
     */
    public function send(Request $request, User $user)
    {
        $result = $this->credentialService->sendInitialCredentials($user);

        if ($result['success']) {
            return back()->with('success', "Email kredensial berhasil dikirim ke {$user->email}");
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Resend kredensial (reset password + kirim ulang)
     */
    public function resend(Request $request, User $user)
    {
        $result = $this->credentialService->resendCredentials($user);

        if ($result['success']) {
            return back()->with('success', "Password direset dan email dikirim ke {$user->email}");
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Bulk send kredensial
     */
    public function bulkSend(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $results = $this->credentialService->bulkSendCredentials(
            $request->user_ids,
            5 // 5 detik delay antar email
        );

        return back()->with('success',
            "Berhasil menjadwalkan {$results['success']} email. Gagal: {$results['failed']}"
        );
    }
}
