<!doctype html>
<html lang="id">

<head>
    {{-- Meta --}}
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Icon --}}
    <link rel="icon" href="/logo.png" type="image/x-icon" />

    {{-- Judul --}}
    <title>Laravel Todolist</title>

    {{-- Styles --}}
    @livewireStyles
    <link rel="stylesheet" href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    
    {{-- SweetAlert2 CDN (Disarankan ditaruh di <head>) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">
    <div class="container-fluid">
        @yield('content')
    </div>

    {{-- Scripts --}}
    <script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- @livewireScripts HARUS ADA SEBELUM SCRIPT KUSTOM YANG MENGGUNAKAN 'Livewire' --}}
    @livewireScripts

    <!-- =============================================== -->
    <!-- APEXCHARTS CDN                                  -->
    <!-- =============================================== -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- =============================================== -->

    <script>
        // Definisikan variabel chart di scope global
        let financeChart;

        // ======================================================
        // SEMUA LISTENER LIVEWIRE HARUS DI DALAM SINI
        // Ini dijalankan setelah Livewire siap, tapi sebelum komponen memuat
        // ======================================================
        document.addEventListener("livewire:initialized", () => {
            
            // Listener untuk menutup modal Bootstrap
            Livewire.on("closeModal", (data) => {
                const modalEl = document.getElementById(data.id);
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                }
            });

            // Listener untuk menampilkan modal Bootstrap
            Livewire.on("showModal", (data) => {
                const modalEl = document.getElementById(data.id);
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (modal) {
                        modal.show();
                    }
                }
            });

            // Listener untuk Validasi Gagal (Struktur diperbaiki)
            Livewire.hook('message.failed', ({ component, updateQueue, errors }) => {
                if (errors) {
                    const errorMessages = Object.values(errors).flat();
                    const firstError = errorMessages[0];
                    
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                    });

                    Toast.fire({
                        icon: 'error',
                        title: firstError
                    });
                    
                    return false; 
                }
            });

            // Listener Alert (Toast) Biasa (Struktur diperbaiki)
            Livewire.on('showAlert', (data) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: data.icon, // Sudah benar (tanpa [0])
                    title: data.message // Sudah benar (tanpa [0])
                });
            });

            // Listener Alert Konfirmasi Hapus (Struktur diperbaiki)
            Livewire.on('showConfirm', (data) => {
                Swal.fire({
                    title: data.title, // Sudah benar (tanpa [0])
                    text: data.text, // Sudah benar (tanpa [0])
                    icon: data.icon, // Sudah benar (tanpa [0])
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: data.confirmButtonText || 'Ya, Lanjutkan!', // Sudah benar (tanpa [0])
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(data.method); // Sudah benar (tanpa [0])
                    }
                });
            });
            
            // Listener untuk 'update-chart' dari Livewire (Struktur diperbaiki)
            Livewire.on('update-chart', (event) => {
                if (financeChart) {
                    const chartData = event.data; // Sudah benar (tanpa [0])

                    if (chartData) {
                        const isDataEmpty = chartData.series.every(item => item === 0);

                        if (isDataEmpty) {
                             financeChart.updateOptions({
                                series: [1], 
                                labels: ['Tidak ada data'],
                                colors: ['#E0E0E0'],
                                tooltip: { y: { formatter: (val) => "Tidak ada data" } }
                             });
                        } else {
                            financeChart.updateOptions({
                                series: chartData.series,
                                labels: chartData.labels,
                                colors: ['#28a745', '#dc3545'] 
                            });
                        }
                    }
                }
            });

        }); // <- INI PENUTUP UNTUK 'livewire:initialized'

        // ======================================================
        // 2. INISIALISASI HALAMAN (DOM)
        // ======================================================
        
        document.addEventListener('DOMContentLoaded', function () {
            
            const chartOptions = {
                chart: {
                    type: 'donut',
                    height: 350
                },
                series: [], // Dimulai kosong
                labels: [], // Dimulai kosong
                colors: ['#28a745', '#dc3545'], 
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    position: 'right',
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                },
                dataLabels: {
                    formatter: function (val, opts) {
                        // Jangan tampilkan persen jika datanya 0
                        return opts.w.config.series[opts.seriesIndex] > 0 ? val.toFixed(1) + '%' : '';
                    }
                }
            };

            // Inisialisasi chart saat halaman dimuat
            const chartElement = document.getElementById('finance-chart');
            if (chartElement) {
                // 'financeChart' sekarang diisi,
                // sehingga listener 'update-chart' di atas bisa menemukannya.
                financeChart = new ApexCharts(chartElement, chartOptions);
                financeChart.render();
                
                // "Handshake" - Beri tahu Livewire bahwa chart sudah siap
                Livewire.dispatch('chart-ready-for-data');
            }

        });
    </script>
    
</body>

</html>

