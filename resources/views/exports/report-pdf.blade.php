<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $periodLabel }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1A202C;
            line-height: 1.5;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #2B3A8C;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #2B3A8C;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            color: #718096;
            margin-top: 4px;
        }
        .metrics-grid {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            border-collapse: collapse;
        }
        .metrics-grid td {
            width: 33.33%;
            padding: 10px 14px;
            vertical-align: top;
            border-right: 1px solid #E2E8F0;
        }
        .metrics-grid td:last-child {
            border-right: none;
        }
        .metric-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #718096;
            font-weight: bold;
        }
        .metric-value {
            font-size: 15px;
            font-weight: bold;
            margin-top: 4px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2D3748;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.data-table th {
            background-color: #F7FAFC;
            color: #4A5568;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border-bottom: 1px solid #CBD5E0;
        }
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #EDF2F7;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-green { color: #0B7A55; }
        .text-red { color: #B93A2B; }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
            font-size: 9px;
            color: #A0AEC0;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ config('app.name', 'Kasku') }} — Laporan Keuangan</h1>
        <div class="subtitle">
            Periode: <strong>{{ $periodLabel }}</strong> | Pemilik: {{ $user->name }} ({{ $user->email }})
        </div>
    </div>

    <!-- Metrik Utama -->
    <table class="metrics-grid">
        <tr>
            <td>
                <div class="metric-label">Total Pemasukan</div>
                <div class="metric-value text-green">+{{ rupiah($summary['total_income']) }}</div>
            </td>
            <td>
                <div class="metric-label">Total Pengeluaran & Biaya</div>
                <div class="metric-value text-red">-{{ rupiah($summary['total_expense'] + $summary['total_transfer_fee']) }}</div>
            </td>
            <td>
                <div class="metric-label">Arus Kas Bersih (Selisih)</div>
                <div class="metric-value {{ $summary['net_cashflow'] >= 0 ? 'text-green' : 'text-red' }}">
                    {{ $summary['net_cashflow'] >= 0 ? '+' : '' }}{{ rupiah($summary['net_cashflow']) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Rincian Waktu -->
    <div class="section-title">{{ $periodType === 'yearly' ? 'Rincian Per Bulan' : 'Rincian Harian' }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th class="text-right">Pemasukan</th>
                <th class="text-right">Pengeluaran & Biaya</th>
                <th class="text-right">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($summary['breakdown'] as $b)
                <tr>
                    <td>{{ $b['label'] }}</td>
                    <td class="text-right text-green">+{{ rupiah($b['income']) }}</td>
                    <td class="text-right text-red">-{{ rupiah($b['expense'] + $b['transfer_fee']) }}</td>
                    <td class="text-right {{ $b['net'] >= 0 ? 'text-green' : 'text-red' }}" style="font-weight: bold;">
                        {{ $b['net'] >= 0 ? '+' : '' }}{{ rupiah($b['net']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #A0AEC0;">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Kategori Pengeluaran -->
    <div class="section-title">Distribusi Pengeluaran Per Kategori</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Frekuensi</th>
                <th class="text-right">Porsi (%)</th>
                <th class="text-right">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $cat)
                <tr>
                    <td>{{ $cat['name'] }}</td>
                    <td>{{ $cat['count'] }} kali</td>
                    <td class="text-right">{{ $cat['percent'] }}%</td>
                    <td class="text-right" style="font-weight: bold;">{{ rupiah($cat['amount']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #A0AEC0;">Belum ada pengeluaran per kategori.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Sumber Saldo -->
    <div class="section-title">Ringkasan Mutasi Sumber Saldo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Sumber Saldo</th>
                <th class="text-right">Pemasukan</th>
                <th class="text-right">Pengeluaran</th>
                <th class="text-right">Transfer Masuk</th>
                <th class="text-right">Transfer Keluar</th>
                <th class="text-right">Saldo Saat Ini</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sources as $s)
                <tr>
                    <td>{{ $s['name'] }} ({{ $s['type'] }})</td>
                    <td class="text-right text-green">+{{ rupiah($s['income']) }}</td>
                    <td class="text-right text-red">-{{ rupiah($s['expense']) }}</td>
                    <td class="text-right text-green">+{{ rupiah($s['transfers_in']) }}</td>
                    <td class="text-right text-red">-{{ rupiah($s['transfers_out'] + $s['transfer_fee']) }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ rupiah($s['current_balance']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh {{ config('app.name', 'Kasku') }} pada {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
