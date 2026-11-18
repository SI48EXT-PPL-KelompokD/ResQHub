<?php

namespace Tests\Feature;

use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use App\Models\EarlyWarning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisasterEventModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_hanya_mengembalikan_kejadian_aktif(): void
    {
        $aktif = DisasterEvent::factory()->create(['status' => 'aktif']);
        DisasterEvent::factory()->create(['status' => 'selesai']);

        $hasil = DisasterEvent::active()->pluck('id');

        $this->assertSame([$aktif->id], $hasil->all());
    }

    public function test_relasi_laporan_dan_peringatan_dini(): void
    {
        $event = DisasterEvent::factory()->create();
        DisasterReport::factory()->count(2)->create(['disaster_event_id' => $event->id]);
        EarlyWarning::factory()->create(['disaster_event_id' => $event->id]);

        $event->refresh();

        $this->assertCount(2, $event->reports);
        $this->assertCount(1, $event->earlyWarnings);
        $this->assertIsFloat($event->lat);
        $this->assertNotNull($event->started_at);
    }
}
