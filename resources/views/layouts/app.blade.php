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
    <title>Aplikasi Keuangan</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Styles --}}
    @livewireStyles
    <link rel="stylesheet" href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* PERUBAHAN: Warna latar belakang diubah agar card putih lebih menonjol */
            background-color: #eef2f6; 
        }
    </style>
</head>

<body>
    {{-- Konten dibungkus container-xl untuk max-width di desktop --}}
    <div class="container-xl"> 
        @yield('content')
    </div>

    {{-- Scripts --}}
    <script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Definisikan variabel chart di scope global
        let financeChart;

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
            // Listener Alert (Toast) Biasa (Struktur diperbaiki)
           // Listener Alert (Toast) Biasa (Struktur diperbaiki)
Livewire.on('showAlert', (event) => { 
    // HAPUS BARIS "const data = event.data;"
    // Properti 'icon' dan 'message' ada langsung di 'event'

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
        icon: event.icon,     // <-- UBAH KE event.icon
        title: event.message  // <-- UBAH KE event.message
    });
});

            // Listener Alert Konfirmasi Hapus (Struktur diperbaiki)
            Livewire.on('showConfirm', (data) => {
                Swal.fire({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: data.confirmButtonText || 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(data.method);
                    }
                });
            });
            
            // Listener untuk 'update-chart' dari Livewire (Struktur diperbaiki)
            Livewire.on('update-chart', (event) => {
                if (financeChart) {
                    const chartData = event.data;

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

        document.addEventListener('DOMContentLoaded', function () {
            
            const chartOptions = {
                chart: {
                    type: 'donut',
                    height: 350,
                    fontFamily: 'Inter, sans-serif' // Terapkan font di chart
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

            const chartElement = document.getElementById('finance-chart');
            if (chartElement) {
                financeChart = new ApexCharts(chartElement, chartOptions);
                financeChart.render();
                
                Livewire.dispatch('chart-ready-for-data');
            }

        });
    </script>
    
</body>
</html>