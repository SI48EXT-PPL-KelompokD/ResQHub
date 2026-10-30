<?php

namespace App\Http\Controllers;

use App\Http\Requests\Refugee\DeleteRefugeeRequest;
use App\Http\Requests\Refugee\StoreRefugeeRequest;
use App\Http\Requests\Refugee\TransferRefugeeRequest;
use App\Http\Requests\Refugee\UpdateRefugeeRequest;
use App\Models\Refugee;
use App\Models\User;
use App\Services\RefugeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefugeeController extends Controller
{
    public function __construct(
        protected RefugeeService $refugeeService
    ) {
    }

    /**
     * Ambil instance User model yang sedang terotentikasi.
     */
    protected function authenticatedUser(Request $request): User
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            abort(401, 'Sesi autentikasi telah berakhir.');
        }

        return $user;
    }

    /**
     * AC 2: Lihat daftar pengungsi per posko dengan pencarian dan filter status (JSON / API support).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'shelter_id' => $request->query('shelter_id'),
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        $perPage = (int) $request->query('per_page', 10);
        $user = $this->authenticatedUser($request);
        $refugees = $this->refugeeService->listRefugees($user, $filters, $perPage);
        $statistics = $this->refugeeService->getSummaryStatistics(
            $user,
            !empty($filters['shelter_id']) ? (int) $filters['shelter_id'] : null
        );

        return response()->json([
            'data' => $refugees->items(),
            'meta' => [
                'current_page' => $refugees->currentPage(),
                'last_page' => $refugees->lastPage(),
                'per_page' => $refugees->perPage(),
                'total' => $refugees->total(),
            ],
            'statistics' => $statistics,
        ]);
    }

    /**
     * AC 2: Lihat detail pengungsi beserta NIK tersamar dan riwayat masuk, pindah, dan pulang.
     */
    public function show(Request $request, Refugee $refugee): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $detail = $this->refugeeService->getRefugeeDetail($user, $refugee->id);

        return response()->json([
            'data' => $detail,
            'masked_nik' => $detail->masked_nik,
            'can_be_deleted' => $detail->canBeDeleted(),
            'deletion_block_reasons' => $detail->deletionBlockReasons(),
            'movements' => $detail->movements,
        ]);
    }

    /**
     * AC 1: Tambah (check-in) pengungsi baru per kepala keluarga.
     */
    public function store(StoreRefugeeRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $result = $this->refugeeService->checkIn($user, $request->validated());

        $message = 'Data pengungsi berhasil didaftarkan (Check-In).';
        $warningMessages = array_column($result['warnings'], 'message');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $result['refugee'],
                'warnings' => $result['warnings'],
            ], 201);
        }

        return redirect()
            ->route('admin.refugees')
            ->with('success', $message)
            ->with('warnings', $warningMessages);
    }

    /**
     * AC 3 (Bagian 1): Perbarui data keluarga pengungsi.
     */
    public function update(UpdateRefugeeRequest $request, Refugee $refugee): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $result = $this->refugeeService->updateFamilyData($user, $refugee, $request->validated());

        $message = 'Data keluarga pengungsi berhasil diperbarui.';
        $warningMessages = array_column($result['warnings'], 'message');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $result['refugee'],
                'warnings' => $result['warnings'],
            ]);
        }

        return redirect()
            ->route('admin.refugees')
            ->with('success', $message)
            ->with('warnings', $warningMessages);
    }

    /**
     * AC 3 (Bagian 2): Aksi Pulang (Check-out) pengungsi dari posko.
     */
    public function checkout(Request $request, Refugee $refugee): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $updated = $this->refugeeService->checkOut(
            $user,
            $refugee,
            $request->input('checked_out_at')
        );

        $message = "Kepulangan keluarga {$updated->head_name} berhasil dicatat dan okupansi posko telah diperbarui.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $updated,
            ]);
        }

        return redirect()
            ->route('admin.refugees')
            ->with('success', $message);
    }

    /**
     * AC 3 (Bagian 3): Aksi Pindah Posko (Transfer).
     */
    public function transfer(TransferRefugeeRequest $request, Refugee $refugee): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $updated = $this->refugeeService->transferShelter(
            $user,
            $refugee,
            (int) $request->validated('to_shelter_id')
        );

        $message = "Perpindahan keluarga {$updated->head_name} ke {$updated->shelter->name} berhasil dicatat.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $updated,
            ]);
        }

        return redirect()
            ->route('admin.refugees')
            ->with('success', $message);
    }

    /**
     * AC 4: Hapus (Soft Delete) data pengungsi yang salah input dengan alasan.
     */
    public function destroy(DeleteRefugeeRequest $request, Refugee $refugee): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $deleted = $this->refugeeService->deleteRefugee(
            $user,
            $refugee,
            (string) $request->validated('delete_reason')
        );

        $message = "Data pengungsi {$deleted->head_name} berhasil dihapus dan okupansi posko telah dikoreksi.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $deleted,
            ]);
        }

        return redirect()
            ->route('admin.refugees')
            ->with('success', $message);
    }
}

