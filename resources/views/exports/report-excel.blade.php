<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 16px; font-weight: bold;">
                LAPORAN KEUANGAN - {{ strtoupper(config('app.name', 'Kasku')) }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 12px;">
                Periode: {{ $periodLabel }} | Pemilik: {{ $user->name }} ({{ $user->email }})
            </th>
        </tr>
        <tr></tr>
    </thead>
    <tbody>
        <!-- Ringkasan Angka Utama -->
        <tr>
            <th colspan="2" style="font-weight: bold; background-color: #E2E8F0;">METRIK</th>
            <th colspan="2" style="font-weight: bold; background-color: #E2E8F0; text-align: right;">NOMINAL (RP)</th>
        </tr>
        <tr>
            <td colspan="2">Total Pemasukan</td>
            <td colspan="2" style="text-align: right; color: #0B7A55; font-weight: bold;">
                {{ $summary['total_income'] }}
            </td>
        </tr>
        <tr>
            <td colspan="2">Total Pengeluaran</td>
            <td colspan="2" style="text-align: right; color: #B93A2B; font-weight: bold;">
                {{ $summary['total_expense'] }}
            </td>
        </tr>
        <tr>
            <td colspan="2">Biaya Admin / Pajak Transfer</td>
            <td colspan="2" style="text-align: right; color: #B93A2B; font-weight: bold;">
                {{ $summary['total_transfer_fee'] }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Arus Kas Bersih (Selisih)</td>
            <td colspan="2" style="text-align: right; font-weight: bold;">
                {{ $summary['net_cashflow'] }}
            </td>
        </tr>

        <tr></tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #CBD5E1;">RINCIAN PERIODE</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #F1F5F9;">Waktu</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Pemasukan</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Pengeluaran</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Selisih Bersih</th>
        </tr>
        @foreach ($summary['breakdown'] as $b)
            <tr>
                <td>{{ $b['label'] }}</td>
                <td style="text-align: right;">{{ $b['income'] }}</td>
                <td style="text-align: right;">{{ $b['expense'] + $b['transfer_fee'] }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $b['net'] }}</td>
            </tr>
        @endforeach

        <tr></tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #CBD5E1;">PENGELUARAN PER KATEGORI</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #F1F5F9;">Kategori</th>
            <th style="font-weight: bold; background-color: #F1F5F9;">Frekuensi</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Porsi (%)</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Total Nominal (Rp)</th>
        </tr>
        @foreach ($categories as $c)
            <tr>
                <td>{{ $c['name'] }}</td>
                <td>{{ $c['count'] }} kali</td>
                <td style="text-align: right;">{{ $c['percent'] }}%</td>
                <td style="text-align: right; font-weight: bold;">{{ $c['amount'] }}</td>
            </tr>
        @endforeach

        <tr></tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #CBD5E1;">MUTASI PER SUMBER SALDO</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #F1F5F9;">Sumber Saldo</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Pemasukan</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Pengeluaran dan Transfer</th>
            <th style="font-weight: bold; background-color: #F1F5F9; text-align: right;">Saldo Saat Ini</th>
        </tr>
        @foreach ($sources as $s)
            <tr>
                <td>{{ $s['name'] }} ({{ $s['type'] }})</td>
                <td style="text-align: right;">{{ $s['income'] + $s['transfers_in'] }}</td>
                <td style="text-align: right;">{{ $s['expense'] + $s['transfers_out'] + $s['transfer_fee'] }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $s['current_balance'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
