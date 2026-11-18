<?php

namespace App\Livewire\Volunteer;

use App\Models\Skill;
use App\Models\TaskAssignment;
use App\Models\VolunteerProfile;
use App\Models\VolunteerTask;
use App\Notifications\TaskOfferReceived;
use App\Notifications\TaskUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Tugas Relawan')]
class TaskManager extends Component
{
    public string $statusFilter = 'semua';

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $detailId = null;

    // Form fields
    public ?int $sosRequestId = null;

    public string $title = '';

    public string $description = '';

    public ?int $skillId = null;

    public int $quota = 1;

    public string $startAt = '';

    public string $endAt = '';

    public ?int $shelterId = null;

    public string $lat = '';

    public string $lng = '';

    public array $selectedProfiles = [];

    public ?string $notification = null;

    public string $notificationType = 'success';

    public function mount(): void
    {
        if (! auth()->user()?->canAccessModule('modul_g')) {
            abort(403, 'Akses Ditolak: halaman tugas hanya untuk koordinator, admin, dan relawan.');
        }

        $this->resetForm();

        if ($this->isCoordinator()) {
            $this->showForm = true;
        }

        $sosId = (int) request()->query('sos');

        if ($sosId > 0 && $this->isCoordinator()) {
            $this->showForm = true;
            $this->sosRequestId = $sosId;
            $this->prefillFromSos();
        }
    }

    public function isCoordinator(): bool
    {
        return auth()->user()?->isKoordinator() || auth()->user()?->isAdmin();
    }

    public function setFilter(string $status): void
    {
        $this->statusFilter = in_array($status, ['semua', 'terbuka', 'berjalan', 'selesai'], true)
            ? $status
            : 'semua';
        $this->detailId = null;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->detailId = null;
        $this->editingId = null;
    }

    public function openEdit(int $taskId): void
    {
        $task = VolunteerTask::findOrFail($taskId);

        if (! $this->isCoordinator()) {
            return;
        }

        if (! in_array($task->status, ['terbuka', 'berjalan'], true)) {
            $this->notify('Tugas yang sudah selesai tidak dapat diubah.', 'error');

            return;
        }

        $this->editingId = $task->id;
        $this->showForm = true;
        $this->detailId = null;
        $this->sosRequestId = $task->sos_request_id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->skillId = $task->skill_id;
        $this->quota = $task->quota;
        $this->startAt = $task->start_at->format('Y-m-d\TH:i');
        $this->endAt = $task->end_at->format('Y-m-d\TH:i');
        $this->shelterId = $task->shelter_id;
        $this->lat = (string) $task->lat;
        $this->lng = (string) $task->lng;
        $this->selectedProfiles = [];
    }

    public function openDetail(int $taskId): void
    {
        if (! $this->isCoordinator() && ! $this->myOfferStatus($taskId)) {
            $this->notify('Tugas ini bukan bagian dari penugasan Anda.', 'error');

            return;
        }

        $this->detailId = $taskId;
        $this->showForm = false;
        $this->editingId = null;
    }

    public function closePanel(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->detailId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->sosRequestId = null;
        $this->title = '';
        $this->description = '';
        $this->skillId = null;
        $this->quota = 1;
        $this->startAt = Carbon::now()->addHour()->format('Y-m-d\TH:i');
        $this->endAt = Carbon::now()->addHours(3)->format('Y-m-d\TH:i');
        $this->shelterId = null;
        $this->lat = '';
        $this->lng = '';
        $this->selectedProfiles = [];
        $this->resetValidation();
    }

    public function updatedSosRequestId(): void
    {
        $this->prefillFromSos();
    }

    public function prefillFromSos(): void
    {
        if (! $this->sosRequestId) {
            return;
        }

        $sos = DB::table('sos_requests')
            ->join('users', 'sos_requests.requester_id', '=', 'users.id')
            ->where('sos_requests.id', $this->sosRequestId)
            ->select('sos_requests.*', 'users.name as requester_name')
            ->first();

        if (! $sos) {
            return;
        }

        $skillName = match ($sos->help_type) {
            'evakuasi' => 'Evakuasi',
            'medis' => 'Medis',
            'pangan' => 'Dapur Umum',
            default => null,
        };

        $this->skillId = $skillName
            ? Skill::where('name', 'like', '%'.$skillName.'%')->value('id')
            : $this->skillId;

        $this->title = ucfirst($sos->help_type).' '.$sos->requester_name.' ('.$sos->people_count.' orang)';
        $this->description = (string) $sos->note;
        $this->quota = max(1, (int) ceil($sos->people_count / 2));
        $this->lat = (string) $sos->lat;
        $this->lng = (string) $sos->lng;
        $this->shelterId = null;
    }

    public function updatedShelterId(): void
    {
        if (! $this->shelterId) {
            return;
        }

        $shelter = DB::table('shelters')->where('id', $this->shelterId)->first();

        if ($shelter) {
            $this->lat = (string) $shelter->lat;
            $this->lng = (string) $shelter->lng;
        }
    }

    public function updatedQuota(): void
    {
        $this->quota = max(1, min(50, (int) $this->quota));

        if (count($this->selectedProfiles) > $this->quota) {
            $this->selectedProfiles = array_slice($this->selectedProfiles, 0, $this->quota);
        }
    }

    public function toggleProfile(int $profileId): void
    {
        if (in_array($profileId, $this->selectedProfiles, true)) {
            $this->selectedProfiles = array_values(array_diff($this->selectedProfiles, [$profileId]));

            return;
        }

        if (count($this->selectedProfiles) >= $this->quota) {
            $this->notify('Jumlah relawan yang dipilih tidak boleh melebihi kuota.', 'error');

            return;
        }

        $this->selectedProfiles[] = $profileId;
    }

    public function sendOffers(): void
    {
        if (! $this->isCoordinator()) {
            return;
        }

        $validated = $this->validate([
            'sosRequestId' => ['nullable', 'integer', 'exists:sos_requests,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'skillId' => ['required', 'integer', 'exists:skills,id'],
            'quota' => ['required', 'integer', 'min:1', 'max:50'],
            'startAt' => ['required', 'date'],
            'endAt' => ['required', 'date', 'after:startAt'],
            'shelterId' => ['nullable', 'integer', 'exists:shelters,id'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'title.required' => 'Judul tugas wajib diisi.',
            'description.required' => 'Deskripsi tugas wajib diisi.',
            'skillId.required' => 'Keahlian yang dibutuhkan wajib dipilih.',
            'startAt.required' => 'Waktu mulai wajib diisi.',
            'endAt.after' => 'Waktu selesai harus setelah waktu mulai.',
            'lat.required' => 'Lokasi tugas wajib ditentukan.',
        ]);

        $coordinator = auth()->user();
        $eventId = DB::table('disaster_events')->where('status', 'aktif')->value('id')
            ?? DB::table('disaster_events')->value('id');

        if (! $eventId) {
            $this->notify('Belum ada kejadian bencana aktif. Tugas tidak dapat dibuat.', 'error');

            return;
        }

        $task = DB::transaction(function () use ($validated, $coordinator, $eventId) {
            $task = VolunteerTask::create([
                'disaster_event_id' => $eventId,
                'shelter_id' => $validated['shelterId'] ?? null,
                'sos_request_id' => $validated['sosRequestId'] ?? null,
                'skill_id' => $validated['skillId'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'quota' => $validated['quota'],
                'start_at' => $validated['startAt'],
                'end_at' => $validated['endAt'],
                'status' => 'terbuka',
                'created_by' => $coordinator->id,
            ]);

            $profiles = VolunteerProfile::with('user')
                ->whereIn('id', array_slice($this->selectedProfiles, 0, $validated['quota']))
                ->get();

            foreach ($profiles as $profile) {
                TaskAssignment::create([
                    'volunteer_task_id' => $task->id,
                    'volunteer_profile_id' => $profile->id,
                    'status' => 'ditawarkan',
                ]);

                $profile->user?->notify(new TaskOfferReceived($task));
            }

            if ($validated['sosRequestId']) {
                DB::table('sos_requests')
                    ->where('id', $validated['sosRequestId'])
                    ->update(['status' => 'ditangani', 'updated_at' => now()]);
            }

            return $task;
        });

        $offered = min(count($this->selectedProfiles), $validated['quota']);

        $this->notify($offered > 0
            ? "Tugas \"{$task->title}\" dibuat dan penawaran dikirim ke {$offered} relawan."
            : "Tugas \"{$task->title}\" berhasil dibuat.");

        $this->closePanel();
    }

    public function saveChanges(): void
    {
        if (! $this->isCoordinator() || ! $this->editingId) {
            return;
        }

        $task = VolunteerTask::findOrFail($this->editingId);

        if (! in_array($task->status, ['terbuka', 'berjalan'], true)) {
            $this->notify('Tugas yang sudah selesai tidak dapat diubah.', 'error');

            return;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'skillId' => ['required', 'integer', 'exists:skills,id'],
            'quota' => ['required', 'integer', 'min:1', 'max:50'],
            'startAt' => ['required', 'date'],
            'endAt' => ['required', 'date', 'after:startAt'],
            'shelterId' => ['nullable', 'integer', 'exists:shelters,id'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'title.required' => 'Judul tugas wajib diisi.',
            'description.required' => 'Deskripsi tugas wajib diisi.',
            'skillId.required' => 'Keahlian yang dibutuhkan wajib dipilih.',
            'endAt.after' => 'Waktu selesai harus setelah waktu mulai.',
            'lat.required' => 'Lokasi tugas wajib ditentukan.',
        ]);

        $task->update([
            'shelter_id' => $validated['shelterId'] ?? null,
            'skill_id' => $validated['skillId'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'quota' => $validated['quota'],
            'start_at' => $validated['startAt'],
            'end_at' => $validated['endAt'],
        ]);

        $task->assignments()
            ->whereIn('status', ['ditawarkan', 'diterima'])
            ->with('volunteerProfile.user')
            ->get()
            ->each(fn (TaskAssignment $assignment) => $assignment->volunteerProfile?->user?->notify(new TaskUpdated($task)));

        $offered = $this->sendOffersToSelected($task);

        $this->notify($offered > 0
            ? "Perubahan tugas \"{$task->title}\" disimpan, {$offered} penawaran baru dikirim, dan relawan yang ditugaskan diberi notifikasi."
            : "Perubahan tugas \"{$task->title}\" disimpan dan relawan yang ditugaskan diberi notifikasi.");

        $this->closePanel();
    }

    private function sendOffersToSelected(VolunteerTask $task): int
    {
        if (empty($this->selectedProfiles)) {
            return 0;
        }

        $taken = $task->assignments()
            ->whereIn('status', ['ditawarkan', 'diterima', 'selesai'])
            ->count();

        $remaining = max(0, $task->quota - $taken);

        if ($remaining === 0) {
            return 0;
        }

        $profiles = VolunteerProfile::with('user')
            ->whereIn('id', array_slice($this->selectedProfiles, 0, $remaining))
            ->get();

        foreach ($profiles as $profile) {
            TaskAssignment::updateOrCreate(
                [
                    'volunteer_task_id' => $task->id,
                    'volunteer_profile_id' => $profile->id,
                ],
                ['status' => 'ditawarkan', 'reject_reason' => null]
            );

            $profile->user?->notify(new TaskOfferReceived($task));
        }

        return $profiles->count();
    }

    public function reoffer(int $assignmentId): void
    {
        if (! $this->isCoordinator()) {
            return;
        }

        $assignment = TaskAssignment::with('volunteerTask', 'volunteerProfile.user')->find($assignmentId);

        if (! $assignment) {
            return;
        }

        $task = $assignment->volunteerTask;

        if (! $task || ! in_array($task->status, ['terbuka', 'berjalan'], true)) {
            $this->notify('Penawaran hanya bisa dikirim ulang untuk tugas yang belum selesai.', 'error');

            return;
        }

        if ($assignment->status !== 'ditolak') {
            $this->notify('Penawaran ini sudah aktif atau sudah selesai.', 'error');

            return;
        }

        $taken = $task->assignments()
            ->whereIn('status', ['ditawarkan', 'diterima', 'selesai'])
            ->count();

        if ($taken >= $task->quota) {
            $this->notify('Kuota tugas sudah penuh. Tambah kuota dulu sebelum menawar ulang.', 'error');

            return;
        }

        $assignment->update(['status' => 'ditawarkan', 'reject_reason' => null]);

        $volunteer = $assignment->volunteerProfile?->user;
        $volunteer?->notify(new TaskOfferReceived($task));

        $this->notify('Penawaran dikirim ulang ke '.($volunteer?->name ?? 'relawan').'.');
    }

    public function completeTask(int $taskId): void
    {
        if (! $this->isCoordinator()) {
            return;
        }

        $task = VolunteerTask::findOrFail($taskId);

        if ($task->status === 'selesai') {
            $this->notify('Tugas ini sudah selesai.', 'error');

            return;
        }

        if (! in_array($task->status, ['terbuka', 'berjalan'], true)) {
            $this->notify('Tugas yang dibatalkan tidak dapat diselesaikan.', 'error');

            return;
        }

        $accepted = $task->assignments()->where('status', 'diterima')->count();

        if ($accepted === 0) {
            $this->notify('Belum ada relawan yang menerima tugas ini. Kirim penawaran dulu.', 'error');

            return;
        }

        DB::transaction(function () use ($task) {
            $task->assignments()
                ->where('status', 'diterima')
                ->update(['status' => 'selesai', 'updated_at' => now()]);

            $task->assignments()
                ->where('status', 'ditawarkan')
                ->update([
                    'status' => 'ditolak',
                    'reject_reason' => 'Tugas sudah diselesaikan.',
                    'updated_at' => now(),
                ]);

            $task->update(['status' => 'selesai']);

            $task->assignments()
                ->where('status', 'selesai')
                ->with('volunteerProfile.user')
                ->get()
                ->each(fn (TaskAssignment $assignment) => $assignment->volunteerProfile?->user?->notify(new TaskUpdated($task)));
        });

        $this->notify("Tugas \"{$task->title}\" ditandai selesai dan {$accepted} relawan menerima notifikasi.");
        $this->detailId = $task->id;
    }

    public function cancelTask(int $taskId): void
    {
        if (! $this->isCoordinator()) {
            return;
        }

        $task = VolunteerTask::findOrFail($taskId);

        if ($task->status === 'selesai') {
            $this->notify('Tugas yang sudah selesai tidak dapat dibatalkan.', 'error');

            return;
        }

        $task->update(['status' => 'dibatalkan', 'cancel_reason' => 'Dibatalkan oleh koordinator.']);

        $task->assignments()
            ->with('volunteerProfile.user')
            ->get()
            ->each(fn (TaskAssignment $assignment) => $assignment->volunteerProfile?->user?->notify(new TaskUpdated($task)));

        $this->notify("Tugas \"{$task->title}\" dibatalkan.");
        $this->closePanel();
    }

    public function acceptOffer(int $assignmentId): void
    {
        $assignment = $this->ownAssignment($assignmentId);

        if (! $assignment || $assignment->status !== 'ditawarkan') {
            return;
        }

        $assignment->update(['status' => 'diterima']);

        $task = $assignment->volunteerTask;
        $accepted = $task->assignments()->whereIn('status', ['diterima', 'selesai'])->count();

        if ($accepted >= $task->quota) {
            $task->update(['status' => 'berjalan']);
        }

        $this->notify("Penawaran tugas \"{$task->title}\" diterima.");
    }

    public function declineOffer(int $assignmentId): void
    {
        $assignment = $this->ownAssignment($assignmentId);

        if (! $assignment || $assignment->status !== 'ditawarkan') {
            return;
        }

        $assignment->update(['status' => 'ditolak', 'reject_reason' => 'Ditolak oleh relawan.']);

        $this->notify('Penawaran tugas ditolak.');
    }

    private function ownAssignment(int $assignmentId): ?TaskAssignment
    {
        $profileId = auth()->user()?->volunteerProfile?->id;

        if (! $profileId) {
            return null;
        }

        return TaskAssignment::where('id', $assignmentId)
            ->where('volunteer_profile_id', $profileId)
            ->first();
    }

    public function tasks()
    {
        $query = DB::table('volunteer_tasks')
            ->leftJoin('skills', 'volunteer_tasks.skill_id', '=', 'skills.id')
            ->select('volunteer_tasks.*', 'skills.name as skill_name')
            ->selectSub(
                DB::table('task_assignments')
                    ->selectRaw('count(*)')
                    ->whereColumn('task_assignments.volunteer_task_id', 'volunteer_tasks.id')
                    ->whereIn('status', ['ditawarkan', 'diterima', 'selesai']),
                'taken'
            );

        if ($this->statusFilter !== 'semua') {
            $query->where('volunteer_tasks.status', $this->statusFilter);
        }

        if (! $this->isCoordinator()) {
            $profileId = auth()->user()?->volunteerProfile?->id;
            $query->whereExists(function ($sub) use ($profileId) {
                $sub->selectRaw('1')
                    ->from('task_assignments')
                    ->whereColumn('task_assignments.volunteer_task_id', 'volunteer_tasks.id')
                    ->where('task_assignments.volunteer_profile_id', $profileId ?? 0);
            });
        }

        return $query
            ->orderByRaw("CASE volunteer_tasks.status WHEN 'terbuka' THEN 1 WHEN 'berjalan' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
            ->orderByDesc('volunteer_tasks.start_at')
            ->get();
    }

    public function statusCounts(): array
    {
        $rows = DB::table('volunteer_tasks')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'semua' => array_sum($rows),
            'terbuka' => $rows['terbuka'] ?? 0,
            'berjalan' => $rows['berjalan'] ?? 0,
            'selesai' => $rows['selesai'] ?? 0,
        ];
    }

    public function recommendations(): array
    {
        if (! $this->skillId || ! $this->isCoordinator()) {
            return [];
        }

        $start = $this->startAt ? Carbon::parse($this->startAt) : null;
        $end = $this->endAt ? Carbon::parse($this->endAt) : null;

        $profiles = VolunteerProfile::with(['user', 'skills'])
            ->where('availability', 'siap')
            ->whereHas('skills', fn ($query) => $query->where('skills.id', $this->skillId))
            ->get()
            ->filter(fn (VolunteerProfile $profile) => $profile->user && $profile->user->is_active);

        $rows = [];

        foreach ($profiles as $profile) {
            $activeCount = DB::table('task_assignments')
                ->join('volunteer_tasks', 'volunteer_tasks.id', '=', 'task_assignments.volunteer_task_id')
                ->where('task_assignments.volunteer_profile_id', $profile->id)
                ->whereIn('task_assignments.status', ['ditawarkan', 'diterima'])
                ->whereIn('volunteer_tasks.status', ['terbuka', 'berjalan'])
                ->count();

            $conflict = $start && $end && DB::table('task_assignments')
                ->join('volunteer_tasks', 'volunteer_tasks.id', '=', 'task_assignments.volunteer_task_id')
                ->where('task_assignments.volunteer_profile_id', $profile->id)
                ->whereIn('task_assignments.status', ['ditawarkan', 'diterima'])
                ->whereIn('volunteer_tasks.status', ['terbuka', 'berjalan'])
                ->when($this->editingId, fn ($query) => $query->where('volunteer_tasks.id', '!=', $this->editingId))
                ->where('volunteer_tasks.start_at', '<', $end)
                ->where('volunteer_tasks.end_at', '>', $start)
                ->exists();

            if ($conflict) {
                continue;
            }

            $rows[] = [
                'profile_id' => $profile->id,
                'name' => $profile->user->name,
                'distance' => $this->distanceKm(
                    $profile->domicile_lat !== null ? (float) $profile->domicile_lat : null,
                    $profile->domicile_lng !== null ? (float) $profile->domicile_lng : null,
                    is_numeric($this->lat) ? (float) $this->lat : null,
                    is_numeric($this->lng) ? (float) $this->lng : null,
                ),
                'active' => $activeCount,
                'selected' => in_array($profile->id, $this->selectedProfiles, true),
            ];
        }

        usort($rows, function (array $a, array $b) {
            $distanceA = $a['distance'] ?? PHP_INT_MAX;
            $distanceB = $b['distance'] ?? PHP_INT_MAX;

            return [$distanceA, $a['active']] <=> [$distanceB, $b['active']];
        });

        return $rows;
    }

    public function offersForDetail(): array
    {
        if (! $this->detailId) {
            return [];
        }

        return DB::table('task_assignments')
            ->join('volunteer_profiles', 'volunteer_profiles.id', '=', 'task_assignments.volunteer_profile_id')
            ->join('users', 'users.id', '=', 'volunteer_profiles.user_id')
            ->where('task_assignments.volunteer_task_id', $this->detailId)
            ->select('task_assignments.id', 'task_assignments.status', 'users.name', 'volunteer_profiles.domicile_lat', 'volunteer_profiles.domicile_lng')
            ->orderBy('users.name')
            ->get()
            ->all();
    }

    public function myOfferStatus(int $taskId): ?string
    {
        $profileId = auth()->user()?->volunteerProfile?->id;

        if (! $profileId) {
            return null;
        }

        return DB::table('task_assignments')
            ->where('volunteer_task_id', $taskId)
            ->where('volunteer_profile_id', $profileId)
            ->value('status');
    }

    private function distanceKm(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        $earth = 6371;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        return round(2 * $earth * asin(min(1, sqrt($a))), 1);
    }

    public function notify(string $message, string $type = 'success'): void
    {
        $this->notification = $message;
        $this->notificationType = $type;
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    public function render()
    {
        $detailTask = $this->detailId ? VolunteerTask::find($this->detailId) : null;
        $profileId = auth()->user()?->volunteerProfile?->id;

        $myAssignments = $profileId
            ? DB::table('task_assignments')
                ->where('volunteer_profile_id', $profileId)
                ->get(['id', 'volunteer_task_id', 'status'])
                ->keyBy('volunteer_task_id')
            : collect();

        return view('livewire.volunteer.task-manager', [
            'tasks' => $this->tasks(),
            'myAssignments' => $myAssignments,
            'counts' => $this->statusCounts(),
            'skills' => Skill::orderBy('id')->get(),
            'shelters' => DB::table('shelters')->orderBy('name')->get(['id', 'name']),
            'sosOptions' => DB::table('sos_requests')
                ->join('users', 'sos_requests.requester_id', '=', 'users.id')
                ->whereIn('sos_requests.status', ['baru', 'ditangani'])
                ->select('sos_requests.id', 'sos_requests.help_type', 'sos_requests.people_count', 'users.name as requester_name')
                ->orderByDesc('sos_requests.created_at')
                ->get(),
            'recommendations' => $this->recommendations(),
            'detailTask' => $detailTask,
            'offers' => $this->offersForDetail(),
            'skillName' => $this->skillId ? Skill::where('id', $this->skillId)->value('name') : null,
        ]);
    }
}
