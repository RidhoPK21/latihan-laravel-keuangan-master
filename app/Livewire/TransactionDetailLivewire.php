<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TransactionDetailLivewire extends Component
{
    use WithFileUploads;

    public $transaction;
    public $auth;

    // Properti untuk Ubah Cover (Nama disamakan dengan Blade)
    public $editCoverFile;

    public function mount()
    {
        $this->auth = Auth::user();
        $transaction_id = request()->route('transaction_id');
        
        $targetTransaction = Transaction::where('id', $transaction_id)->where('user_id', $this->auth->id)->first();
        if (!$targetTransaction) {
            return redirect()->route('app.home');
        }

        $this->transaction = $targetTransaction;
    }

    public function render()
    {
        return view('livewire.transaction-detail-livewire');
    }

    /**
     * Logika untuk Simpan Cover Transaksi
     */
   /**
     * Logika untuk Simpan Cover Transaksi
     */
// app/Livewire/TransactionDetailLivewire.php

    public function saveCover()
    {
        $this->validate([
            'editCoverFile' => 'required|image|max:2048', // Max 2MB
        ]);

        try {
            if ($this->editCoverFile) {
                // Hapus cover lama jika ada
                if ($this->transaction->cover && Storage::disk('public')->exists($this->transaction->cover)) {
                    Storage::disk('public')->delete($this->transaction->cover);
                }

                // ... (logika penyimpanan file Anda) ...
                
                $path = $this->editCoverFile->storeAs('covers', $filename, 'public');

                $this->transaction->cover = $path;
                $this->transaction->save();

                // =========================================================
                // TAMBAHKAN BARIS INI
                // =========================================================
                // Ini akan mengambil data terbaru dari database ke properti $this->transaction
                $this->transaction->refresh();
                // =========================================================

            }

            $this->reset(['editCoverFile']);
            $this->dispatch('closeModal', id: 'editCoverTransactionModal');
            
            // PASTIKAN BARIS INI BENAR
            $this->dispatch('showAlert', icon: 'success', message: 'Bukti transaksi berhasil diubah.');

        } catch (\Exception $e) {
            // PASTIKAN BARIS INI JUGA BENAR
            $this->dispatch('showAlert', icon: 'error', message: 'Gagal mengubah bukti transaksi: ' . $e->getMessage());
        }
    }
}
