<form wire:submit.prevent="register">
    {{-- Kartu dibuat modern, konsisten dengan dashboard --}}
    <div class="card rounded-3 shadow-sm border-0" style="max-width: 400px; margin: auto;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="/logo.png" alt="Logo" style="width: 72px;">
                <h2 class="h4 fw-bold mt-3 mb-0">Buat Akun Baru</h2>
                <small class="text-muted">Silakan isi data Anda.</small>
            </div>
            
            <hr class="my-4">

            {{-- Nama dengan Icon --}}
            <div class="form-group mb-3">
                <label class="form-label">Nama</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" wire:model="name" placeholder="Nama Lengkap Anda">
                </div>
                @error('name')
                    <span class="text-danger small mt-1">{{ $message }}</span>
                @enderror
            </div>

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
                    <span wire:loading.remove wire:target="register">Daftar</span>
                    <span wire:loading wire:target="register" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
            </div>

            <hr class="my-4">
            <p class="text-center small mb-0">
                Sudah memiliki akun? <a href="{{ route('auth.login') }}">Masuk di sini</a>
            </p>
        </div>
    </div>
</form>