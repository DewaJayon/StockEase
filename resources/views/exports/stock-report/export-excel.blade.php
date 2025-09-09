<table>
    {{-- Header Laporan --}}
    <tr>
        <th colspan="8">Laporan Stock</th>
    </tr>
    <tr>
        <td>Periode:</td>
        <td>
            {{ Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d F Y') }} -
            {{ Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d F Y') }}
        </td>
    </tr>
    <tr>
        <td>Kategori:</td>
        <td>{{ ucwords(str_replace('-', ' ', strtolower($filters['category']))) }}</td>
    </tr>
    <tr>
        <td>Supplier:</td>
        <td>{{ ucwords(str_replace('-', ' ', strtolower($filters['supplier']))) }}</td>
    </tr>

    <tr>
        <td colspan="8"></td>
    </tr>

    <tr>
        <td colspan="8"></td>
    </tr>

    {{-- Detail Transaksi --}}
    <tr>
        <th>No</th>
        <th>Nama Produk</th>
        <th>Nama Supplier</th>
        <th>Kategori</th>
        <th>Stock</th>
        <th>Stock Minimum</th>
    </tr>

    @foreach ($filteredStocks as $index => $stock)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $stock->name }}</td>
            <td>{{ $stock->supplier }}</td>
            <td>{{ $stock->category }}</td>
            <td>{{ $stock->stock }}</td>
            <td>{{ $stock->alert_stock }}</td>
        </tr>
    @endforeach
</table>
