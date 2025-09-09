<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
        }

        .card h2 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        .card p {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>Laporan Stock</h1>
    <p><strong>Tanggal Mulai:</strong> {{ Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d F Y') }}
        &nbsp;
        <strong>Tanggal Selesai:</strong> {{ Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d F Y') }}
    </p>
    <p style="text-transform: capitalize">
        <strong>Kategori:</strong> {{ str_replace('-', ' ', $filters['category']) }} &nbsp;
        <strong>Supplier:</strong> {{ str_replace('-', ' ', $filters['supplier']) }}
    </p>

    <h2>Detail Stock</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Nama Supplier</th>
                <th>Kategori</th>
                <th class="center">Stock</th>
                <th class="center">Stock Minimum</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filteredStocks as $index => $stock)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $stock->name }}</td>
                    <td>{{ $stock->supplier }}</td>
                    <td>{{ $stock->category }}</td>
                    <td class="center">{{ $stock->stock }}</td>
                    <td class="center">{{ $stock->alert_stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
