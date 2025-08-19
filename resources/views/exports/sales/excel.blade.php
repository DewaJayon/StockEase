<table>
    {{-- Header Laporan --}}
    <tr>
        <th colspan="8">Laporan Penjualan</th>
    </tr>
    <tr>
        <td>Periode</td>
        <td>{{ $filters['start_date'] }} - {{ $filters['end_date'] }}</td>
    </tr>
    <tr>
        <td>Kasir</td>
        <td>{{ $filters['cashier'] ?? 'Semua' }}</td>
    </tr>
    <tr>
        <td>Metode Pembayaran</td>
        <td>{{ $filters['payment'] ?? 'Semua' }}</td>
    </tr>

    <tr>
        <td colspan="8"></td>
    </tr>

    {{-- Ringkasan --}}
    <tr>
        <td>Total Penjualan</td>
        <td>{{ $summary['total_sales'] }}</td>
    </tr>
    <tr>
        <td>Jumlah Transaksi</td>
        <td>{{ $summary['transaction_count'] }}</td>
    </tr>
    <tr>
        <td>Produk Terjual</td>
        <td>{{ $summary['product_count'] }}</td>
    </tr>
    <tr>
        <td>Produk Terlaris</td>
        <td>{{ $summary['best_product'] }}</td>
    </tr>

    <tr>
        <td colspan="8"></td>
    </tr>

    {{-- Detail Transaksi --}}
    <tr>
        <th>Tanggal</th>
        <th>Invoice</th>
        <th>Kasir</th>
        <th>Customer</th>
        <th>Produk</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
        <th>Metode Pembayaran</th>
    </tr>

    @foreach ($sales as $sale)
        @foreach ($sale->saleItems as $item)
            <tr>
                <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->user->name }}</td>
                <td>{{ $sale->customer_name ?? '-' }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ number_format($item->price) }}</td>
                <td>{{ number_format($item->price * $item->qty) }}</td>
                <td>{{ $sale->payment_method }}</td>
            </tr>
        @endforeach
    @endforeach
</table>
