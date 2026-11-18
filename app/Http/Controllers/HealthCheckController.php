<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthCheck\DeleteHealthCheckRequest;
use App\Http\Requests\HealthCheck\StoreHealthCheckRequest;
use App\Http\Requests\HealthCheck\UpdateHealthCheckRequest;
use App\Models\HealthCheck;
use App\Models\User;
use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    public function __construct(protected HealthCheckService $healthCheckService) {}

    /**
     * AC 2: Daftar catatan pemeriksaan (filter posko, triase, tanggal, pencarian).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $filters = $request->only(['shelter_id', 'triage', 'date', 'search']);

        return response()->json(
            $this->healthCheckService->listHealthChecks($user, $filters, (int) $request->input('per_page', 10))
        );
    }

    /**
     * AC 2: Ringkasan triase harian per posko.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $shelterId = $request->filled('shelter_id') ? (int) $request->input('shelter_id') : null;

        return response()->json([
            'data' => $this->healthCheckService->getDailyTriageSummary($user, $request->input('date'), $shelterId),
        ]);
    }

    /**
     * AC 2: Riwayat pemeriksaan per pengungsi.
     */
    public function history(Request $request, int $refugee): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $data = $this->healthCheckService->getRefugeeHistory($user, $refugee);

        return response()->json([
            'data' => $data,
            'health_checks' => $data->healthChecks,
        ]);
    }

    public function show(Request $request, HealthCheck $healthCheck): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        return response()->json([
            'data' => $this->healthCheckService->getHealthCheckDetail($user, $healthCheck->id),
        ]);
    }

    /**
     * AC 1: Tambah catatan pemeriksaan & triase.
     */
    public function store(StoreHealthCheckRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $check = $this->healthCheckService->createHealthCheck($user, $request->validated());

        $message = $check->isCritical()
            ? 'Catatan pemeriksaan tersimpan. Triase MERAH: koordinator posko telah dinotifikasi.'
            : 'Catatan pemeriksaan berhasil disimpan.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'data' => $check], 201);
        }

        return redirect()
            ->route('admin.health-checks')
            ->with('success', $message);
    }

    /**
     * AC 3: Koreksi catatan pemeriksaan (maks. 24 jam, nakes pembuat).
     */
    public function update(UpdateHealthCheckRequest $request, HealthCheck $healthCheck): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $check = $this->healthCheckService->updateHealthCheck($user, $healthCheck, $request->validated());
        $message = 'Catatan pemeriksaan berhasil dikoreksi.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'data' => $check]);
        }

        return redirect()
            ->route('admin.health-checks')
            ->with('success', $message);
    }

    /**
     * AC 4: Hapus (soft delete) catatan salah input dengan alasan.
     */
    public function destroy(DeleteHealthCheckRequest $request, HealthCheck $healthCheck): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $check = $this->healthCheckService->deleteHealthCheck(
            $user,
            $healthCheck,
            (string) $request->validated('delete_reason')
        );
        $message = 'Catatan pemeriksaan berhasil dihapus.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'data' => $check]);
        }

        return redirect()
            ->route('admin.health-checks')
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
