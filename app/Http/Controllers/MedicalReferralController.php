<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalReferral\CancelMedicalReferralRequest;
use App\Http\Requests\MedicalReferral\StoreMedicalReferralRequest;
use App\Http\Requests\MedicalReferral\UpdateMedicalReferralRequest;
use App\Models\MedicalReferral;
use App\Models\User;
use App\Services\MedicalReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class MedicalReferralController extends Controller
{
    public function __construct(protected MedicalReferralService $referralService) {}

    /**
     * AC 2: Daftar rujukan medis per posko dengan filter status dan pencarian.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $filters = $request->only(['shelter_id', 'status', 'search']);

        return response()->json(
            $this->referralService->listReferrals($user, $filters, (int) $request->input('per_page', 10))
        );
    }

    /**
     * Ringkasan status rujukan medis per posko.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $shelterId = $request->filled('shelter_id') ? (int) $request->input('shelter_id') : null;

        return response()->json([
            'data' => $this->referralService->getSummary($user, $shelterId),
        ]);
    }

    /**
     * AC 2: Detail rujukan medis beserta pemeriksaan asal dan riwayat status.
     */
    public function show(Request $request, MedicalReferral $medicalReferral): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $detail = $this->referralService->getReferralDetail($user, $medicalReferral->id);

        return response()->json([
            'data' => $detail,
            'history' => $this->referralService->getStatusHistory($detail),
        ]);
    }

    /**
     * AC 1: Tambah rujukan medis baru dari catatan pemeriksaan.
     */
    public function store(StoreMedicalReferralRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $referral = $this->referralService->createReferral($user, $request->validated());
        $message = 'Rujukan medis berhasil diajukan dan koordinator posko telah dinotifikasi.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'data' => $referral], 201);
        }

        return redirect()
            ->to(Route::has('admin.medical-referrals') ? route('admin.medical-referrals') : route('admin.health-checks'))
            ->with('success', $message);
    }

    /**
     * AC 3: Perbarui status rujukan medis dan catatan perkembangan.
     */
    public function update(UpdateMedicalReferralRequest $request, MedicalReferral $medicalReferral): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $referral = $this->referralService->updateReferralStatus($user, $medicalReferral, $request->validated());
        $message = 'Status rujukan medis berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $referral,
                'history' => $this->referralService->getStatusHistory($referral),
            ]);
        }

        return redirect()
            ->to(Route::has('admin.medical-referrals') ? route('admin.medical-referrals') : route('admin.health-checks'))
            ->with('success', $message);
    }

    /**
     * AC 4: Batalkan rujukan medis yang masih berstatus Diajukan dengan alasan wajib.
     */
    public function destroy(CancelMedicalReferralRequest $request, MedicalReferral $medicalReferral): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $referral = $this->referralService->cancelReferral(
            $user,
            $medicalReferral,
            (string) $request->validated('cancel_reason')
        );
        $message = 'Rujukan medis berhasil dibatalkan.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'data' => $referral]);
        }

        return redirect()
            ->to(Route::has('admin.medical-referrals') ? route('admin.medical-referrals') : route('admin.health-checks'))
            ->with('success', $message);
    }

    protected function authenticatedUser(Request $request): User
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            abort(401, 'Sesi autentikasi telah berakhir.');
        }

        return $user;
    }
}
