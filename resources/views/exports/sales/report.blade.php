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
    <h1>Laporan Penjualan</h1>
    <p><strong>Tanggal Mulai:</strong> {{ $start_date }} &nbsp;
        <strong>Tanggal Selesai:</strong> {{ $end_date }}
    </p>
    <p><strong>Kasir:</strong> {{ $cashier_name }} &nbsp;
        <strong>Metode Pembayaran:</strong> {{ $payment }}
    </p>

    <div class="grid">
        <div class="card">
            <h2>Total Penjualan</h2>
            <p>Rp {{ number_format($total_sales, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <h2>Jumlah Transaksi</h2>
            <p>{{ $transaction_count }}</p>
        </div>
        <div class="card">
            <h2>Produk Terjual</h2>
            <p>{{ $product_sold }}</p>
        </div>
        <div class="card">
            <h2>Produk Terlaris</h2>
            <p>{{ $best_selling_product }}</p>
        </div>
    </div>

    <h2>Detail Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $index => $sale)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $sale->date }}</td>
                    <td>{{ $sale->product_name }}</td>
                    <td class="center">{{ $sale->quantity }}</td>
                    <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
