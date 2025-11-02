<div>
    {{-- KARTU 1: Header, Selamat Datang, dan Kontrol Filter --}}
    <div class="mt-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3 d-flex">
                <div class="flex-fill">
                    <h3 class="mb-0">Hay, {{ $auth->name }}</h3>
                </div>
                <div>
                    <a href="{{ route('auth.logout') }}" class="btn btn-warning">Keluar</a>
                </div>
            </div>
            <div class="card-body">
                
                {{-- BAGIAN BARU: PENCARIAN DAN FILTER --}}
                <div class="row">
                    {{-- Kolom Pencarian --}}
                    <div class="col-md-6 mb-2 mb-md-0">
                        <input type="text" class="form-control" placeholder="Cari Judul atau Deskripsi Transaksi..." 
                               wire:model.live.debounce.300ms="search">
                    </div>
                    {{-- Kolom Filter Tipe --}}
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select class="form-select" wire:model.live="filterType">
                            <option value="all">Semua Tipe</option>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                    {{-- Tombol Tambah Transaksi --}}
                    <div class="col-md-3 d-flex justify-content-end">
                        <button class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#addTransactionModal">
                            Tambah Transaksi
                        </button>
                    </div>
                </div>
                {{-- AKHIR BAGIAN BARU: PENCARIAN DAN FILTER --}}

            </div>
        </div>
    </div>

    <!-- =============================================== -->
    <!-- BARU: Blok untuk Statistik dan Grafik           -->
    <!-- =============================================== -->
    <div class="row my-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Statistik Keuangan</h5>
                    <small class="text-muted">Grafik Pemasukan vs Pengeluaran berdasarkan filter Anda saat ini.</small>
                </div>
                <div class="card-body">
                    {{-- 
                      wire:ignore SANGAT PENTING. 
                      Ini memberitahu Livewire untuk tidak menyentuh elemen ini 
                      setelah dirender, sehingga ApexCharts dapat mengontrolnya.
                    --}}
                    <div wire:ignore>
                        <div id="finance-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- =============================================== -->
    <!-- AKHIR BLOK BARU                                 -->
    <!-- =============================================== -->

    {{-- KARTU 2: Daftar Transaksi (Tabel) --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h3 class="mb-0">Daftar Transaksi</h3>
        </div>

        {{-- Menggunakan card-body hanya jika ada data, atau untuk pesan error --}}
        <div class="card-body">
            @if ($transactions->isEmpty())
                <div class="alert alert-info text-center mb-0">
                    Tidak ada transaksi yang ditemukan untuk kriteria pencarian atau filter saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Jumlah</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Pagination dimulai dari halaman mana pun, bukan selalu 1 --}}
                            @foreach ($transactions as $key => $transaction)
                                <tr>
                                    <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                    <td>{{ $transaction->date->format('d F Y') }}</td>
                                    <td>
                                        @if ($transaction->type == 'income')
                                            <span class="badge bg-success">Pemasukan</span>
                                        @else
                                            <span class="badge bg-danger">Pengeluaran</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($transaction->amount) }}</td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $transaction->description }}">
                                            {{ $transaction->description }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('app.transactions.detail', ['transaction_id' => $transaction->id]) }}"
                                           class="btn btn-sm btn-info">
                                           Detail
                                        </a>
                                        <button wire:click="prepareEditTransaction({{ $transaction->id }})"
                                                class="btn btn-sm btn-warning">
                                            Edit
                                        </button>
                                        <button wire:click="prepareDeleteTransaction({{ $transaction->id }})"
                                                class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- BAGIAN PAGINATION --}}
                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
                {{-- AKHIR PAGIAN BARU --}}

            @endif
        </div>
    </div>

    {{-- Modals --}}
    @include('components.modals.transactions.add')
    @include('components.modals.transactions.edit')
    {{-- Modal Delete tidak diperlukan jika menggunakan SweetAlert --}}
    {{-- @include('components.modals.transactions.delete') --}}
</div>

