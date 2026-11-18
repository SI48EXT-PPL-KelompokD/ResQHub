<dialog id="cancel-modal" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl bg-white p-0 shadow-xl backdrop:bg-gray-900/50">
    <form method="POST" class="p-5">
        @csrf
        @method('DELETE')

        <h2 class="text-lg font-semibold text-gray-900">Batalkan janji <span data-slot="code"></span>?</h2>
        <p class="mt-2 text-sm text-gray-500">
            Jumlah dijanjikan pada kebutuhan berkurang <span data-slot="qty"></span> dan koordinator posko menerima notifikasi.
        </p>

        <label for="cancel_reason" class="mt-4 mb-2 block text-sm font-medium text-gray-900">Alasan (wajib)</label>
        <select id="cancel_reason" name="cancel_reason"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
            <option value="Barang tidak jadi tersedia" selected>Barang tidak jadi tersedia</option>
            <option value="Jadwal pengiriman berubah">Jadwal pengiriman berubah</option>
            <option value="Kebutuhan sudah dipenuhi pihak lain">Kebutuhan sudah dipenuhi pihak lain</option>
        </select>

        <div class="mt-5 flex flex-col-reverse gap-2 min-[380px]:flex-row min-[380px]:justify-end">
            <button type="button" data-close
                    class="min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-blue-300">
                Kembali
            </button>
            <button type="submit"
                    class="min-h-11 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">
                Batalkan Janji
            </button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('cancel-modal');
        const form = dialog.querySelector('form');
        let trigger = null;

        document.querySelectorAll('[data-cancel]').forEach(btn => btn.addEventListener('click', () => {
            trigger = btn;
            form.action = btn.dataset.action;
            dialog.querySelector('[data-slot=code]').textContent = btn.dataset.code;
            dialog.querySelector('[data-slot=qty]').textContent = btn.dataset.qty;
            document.body.style.overflow = 'hidden';
            dialog.showModal();
        }));

        dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
        dialog.addEventListener('close', () => {
            document.body.style.overflow = '';
            trigger?.focus();
        });
    })();
</script>

