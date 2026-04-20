<?php

namespace App\Notifications;

use App\Models\LaporanGangguan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KeluhanBaruNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LaporanGangguan $laporan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'laporan_id' => $this->laporan->id,
            'kode_keluhan' => $this->laporan->kode_keluhan,
            'judul' => $this->laporan->judul,
            'pelapor' => $this->laporan->pelapor,
            'prioritas' => $this->laporan->prioritas,
            'koordinat' => [
                'latitude' => $this->laporan->latitude,
                'longitude' => $this->laporan->longitude,
            ],
            'url' => route('keluhan.show', $this->laporan),
            'message' => sprintf(
                'Keluhan baru: %s | Pelapor: %s | Prioritas: %s',
                $this->laporan->judul,
                $this->laporan->pelapor ?? '-',
                ucfirst($this->laporan->prioritas ?? 'sedang')
            ),
        ];
    }
}
