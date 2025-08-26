<table>
    {{-- BAGIAN 1: INFORMASI FILTER --}}
    <tr>
        <td colspan="8" style="font-weight: bold; font-size: 16px;">LAPORAN PEMBELIAN</td>
    </tr>
    <tr>
        <td>Tanggal Mulai:</td>
        <td>{{ $filters['start_date'] }}</td>
    </tr>
    <tr>
        <td>Tanggal Selesai:</td>
        <td>{{ $filters['end_date'] }}</td>
    </tr>
    <tr>
        <td>Supplier:</td>
        <td>{{ $filters['supplier'] }}</td>
    </tr>
    <tr>
        <td>User:</td>
        <td>{{ $filters['user'] }}</td>
    </tr>
    <tr>
        <td>Total Transaksi Produk:</td>
        <td>Rp {{ number_format($summary['sumTotalPurchase'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Total Item Dibeli:</td>
        <td>{{ $summary['totalItemsPurchased'] }}</td>
    </tr>
    <tr>
        <td>Total Pembelian:</td>
        <td>{{ $summary['totalTransaction'] }}</td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>

    {{-- BAGIAN 2: RINGKASAN SUPPLIER --}}
    <tr>
        <td colspan="8" style="font-weight: bold;">Ringkasan per Supplier</td>
    </tr>
    <tr>
        <th>Supplier</th>
        <th>Total Pembelian</th>
        <th>Jumlah Item</th>
    </tr>
    @foreach ($summary['suppliers'] as $supplier)
        <tr>
            <td>{{ $supplier->name }}</td>
            <td>Rp {{ number_format($supplier->total, 0, ',', '.') }}</td>
            <td>{{ $supplier->qty }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>

    {{-- BAGIAN 4: DETAIL TRANSAKSI --}}
    <tr>
        <td colspan="8" style="font-weight: bold; font-size: 14px;">Detail Transaksi</td>
    </tr>
    <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Nama Produk</th>
        <th>Qty</th>
        <th>Harga Satuan</th>
        <th>Total Harga</th>
        <th>Supplier</th>
        <th>User</th>
    </tr>

    @php $no = 1; @endphp

    @foreach ($purchase as $trx)
        @foreach ($trx->purcaseItems as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</td>
                <td>{{ $trx->supplier->name }}</td>
                <td>{{ $trx->user->name }}</td>
            </tr>
        @endforeach
    @endforeach
</table>
