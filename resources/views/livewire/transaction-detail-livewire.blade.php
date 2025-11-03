<div>
    {{-- Header Halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <span class="text-muted fw-light">Transaksi /</span> Detail
        </h3>
        <a href="{{ route('app.home') }}" class="btn btn-secondary" wire:navigate>
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="row g-4">
        {{-- Kolom Kiri: Detail Transaksi --}}
        <div class="col-lg-7 col-md-6">
            <div class="card">
                <div class="card-body">
                    {{-- Judul Transaksi --}}
                    <h4 class="card-title mb-1">{{ $transaction->title }}</h4>

                    {{-- Tipe dan Tanggal --}}
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if ($transaction->type == 'income')
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill">
                                Pemasukan
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">
                                Pengeluaran
                            </span>
                        @endif
                        <span class="text-muted">
                            <i class="bi bi-calendar-event me-1"></i>
                            {{ $transaction->date->format('d F Y') }}
                        </span>
                    </div>

                    {{-- Jumlah (Amount) --}}
                    <div class="mb-4">
                        <h2 class="fw-bold mb-0">
                            @if ($transaction->type == 'income')
                                <span class="text-success">
                                    + Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-danger">
                                    - Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </span>
                            @endif
                        </h2>
                    </div>

                    {{-- Deskripsi --}}
                    <h5 class="fw-bold">Deskripsi</h5>
                    <p class="card-text fs-6">
                        {!! nl2br(e($transaction->description)) !!}
                    </p>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Bukti Transaksi (Cover) --}}
        <div class="col-lg-5 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-bold">Bukti Transaksi</h5>
                </div>
                <div class="card-body text-center">
                    {{-- Tampilkan Cover --}}
                    @if ($transaction->cover)
                        <img src="{{ asset('storage/' . $transaction->cover) }}"
                            alt="Bukti Transaksi" class="img-fluid rounded mb-3" style="max-height: 400px; object-fit: cover;">
                    @else
                        <div class="alert alert-secondary text-start">
                            <i class="bi bi-info-circle me-1"></i>
                            Belum ada bukti transaksi yang di-upload.
                        </div>
                    @endif

                    {{-- Tombol untuk Buka Modal Edit Cover --}}
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#editCoverTransactionModal">
                        <i class="bi bi-upload me-1"></i>
                        Ubah / Upload Bukti
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 
      Modal untuk edit cover. 
      File ini sudah Anda miliki di resources/views/components/modals/transactions/edit-cover.blade.php
      dan akan otomatis dipanggil oleh tombol di atas.
    --}}
    @include('components.modals.transactions.edit-cover')
</div>