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
    <h1>Laporan Pembelian</h1>
    <p><strong>Tanggal Mulai:</strong> {{ $startDate }} &nbsp;
        <strong>Tanggal Selesai:</strong> {{ $endDate }}
    </p>
    <p style="text-transform: capitalize">
        <strong>User:</strong> {{ str_replace('-', ' ', $user) }} &nbsp;
        <strong>Supplier:</strong> {{ str_replace('-', ' ', $supplier) }}
    </p>

    <div class="grid">
        <div class="card">
            <h2>Total Transaksi Produk</h2>
            <p>Rp {{ number_format($sumTotalPurchase, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <h2>Total Item Dibeli</h2>
            <p>{{ $totalItemsPurchased }}</p>
        </div>
        <div class="card">
            <h2>Total Pembelian</h2>
            <p>{{ $totalTransaction }}</p>
        </div>
    </div>

    <h2>Detail Pembelian</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Produk</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $index => $purchase)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $purchase->date->format('d M Y') }}</td>
                    <td>{{ $purchase->product_name }}</td>
                    <td>Rp. {{ number_format($purchase->product_price, 0, ',', '.') }}</td>
                    <td class="center">{{ $purchase->qty }}</td>
                    <td>Rp. {{ number_format($purchase->total_purchase, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
