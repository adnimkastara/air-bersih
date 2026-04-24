<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PelangganResource;
use App\Models\LaporanGangguan;
use App\Models\MeterRecord;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Desa;
use App\Models\User;
use App\Notifications\KeluhanBaruNotification;
use App\Services\WhatsAppService;
use App\Services\GenerateCustomerCodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FieldAppController extends Controller
{
    private const PAYMENT_METHODS = [
        'tunai' => ['tunai', 'cash', 'CASH', 'Tunai'],
        'transfer_bank' => ['transfer_bank', 'transfer', 'bank_transfer', 'Transfer Bank'],
        'e_wallet' => ['e_wallet', 'ewallet', 'e-wallet', 'dompet_digital', 'E-Wallet'],
    ];

    public function __construct(private readonly GenerateCustomerCodeService $customerCodeService)
    {
    }

    public function pelangganIndex(Request $request)
    {
        $query = Pelanggan::query()->with(['desa', 'kecamatan', 'assignedPetugas'])->orderBy('name');
        if (! $request->user()->isKecamatanLevel()) {
            $query->where('desa_id', $request->user()->desa_id);
        }

        if ($keyword = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('kode_pelanggan', 'like', "%{$keyword}%")
                    ->orWhere('nomor_meter', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $paginator = $query->paginate((int) $request->integer('per_page', 20));
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Pelanggan $item) => (new PelangganResource($item))->resolve())
        );

        return $this->successResponse(
            'Daftar pelanggan berhasil diambil.',
            $paginator
        );
    }

    public function pelangganShow(Request $request, Pelanggan $pelanggan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        return $this->successResponse('Detail pelanggan berhasil diambil.', new PelangganResource($pelanggan->load(['desa', 'kecamatan', 'assignedPetugas'])));
    }

    public function pelangganStore(Request $request)
    {
        $isKecamatanLevel = $request->user()->isKecamatanLevel();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'dusun' => ['required', 'string', 'max:255'],
            'jenis_pelanggan' => ['required', 'string', 'max:100'],
            'nomor_meter' => ['required', 'string', 'max:50', Rule::unique('pelanggans', 'nomor_meter')],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_id' => [$isKecamatanLevel ? 'required' : 'nullable', 'exists:desas,id'],
            'assigned_petugas_id' => ['nullable', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        if (! $isKecamatanLevel) {
            $data['desa_id'] = $request->user()->desa_id;
        }
        $this->abortUnlessCanAccessDesa($request, $data['desa_id']);

        $pelanggan = DB::transaction(function () use ($data) {
            $desa = Desa::query()->lockForUpdate()->findOrFail($data['desa_id']);
            $generated = $this->customerCodeService->nextForDesa($desa);
            $data['kode_pelanggan'] = $generated['kode_pelanggan'];
            $data['nomor_urut_desa'] = $generated['nomor_urut_desa'];

            return Pelanggan::create($data);
        });

        return $this->successResponse('Pelanggan berhasil dibuat.', new PelangganResource($pelanggan->load(['desa', 'kecamatan', 'assignedPetugas'])), 201);
    }

    public function pelangganUpdate(Request $request, Pelanggan $pelanggan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        $isKecamatanLevel = $request->user()->isKecamatanLevel();
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'required', 'string', 'max:1000'],
            'dusun' => ['sometimes', 'required', 'string', 'max:255'],
            'jenis_pelanggan' => ['sometimes', 'required', 'string', 'max:100'],
            'nomor_meter' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('pelanggans', 'nomor_meter')->ignore($pelanggan->id)],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_id' => [$isKecamatanLevel ? 'sometimes' : 'nullable', 'exists:desas,id'],
            'assigned_petugas_id' => ['nullable', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['sometimes', 'required', 'in:aktif,nonaktif'],
        ]);

        if (! $isKecamatanLevel) {
            $data['desa_id'] = $request->user()->desa_id;
        }

        if (isset($data['desa_id'])) {
            $this->abortUnlessCanAccessDesa($request, $data['desa_id']);
        }

        $pelanggan->update($data);

        return $this->successResponse('Pelanggan berhasil diperbarui.', new PelangganResource($pelanggan->fresh()->load(['desa', 'kecamatan', 'assignedPetugas'])));
    }

    public function pelangganDestroy(Request $request, Pelanggan $pelanggan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        $hasDependency = $pelanggan->meterRecords()->exists()
            || $pelanggan->tagihans()->exists()
            || $pelanggan->laporanGangguans()->exists();

        if ($hasDependency) {
            return $this->errorResponse('Pelanggan tidak dapat dihapus karena sudah memiliki data transaksi/riwayat.', 422);
        }

        $pelanggan->delete();

        return $this->successResponse('Pelanggan berhasil dihapus.', null);
    }

    public function meterIndex(Request $request)
    {
        $query = MeterRecord::with('pelanggan')->orderByDesc('recorded_at');
        if (! $request->user()->isKecamatanLevel()) {
            $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        return $this->successResponse('Daftar catat meter berhasil diambil.', $query->paginate((int) $request->integer('per_page', 20)));
    }

    public function meterStore(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => ['required', 'integer', 'exists:pelanggans,id'],
            'meter_previous_month' => ['required', 'integer', 'min:0'],
            'meter_current_month' => ['required', 'integer', 'min:0'],
            'recorded_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_recorded_at' => ['nullable', 'date'],
        ]);

        $pelanggan = Pelanggan::findOrFail($data['pelanggan_id']);
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        $data['petugas_id'] = $request->user()->id;
        $data['verification_status'] = 'pending';
        $data['is_anomaly'] = (int) $data['meter_current_month'] < (int) $data['meter_previous_month'];

        return $this->successResponse('Meter record tersimpan.', MeterRecord::create($data), 201);
    }

    public function tagihanIndex(Request $request)
    {
        $query = Tagihan::with(['pelanggan', 'pembayarans'])->orderByDesc('period');
        if (! $request->user()->isKecamatanLevel()) {
            $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        if ($request->filled('pelanggan_id')) {
            $query->where('pelanggan_id', $request->integer('pelanggan_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }
        if ($request->filled('period')) {
            $query->where('period', (string) $request->string('period'));
        }
        if ($request->boolean('unpaid_only')) {
            $query->whereIn('status', ['draft', 'terbit', 'menunggak']);
        }

        return $this->successResponse('Daftar tagihan berhasil diambil.', $query->paginate((int) $request->integer('per_page', 20)));
    }

    public function tagihanOpen(Request $request)
    {
        $request->merge(['unpaid_only' => true]);

        return $this->tagihanIndex($request);
    }

    public function tagihanShow(Request $request, Tagihan $tagihan)
    {
        $tagihan->load(['pelanggan', 'meterRecord', 'tarif', 'pembayarans.petugas']);
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);
        }

        $totalPaid = (float) $tagihan->pembayarans->sum('amount');

        return $this->successResponse('Detail tagihan berhasil diambil.', [
                'tagihan' => $tagihan,
                'total_paid' => $totalPaid,
                'remaining' => max(0, (float) $tagihan->amount - $totalPaid),
            ]);
    }

    public function tagihanPublish(Request $request, Tagihan $tagihan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);
        }
        $tagihan->status = 'terbit';
        $tagihan->save();

        return $this->successResponse('Tagihan berhasil diterbitkan.', $tagihan->fresh());
    }

    public function tagihanGenerate(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $period = \Carbon\Carbon::createFromFormat('Y-m', $validated['period'])->startOfMonth();
        $monthStart = $period->copy()->startOfMonth();
        $monthEnd = $period->copy()->endOfMonth();

        $meterRecords = MeterRecord::with('pelanggan')
            ->whereBetween('recorded_at', [$monthStart, $monthEnd])
            ->when(! $request->user()->isKecamatanLevel(), fn ($query) => $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id)))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($meterRecords as $record) {
            if (! $record->pelanggan) {
                $skipped++;
                continue;
            }

            $exists = Tagihan::where('pelanggan_id', $record->pelanggan_id)
                ->where('period', $period->format('Y-m'))
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $tarif = $record->pelanggan->activeTarif();
            if (! $tarif) {
                $skipped++;
                continue;
            }

            $usage = max(0, (int) ($record->meter_current_month - $record->meter_previous_month));
            $abonemenAmount = (float) $tarif->abonemen;
            $baseAmount = (float) $tarif->tarif_dasar;
            $usageAmount = $usage * (float) $tarif->tarif_per_m3;
            $totalAmount = $abonemenAmount + $baseAmount + $usageAmount;

            Tagihan::create([
                'pelanggan_id' => $record->pelanggan_id,
                'meter_record_id' => $record->id,
                'tarif_id' => $tarif->id,
                'amount' => $totalAmount,
                'status' => 'draft',
                'due_date' => $monthEnd->copy()->addDays(10),
                'period' => $period->format('Y-m'),
                'usage_m3' => $usage,
                'base_amount' => $abonemenAmount + $baseAmount,
                'usage_amount' => $usageAmount,
                'late_fee' => 0,
                'generated_at' => now(),
            ]);

            $created++;
        }

        return $this->successResponse("Generate tagihan {$period->format('Y-m')} selesai.", [
                'created' => $created,
                'skipped' => $skipped,
                'period' => $period->format('Y-m'),
            ]);
    }

    public function paymentMethods()
    {
        return $this->successResponse('Daftar metode pembayaran tersedia.', [
            'canonical_values' => array_keys(self::PAYMENT_METHODS),
            'aliases' => self::PAYMENT_METHODS,
        ]);
    }

    public function pembayaranIndex(Request $request)
    {
        $query = Pembayaran::with(['tagihan.pelanggan', 'petugas'])->orderByDesc('paid_at');
        if (! $request->user()->isKecamatanLevel()) {
            $query->whereHas('tagihan.pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        if ($request->filled('tagihan_id')) {
            $query->where('tagihan_id', $request->integer('tagihan_id'));
        }
        if ($request->filled('payment_method')) {
            $method = $this->normalizePaymentMethod((string) $request->string('payment_method'));
            $query->where('payment_method', $method ?? (string) $request->string('payment_method'));
        }

        return $this->successResponse('Riwayat pembayaran berhasil diambil.', $query->paginate((int) $request->integer('per_page', 20)));
    }

    public function pembayaranShow(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->load(['tagihan.pelanggan', 'petugas']);
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pembayaran->tagihan?->pelanggan?->desa_id);
        }

        return $this->successResponse('Detail pembayaran berhasil diambil.', $pembayaran);
    }

    public function pembayaranStore(Request $request)
    {
        $canonicalMethod = $this->normalizePaymentMethod($request->input('payment_method'));
        if ($canonicalMethod) {
            $request->merge(['payment_method' => $canonicalMethod]);
        }

        $data = $request->validate([
            'tagihan_id' => ['required', 'integer', 'exists:tagihans,id'],
            'payment_method' => ['required', Rule::in(array_keys(self::PAYMENT_METHODS))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tagihan = Tagihan::with('pelanggan')->findOrFail($data['tagihan_id']);
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);
        }

        $data['petugas_id'] = $request->user()->id;

        $duplicatePaymentExists = Pembayaran::query()
            ->where('tagihan_id', $data['tagihan_id'])
            ->where('payment_method', $data['payment_method'])
            ->where('paid_at', $data['paid_at'])
            ->where('amount', $data['amount'])
            ->exists();

        if ($duplicatePaymentExists) {
            return $this->errorResponse('Pembayaran duplikat terdeteksi untuk tagihan, nominal, metode, dan tanggal yang sama.', 422);
        }

        $totalPaidBefore = (float) Pembayaran::query()->where('tagihan_id', $tagihan->id)->sum('amount');
        $remaining = max(0, (float) $tagihan->amount - $totalPaidBefore);

        if ((float) $data['amount'] > ($remaining + 0.01)) { // Allow 0.01 tolerance for rounding
            return $this->errorResponse("Nominal melebihi sisa tagihan. Sisa saat ini: {$remaining}", 422);
        }

        $payment = DB::transaction(function () use ($data, $tagihan, $totalPaidBefore) {
            $payment = Pembayaran::create($data);

            $totalPaidAfter = $totalPaidBefore + (float) $data['amount'];
            if ($totalPaidAfter >= ((float) $tagihan->amount - 0.01)) {
                $tagihan->status = 'lunas';
                $tagihan->save();
            }

            return $payment;
        });

        return $this->successResponse('Pembayaran tersimpan.', $payment->load(['tagihan.pelanggan', 'petugas']), 201);
    }

    public function keluhanIndex(Request $request)
    {
        $query = LaporanGangguan::with(['pelanggan', 'reporter'])->latest('reported_at');
        if (! $request->user()->isKecamatanLevel()) {
            $query->where('desa_id', $request->user()->desa_id);
        }

        return $this->successResponse(
            'Daftar keluhan berhasil diambil.',
            $query->paginate((int) $request->integer('per_page', 20))
        );
    }

    public function keluhanStore(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggans,id'],
            'pelapor' => ['required_without:pelanggan_id', 'nullable', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'jenis_laporan' => ['required', 'in:gangguan,keluhan'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'status_penanganan' => ['nullable', 'in:baru,diproses,selesai'],
            'lokasi_text' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tanggal_kejadian' => ['nullable', 'date'],
        ]);

        $pelanggan = ! empty($data['pelanggan_id']) ? Pelanggan::find($data['pelanggan_id']) : null;
        if ($pelanggan && ! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        $data['reported_by'] = $request->user()->id;
        $data['reported_at'] = now();
        $data['status_penanganan'] = $data['status_penanganan'] ?? 'baru';
        $data['kode_keluhan'] = 'KLH-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['desa_id'] = $pelanggan?->desa_id ?? $request->user()->desa_id;
        $data['kecamatan_id'] = $pelanggan?->kecamatan_id ?? $request->user()->kecamatan_id;
        $data['pelapor'] = $this->resolvePelaporName($data['pelapor'] ?? null, $pelanggan?->name);
        $data['no_hp'] = $data['no_hp'] ?: ($pelanggan?->phone ?? null);
        if (! LaporanGangguan::hasPrioritasColumn()) {
            unset($data['prioritas']);
        }
        $data = LaporanGangguan::filterExistingColumns($data);
        $laporan = LaporanGangguan::create($data);
        $this->sendPetugasNotifications($laporan);

        return $this->successResponse('Keluhan tersimpan.', $laporan, 201);
    }

    public function dashboardRingkas(Request $request)
    {
        $desaId = $request->user()->desa_id;

        return $this->successResponse('Ringkasan dashboard berhasil diambil.', [
                'total_pelanggan' => Pelanggan::when($desaId, fn ($q) => $q->where('desa_id', $desaId))->count(),
                'total_tagihan_aktif' => Tagihan::when($desaId, fn ($q) => $q->whereHas('pelanggan', fn ($sq) => $sq->where('desa_id', $desaId)))->whereIn('status', ['draft', 'terbit', 'menunggak'])->count(),
                'total_keluhan_aktif' => LaporanGangguan::when($desaId, fn ($q) => $q->where('desa_id', $desaId))->whereIn('status_penanganan', ['baru', 'diproses'])->count(),
            ]);
    }

    public function monitoringPeta(Request $request)
    {
        try {
            $gpsLat = $request->query('gps_latitude');
            $gpsLng = $request->query('gps_longitude');
            $desaId = $request->user()->isKecamatanLevel() ? null : $request->user()->desa_id;

            $pelanggans = collect();
            if (Schema::hasTable('pelanggans')) {
                $query = Pelanggan::query();

                if ($desaId !== null && Schema::hasColumn('pelanggans', 'desa_id')) {
                    $query->where('desa_id', $desaId);
                }

                if (Schema::hasColumns('pelanggans', ['latitude', 'longitude'])) {
                    $query->whereNotNull('latitude')->whereNotNull('longitude');

                    $availablePelangganColumns = Schema::getColumnListing('pelanggans');
                    $pelangganColumns = [];
                    foreach (['id', 'name', 'kode_pelanggan', 'latitude', 'longitude'] as $col) {
                        if (in_array($col, $availablePelangganColumns)) {
                            $pelangganColumns[] = $col;
                        }
                    }

                    $pelanggans = $query->get($pelangganColumns);
                }
            }

            $keluhans = collect();
            if (Schema::hasTable('laporan_gangguans')) {
                if (Schema::hasColumns('laporan_gangguans', ['latitude', 'longitude'])) {
                    $query = LaporanGangguan::query()
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude');

                    // Hapus when/fn yang bisa menyebabkan error closure / binding
                    if ($desaId !== null) {
                        if (Schema::hasColumn('laporan_gangguans', 'desa_id')) {
                            $query->where('desa_id', $desaId);
                        }
                    }

                    if (Schema::hasColumn('laporan_gangguans', 'status_penanganan')) {
                        $query->whereIn('status_penanganan', ['baru', 'diproses']);
                    }

                    // Tentukan secara eksplisit kolom yang aman
                    $availableColumns = Schema::getColumnListing('laporan_gangguans');
                    $selectColumns = [];
                    foreach (['id', 'judul', 'latitude', 'longitude', 'kode_keluhan', 'status_penanganan'] as $col) {
                        if (in_array($col, $availableColumns)) {
                            $selectColumns[] = $col;
                        }
                    }

                    $keluhans = $query->get($selectColumns);
                }
            }

            return $this->successResponse('Data monitoring berhasil diambil.', [
                'user_current_location' => [
                    'latitude' => $gpsLat,
                    'longitude' => $gpsLng,
                ],
                'fallback_center' => [
                    'latitude' => $gpsLat ?: -7.6189,
                    'longitude' => $gpsLng ?: 110.9507,
                ],
                'pelanggans' => $pelanggans,
                'keluhans' => $keluhans,
            ]);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error('Monitoring Map Error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return $this->errorResponse('Terjadi kendala saat memuat data monitoring: ' . $th->getMessage(), 500);
        }
    }

    private function normalizePaymentMethod(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $normalized = Str::lower(trim($raw));

        foreach (self::PAYMENT_METHODS as $canonical => $aliases) {
            $normalizedAliases = array_map(fn ($alias) => Str::lower(trim((string) $alias)), $aliases);
            if (in_array($normalized, $normalizedAliases, true)) {
                return $canonical;
            }
        }

        return null;
    }

    private function sendPetugasNotifications(LaporanGangguan $laporan): void
    {
        $petugasQuery = User::query()
            ->whereHas('role', fn (Builder $query) => $query->where('name', 'petugas_lapangan'))
            ->where('is_active', true);

        if ($laporan->desa_id) {
            $petugasQuery->where('desa_id', $laporan->desa_id);
        } elseif ($laporan->kecamatan_id) {
            $petugasQuery->where('kecamatan_id', $laporan->kecamatan_id);
        }

        $petugasList = $petugasQuery->get();
        if ($petugasList->isEmpty()) {
            $petugasList = User::query()
                ->whereHas('role', fn (Builder $query) => $query->where('name', 'petugas_lapangan'))
                ->where('is_active', true)
                ->get();
        }

        $whatsAppService = app(WhatsAppService::class);

        foreach ($petugasList as $petugas) {
            $petugas->notify(new KeluhanBaruNotification($laporan));

            if (! empty($petugas->no_hp)) {
                $whatsAppService->sendMessage(
                    $petugas->no_hp,
                    $this->buildWhatsAppMessage($laporan)
                );
            }
        }
    }

    private function buildWhatsAppMessage(LaporanGangguan $laporan): string
    {
        $lokasi = ($laporan->latitude !== null && $laporan->longitude !== null)
            ? sprintf('https://maps.google.com/?q=%s,%s', $laporan->latitude, $laporan->longitude)
            : '-';

        return "Keluhan Baru:\n"
            ."Judul: {$laporan->judul}\n"
            .'Pelapor: '.($laporan->pelapor ?? '-')."\n"
            .'Prioritas: '.ucfirst($laporan->prioritas ?? 'sedang')."\n"
            ."Lokasi: {$lokasi}";
    }

    private function resolvePelaporName(?string $inputPelapor, ?string $pelangganName): ?string
    {
        $candidate = trim((string) $inputPelapor);
        if ($candidate !== '') {
            return $candidate;
        }

        return $pelangganName;
    }

    private function successResponse(string $message, mixed $data = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status = 400, array $errors = [])
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
