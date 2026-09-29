<table>
    <thead>
        <tr>
            <th colspan="6" style="font-size: 16px; font-weight: bold;">
                DAFTAR TRANSAKSI - {{ strtoupper(config('app.name', 'Kasku')) }}
            </th>
        </tr>
        <tr>
            <th colspan="6" style="font-size: 11px;">
                Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} | Pemilik: {{ $user->name }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #2B3A8C; color: #FFFFFF;">Tanggal</th>
            <th style="font-weight: bold; background-color: #2B3A8C; color: #FFFFFF;">Tipe</th>
            <th style="font-weight: bold; background-color: #2B3A8C; color: #FFFFFF;">Kategori</th>
            <th style="font-weight: bold; background-color: #2B3A8C; color: #FFFFFF;">Sumber Saldo</th>
            <th style="font-weight: bold; background-color: #2B3A8C; color: #FFFFFF; text-align: right;">Nominal (Rp)</th>
            <th style="font-weight: bold; background-color: #2B3A8C; color: #FFFFFF;">Catatan / Deskripsi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($transactions as $tx)
            <tr>
                <td>{{ $tx->date->translatedFormat('d M Y') }}</td>
                <td>{{ $tx->type->value === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                <td>{{ $tx->category?->name ?? ($tx->is_adjustment ? 'Penyesuaian Saldo' : '-') }}</td>
                <td>{{ $tx->accountSource?->name ?? '-' }}</td>
                <td style="text-align: right; font-weight: bold; color: {{ $tx->type->value === 'income' ? '#0B7A55' : '#B93A2B' }};">
                    {{ $tx->amount }}
                </td>
                <td>{{ $tx->description ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
