@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-semibold text-gray-800">Laporan Kinerja</h2>
    <div class="flex space-x-2">
        <a href="{{ route('reports.export', ['type' => 'daily']) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm">
            Export Excel
        </a>
        <a href="{{ route('reports.print', ['type' => 'daily']) }}" target="_blank" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm">
            Print PDF
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Laporan</label>
                <select id="report_type" name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="daily" {{ request('type') == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ request('type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                </select>
            </div>
            
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm">
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
    
    @if($report)
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Total Pasien</p>
                <p class="text-2xl font-bold">{{ $report['summary']['total_patients'] }}</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Rata-rata Waktu Tunggu</p>
                <p class="text-2xl font-bold">{{ $report['summary']['avg_wait_time'] }} menit</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Kepuasan Pasien</p>
                <p class="text-2xl font-bold">{{ number_format($report['summary']['avg_satisfaction'], 1) }}/5</p>
            </div>
        </div>
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail per Peran</h3>
        
        @foreach($report['by_role'] as $role => $data)
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 mb-2 capitalize">{{ $role }}</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pasien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Tunggu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi Konsultasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kepuasan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['top_performers'] as $performer)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $performer['name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $performer['patients_served'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $performer['wait_time'] ?? '-' }} menit
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $performer['consultation_duration'] ?? '-' }} menit
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($performer['satisfaction_score'] ?? 0, 1) }}/5
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Grafik Performa</h3>
            <div class="h-80">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>

@if($report)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(@json($report['charts_data']['daily_patients'])),
            datasets: [
                {
                    label: 'Jumlah Pasien',
                    data: Object.values(@json($report['charts_data']['daily_patients'])),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true
                },
                {
                    label: 'Waktu Tunggu (menit)',
                    data: Object.values(@json($report['charts_data']['wait_times'])),
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Pasien'
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Waktu (menit)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection