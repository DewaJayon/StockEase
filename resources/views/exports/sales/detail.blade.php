<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Penjualan {{ $sale->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
            position: relative;
        }

        .logo {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 120px;
            border-radius: 50%;
        }

        .header,
        .footer {
            text-align: left;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            margin-bottom: 15px;
        }

        .info-box {
            width: 48%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .total-box {
            margin-top: 15px;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .label {
            font-weight: bold;
        }

        .value {
            margin-bottom: 4px;
        }

        .capitalize {
            text-transform: capitalize;
        }
    </style>
</head>

<body>
    <img src="{{ public_path('img/StockEase-Logo.png') }}" alt="StockEase" class="logo">

    <div class="header">
        <h2>Detail Penjualan</h2>
    </div>

    <div class="info-grid">

        <div class="info-box">
            <div class="value">
                <span class="label">Nama Kasir:</span>
                {{ $sale->user->name }}
            </div>
            <div class="value">
                <span class="label">Tanggal Penjualan:</span>
                {{ $sale->updated_at->format('d M Y, H:i') }}
            </div>
            <div class="value">
                <span class="label">Sale ID:</span>
                #{{ $sale->id }}
            </div>
        </div>

        <div class="info-box">
            <div class="value">
                <span class="label">Nama Pelanggan:</span>
                {{ $sale->customer_name ?? '-' }}
            </div>
            <div class="value capitalize">
                <span class="label">Metode Pembayaran:</span>
                {{ $sale->payment_method }}
            </div>
            <div class="value capitalize">
                <span class="label">Status Pembayaran:</span>
                <span class="status-badge">{{ $sale->status }}</span>
            </div>

            @if ($sale->paymentTransaction)
                <div class="value"><span class="label">Status Midtrans:</span>
                    <span class="status-badge capitalize">{{ $sale->paymentTransaction->status }}</span>
                </div>
                <div class="value">
                    <span class="label">Order ID Midtrans:</span>
                    {{ $sale->paymentTransaction->external_id }}
                </div>
            @endif

        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Produk</th>
                <th>Quantity</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->saleItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->price) }}</td>
                    <td>Rp {{ number_format($item->price * $item->qty) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <div class="value">Uang Diterima: Rp {{ number_format($sale->paid) }}</div>
        <div class="value">Kembalian: Rp {{ number_format($sale->change) }}</div>
        <div class="value" style="font-weight: bold; font-size: 14px;">Total : Rp {{ number_format($sale->total) }}
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja di StockEase</p>
    </div>

</body>

</html>
