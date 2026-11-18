@props(['points' => [], 'zoom' => 12])

{{-- Peta posko berbasis data asli. Titik diwarnai sesuai sisa kapasitas. --}}
<div
    wire:ignore
    x-data="{
        points: @js($points),
        colors: { tersedia: '#15803d', hampir_penuh: '#b45309', penuh: '#b91c1c' },
        map: null,
        init() { this.draw(); },
        draw() {
            if (typeof L === 'undefined') { setTimeout(() => this.draw(), 200); return; }
            if (! this.points.length || this.map) { return; }
            this.map = L.map(this.$refs.canvas, { scrollWheelZoom: false, zoomControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap',
            }).addTo(this.map);
            const bounds = [];
            this.points.forEach((p) => {
                const color = this.colors[p.status] ?? this.colors.tersedia;
                const icon = L.divIcon({
                    className: '',
                    iconSize: [22, 22],
                    iconAnchor: [11, 11],
                    html: `<span style='display:block;width:22px;height:22px;border-radius:50%;background:${color};border:3px solid #fff;box-shadow:0 1px 4px rgba(15,42,82,.45)'></span>`,
                });
                const popup = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = p.name;
                const detail = document.createElement('div');
                detail.textContent = `${p.statusLabel} · sisa ${p.remaining} tempat`;
                popup.append(title, detail);
                L.marker([p.lat, p.lng], { icon, title: p.name }).addTo(this.map).bindPopup(popup);
                bounds.push([p.lat, p.lng]);
            });
            this.map.fitBounds(bounds, { padding: [48, 48], maxZoom: {{ (int) $zoom }} });
            setTimeout(() => this.map && this.map.invalidateSize(), 200);
        },
    }"
    {{ $attributes->merge(['class' => 'relative isolate bg-slate-200']) }}
>
    <div x-ref="canvas" class="absolute inset-0" role="img" aria-label="Peta sebaran posko evakuasi yang beroperasi"></div>
    @if (empty($points))
        <p class="absolute inset-0 flex items-center justify-center p-6 text-center text-sm text-slate-600">Belum ada posko yang beroperasi.</p>
    @endif
</div>
