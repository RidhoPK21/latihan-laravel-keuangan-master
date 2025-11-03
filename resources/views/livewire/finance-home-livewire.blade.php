<div>
    <!-- Modern Header Card -->
    <div class="mt-3">
        <div class="card rounded-3 shadow-sm border-0 overflow-hidden">
            <div class="row g-0 align-items-center p-3 bg-white">
                <div class="col-md-8 d-flex align-items-center">
                    <div class="me-3">
                        <div class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-weight:600;">
                            {{ strtoupper(substr($auth->name,0,1)) }}
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-0">Halo, <span class="fw-semibold">{{ $auth->name }}</span></h4>
                        <small class="text-muted">Selamat datang kembali! Kelola transaksi keuangan Anda di sini.</small>
                    </div>
                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('auth.logout') }}" class="btn btn-outline-warning me-2">Keluar</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </button>
                </div>
            </div>

            <div class="card-body border-top p-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Cari judul atau deskripsi transaksi..."
                                   wire:model.live.debounce.300ms="search" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" wire:model.live="filterType">
                            <option value="all">Semua Tipe</option>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik / Grafik Card -->
    <div class="row my-4">
        <div class="col-12">
            <div class="card rounded-3 shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-semibold">Statistik Keuangan</h6>
                        <small class="text-muted">Grafik Pemasukan vs Pengeluaran berdasarkan filter saat ini</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Periode: <strong>{{ $currentPeriod ?? 'Semua' }}</strong></small>
                    </div>
                </div>
                <div class="card-body">
                    <div wire:ignore>
                        <div id="finance-chart" style="min-height:260px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Transaksi Card -->
    <div class="card rounded-3 shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Daftar Transaksi</h5>
            <small class="text-muted">Menampilkan {{ $transactions->total() }} transaksi</small>
        </div>

        <div class="card-body p-0">
            @if ($transactions->isEmpty())
                <div class="p-4 text-center">
                    <div class="mb-2">
                        <i class="bi bi-wallet2" style="font-size:28px;color:#6c757d;"></i>
                    </div>
                    <h6 class="mb-1 text-muted">Tidak ada transaksi</h6>
                    <p class="small text-muted mb-0">Tidak ditemukan transaksi untuk kriteria pencarian atau filter saat ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th style="width:56px">No</th>
                                <th style="min-width:140px">Tanggal</th>
                                <th>Tipe</th>
                                <th class="text-end">Jumlah</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $key => $transaction)
                                <tr class="border-top">
                                    <td class="small text-muted">{{ $transactions->firstItem() + $loop->index }}</td>
                                    <td class="fw-medium">{{ $transaction->date->format('d F Y') }}</td>
                                    <td>
                                        @if ($transaction->type == 'income')
                                            <span class="badge rounded-pill bg-light text-success border">Pemasukan</span>
                                        @else
                                            <span class="badge rounded-pill bg-light text-danger border">Pengeluaran</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($transaction->amount,0,',','.') }}</td>
                                    <td class="small">
                                        <div class="text-truncate" style="max-width:320px;" title="{{ $transaction->description }}">
                                            {{ $transaction->description }}
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('app.transactions.detail', ['transaction_id' => $transaction->id]) }}" class="btn btn-sm btn-outline-info me-1">Detail</a>
                                        <button wire:click="prepareEditTransaction({{ $transaction->id }})" class="btn btn-sm btn-outline-warning me-1">Edit</button>
                                        <button wire:click="prepareDeleteTransaction({{ $transaction->id }})" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 border-top">
                    <div>
                        <small class="text-muted">Menampilkan <strong>{{ $transactions->firstItem() }}</strong> - <strong>{{ $transactions->lastItem() }}</strong> dari <strong>{{ $transactions->total() }}</strong></small>
                    </div>
                    <div>
                        {{ $transactions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Floating action button for small screens -->
    <div class="position-fixed end-0 bottom-0 p-3" style="z-index:1050;">
        <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width:56px;height:56px;" data-bs-toggle="modal" data-bs-target="#addTransactionModal" aria-label="Tambah Transaksi">
            <i class="bi bi-plus-lg" style="font-size:20px"></i>
        </button>
    </div>

    {{-- Modals --}}
    @include('components.modals.transactions.add')
    @include('components.modals.transactions.edit')
</div>
