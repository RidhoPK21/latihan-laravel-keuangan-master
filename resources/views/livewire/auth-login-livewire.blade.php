<form wire:submit.prevent="login">
    {{-- Kartu dibuat modern, konsisten dengan dashboard --}}
    <div class="card rounded-3 shadow-sm border-0" style="max-width: 400px; margin: auto;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="/logo.png" alt="Logo" style="width: 72px;">
                <h2 class="h4 fw-bold mt-3 mb-0">Masuk Akun</h2>
                <small class="text-muted">Selamat datang kembali!</small>
            </div>
            
            <hr class="my-4">

            {{-- Alamat Email dengan Icon --}}
            <div class="form-group mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" wire:model="email" placeholder="contoh@email.com">
                </div>
                @error('email')
                    <span class="text-danger small mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kata Sandi dengan Icon --}}
            <div class="form-group mb-3">
                <label class="form-label">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" wire:model="password" placeholder="••••••••">
                </div>
                @error('password')
                    <span class="text-danger small mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kirim (Tombol full-width) --}}
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary btn-block w-100 fw-semibold">
                    <span wire:loading.remove wire:target="login">Masuk</span>
                    <span wire:loading wire:target="login" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
            </div>

            <hr class="my-4">
            <p class="text-center small mb-0">
                Belum memiliki akun? <a href="{{ route('auth.register') }}">Daftar di sini</a>
            </p>
        </div>
    </div>
</form>