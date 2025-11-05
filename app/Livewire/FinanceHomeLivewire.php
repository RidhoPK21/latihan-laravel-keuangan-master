<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On; // Pastikan ini di-import

class FinanceHomeLivewire extends Component
{
    use WithPagination;

    public $auth;
    public $search = '';
    public $filterType = 'all';

    // Properti Tambah Transaksi
    public $addTransactionTitle;
    public $addTransactionDescription;
    public $addTransactionAmount;
    public $addTransactionType = 'income';
    public $addTransactionDate;

    // Properti Edit Transaksi
    public $editTransactionId;
    public $editTransactionTitle;
    public $editTransactionDescription;
    public $editTransactionAmount;
    public $editTransactionType;
    public $editTransactionDate;

    // Properti Hapus Transaksi
    public $deleteTransactionId;
    public $deleteTransactionTitle;
    
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->auth = Auth::user();
    }
    
    // ===============================================
    // BARU: Listener "Handshake" dari JavaScript
    // ===============================================
    /**
     * Listener untuk event dari JS yang menandakan chart sudah siap.
     * Memanggil method ini akan memicu re-render,
     * yang kemudian akan mengirim data chart terbaru.
     */
    #[On('chart-ready-for-data')]
    public function chartIsReady()
    {
        // Tidak perlu melakukan apa-apa di sini.
        // Menerima event ini saja sudah cukup untuk memicu
        // metode render() agar berjalan lagi.
    }
    // ===============================================

    public function resetFilters()
    {
        $this->reset('search', 'filterType');
    }

    // Di file: app/Livewire/FinanceHomeLivewire.php

    public function render()
    {
        $userId = auth()->id() ?? 0;
        
        $query = Transaction::where('user_id', $userId);
        
        // 1. Logika Pencarian
        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search); 
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        // 2. Logika Filter Tipe Transaksi
        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        // ===============================================
        // Logika Agregasi Data (Untuk Chart DAN Stat Cards)
        // ===============================================
        
        // Clone query agar perhitungan total sesuai filter
        $chartQuery = clone $query;
        $totalIncome = (float) $chartQuery->where('type', 'income')->sum('amount');
        
        // Clone lagi dan reset 'type'
        $chartQuery = clone $query; 
        $totalExpense = (float) $chartQuery->where('type', 'expense')->sum('amount');

        // ==== BARU: Hitung Total Saldo ====
        $totalBalance = $totalIncome - $totalExpense;

        // Data untuk Chart
        $chartData = [
            'series' => [$totalIncome, $totalExpense],
            'labels' => ['Pemasukan', 'Pengeluaran'],
        ];

        // Kirim data ke JavaScript SETIAP render
        $this->dispatch('update-chart', data: $chartData);
        // ===============================================

        // 3. Ambil data dengan Pagination
        $transactions = $query
            ->orderBy('date', 'desc') 
            ->paginate(20);

        // ==== BARU: Kirim total ke view ====
        $data = [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalBalance' => $totalBalance, // Variabel baru dikirim
        ];

        return view('livewire.finance-home-livewire', $data);
    }

    /**
     * Logika untuk Tambah Transaksi
     */
    public function addTransaction()
    {
        $this->validate([
            'addTransactionTitle' => 'required|string|max:255',
            'addTransactionDescription' => 'required|string', 
            'addTransactionAmount' => 'required|numeric',
            'addTransactionType' => 'required|in:income,expense',
            'addTransactionDate' => 'required|date',
        ]);

        try {
            Transaction::create([
                'user_id' => auth()->id(), 
                'title' => $this->addTransactionTitle,
                'description' => $this->addTransactionDescription,
                'amount' => $this->addTransactionAmount,
                'type' => $this->addTransactionType,
                'date' => $this->addTransactionDate, 
            ]);

            $this->reset(['addTransactionTitle', 'addTransactionDescription', 'addTransactionAmount', 'addTransactionType', 'addTransactionDate']);
            $this->dispatch('closeModal', id: 'addTransactionModal');
            
            // NOTIFIKASI SUKSES (dikirim sebagai objek)
            $this->dispatch('showAlert', icon: 'success', message: 'Transaksi berhasil ditambahkan.');

        } catch (\Exception $e) {
            // NOTIFIKASI GAGAL (dikirim sebagai objek)
            $this->dispatch('showAlert', icon: 'error', message: 'Gagal menambahkan transaksi.');
        }
    }

    /**
     * Logika untuk Persiapan Edit Transaksi
     */
    public function prepareEditTransaction($id)
    {
        $transaction = Transaction::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$transaction) {
            $this->dispatch('showAlert', icon: 'error', message: 'Data tidak ditemukan untuk diubah.');
            return;
        }

        $this->editTransactionId = $transaction->id;
        $this->editTransactionTitle = $transaction->title;
        $this->editTransactionDescription = $transaction->description;
        $this->editTransactionAmount = $transaction->amount;
        $this->editTransactionType = $transaction->type;
        $this->editTransactionDate = $transaction->date->format('Y-m-d'); 

        $this->dispatch('showModal', id: 'editTransactionModal');
    }

    /**
     * Logika untuk Simpan Edit Transaksi
     */
    public function editTransaction()
    {
        $this->validate([
            'editTransactionTitle' => 'required|string|max:255',
            'editTransactionDescription' => 'required|string',
            'editTransactionAmount' => 'required|numeric',
            'editTransactionType' => 'required|in:income,expense',
            'editTransactionDate' => 'required|date',
        ]);

        $transaction = Transaction::where('id', $this->editTransactionId)->where('user_id', auth()->id())->first();
        
        if (!$transaction) {
            $this->dispatch('showAlert', icon: 'error', message: 'Data transaksi tidak tersedia.');
            return;
        }

        try {
            $transaction->title = $this->editTransactionTitle;
            $transaction->description = $this->editTransactionDescription;
            $transaction->amount = $this->editTransactionAmount;
            $transaction->type = $this->editTransactionType;
            $transaction->date = $this->editTransactionDate;
            $transaction->save();

            $this->reset(['editTransactionId', 'editTransactionTitle', 'editTransactionDescription', 'editTransactionAmount', 'editTransactionType', 'editTransactionDate']);
            $this->dispatch('closeModal', id: 'editTransactionModal');
            
            // NOTIFIKASI SUKSES (dikirim sebagai objek)
            $this->dispatch('showAlert', icon: 'success', message: 'Transaksi berhasil diubah.');

        } catch (\Exception $e) {
            // Perbaikan typo $this-dispatch
            $this->dispatch('showAlert', icon: 'error', message: 'Gagal mengubah transaksi.');
        }
    }

    // ===============================================
    // LOGIKA DELETE DENGAN SWEETALERT2
    // ===============================================

    public function prepareDeleteTransaction($id)
    {
        $transaction = Transaction::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$transaction) {
            $this->dispatch('showAlert', icon: 'error', message: 'Data tidak ditemukan untuk dihapus.');
            return;
        }

        $this->deleteTransactionId = $transaction->id;
        $this->deleteTransactionTitle = $transaction->title;

        // TAMPILKAN KONFIRMASI (dikirim sebagai objek)
        $this->dispatch('showConfirm', 
            title: 'Yakin Hapus?',
            text: "Anda akan menghapus transaksi: " . $this->deleteTransactionTitle . ". Tindakan ini tidak dapat dibatalkan.",
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus Saja!',
            method: 'executeDeleteTransaction' 
        );
    }

    #[On('executeDeleteTransaction')] // Listener untuk event dari JS
    public function executeDeleteTransaction()
    {
        try {
            $transaction = Transaction::where('id', $this->deleteTransactionId)->where('user_id', auth()->id())->first();

            if ($transaction) {
                if ($transaction->cover && \Illuminate\Support\Facades\Storage::disk('public')->exists($transaction->cover)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($transaction->cover);
                }
                
                $transaction->delete();
                $this->dispatch('showAlert', icon: 'success', message: 'Transaksi berhasil dihapus.');
            } else {
                $this->dispatch('showAlert', icon: 'error', message: 'Gagal menghapus. Transaksi tidak ditemukan.');
            }

            $this->reset(['deleteTransactionId', 'deleteTransactionTitle']);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', icon: 'error', message: 'Terjadi kesalahan saat menghapus data.');
        }
    }
}

