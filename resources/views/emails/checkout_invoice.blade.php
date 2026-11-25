<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Pembayaran - POS Market Kita</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 30px 15px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            border: 1px solid #334155;
        }
        .header {
            background: linear-gradient(135deg, #0284c7 0%, #6366f1 100%);
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #e0e7ff;
            margin: 6px 0 0;
            font-size: 13px;
        }
        .content {
            padding: 30px;
        }
        .badge-pending {
            display: inline-block;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .invoice-card {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid #1e293b;
        }
        .row:last-child {
            border-bottom: none;
        }
        .label {
            color: #94a3b8;
        }
        .value {
            color: #ffffff;
            font-weight: 600;
        }
        .cta-box {
            text-align: center;
            margin: 25px 0 20px;
            padding: 20px;
            background: rgba(2, 132, 199, 0.1);
            border: 1px solid rgba(2, 132, 199, 0.3);
            border-radius: 12px;
        }
        .btn-pay {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 15px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
        }
        .steps {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 18px 20px;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .steps strong {
            color: #f8fafc;
        }
        .steps ol {
            margin: 8px 0 0;
            padding-left: 20px;
        }
        .footer {
            background: #0f172a;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #334155;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>POS MARKET KITA</h1>
            <p>Pesanan Anda telah berhasil dibuat &bull; Menunggu Pembayaran</p>
        </div>

        <!-- Body -->
        <div class="content">
            <span class="badge-pending">⏳ Menunggu Pembayaran QRIS</span>
            <h2 style="color: #ffffff; margin-top: 0; font-size: 18px;">Halo, {{ $customerName }}!</h2>
            <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6;">
                Terima kasih telah melakukan checkout produk digital di <strong>POS Market</strong>. Berikut rincian tagihan pesanan Anda:
            </p>

            <!-- Invoice Details Card -->
            <div class="invoice-card">
                <div class="row">
                    <span class="label">Nomor Invoice</span>
                    <span class="value" style="font-family: monospace; color: #38bdf8;">{{ $transaction->invoice_number }}</span>
                </div>
                <div class="row">
                    <span class="label">Produk Digital</span>
                    <span class="value">{{ $productName }}</span>
                </div>
                <div class="row">
                    <span class="label">Total Tagihan</span>
                    <span class="value" style="color: #34d399; font-size: 15px;">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span class="label">Metode Pembayaran</span>
                    <span class="value">QRIS (Midtrans)</span>
                </div>
                <div class="row">
                    <span class="label">Waktu Pemesanan</span>
                    <span class="value">{{ $transaction->created_at ? $transaction->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <!-- Pay CTA Box -->
            <div class="cta-box">
                <p style="color: #f8fafc; font-size: 14px; margin: 0 0 15px; font-weight: 600;">
                    Silakan buka halaman invoice di bawah untuk melakukan scan QRIS:
                </p>
                <a href="{{ $paymentUrl }}" class="btn-pay">
                    Buka Halaman Pembayaran QRIS &rarr;
                </a>
                <p style="color: #94a3b8; font-size: 11px; margin: 12px 0 0;">
                    Tautan pembayaran: <a href="{{ $paymentUrl }}" style="color: #38bdf8; word-break: break-all;">{{ $paymentUrl }}</a>
                </p>
            </div>

            <!-- Instructions -->
            <div class="steps">
                <strong>Petunjuk Pembayaran QRIS:</strong>
                <ol>
                    <li>Buka aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay) atau mobile banking (BCA, Mandiri, BRI, BNI).</li>
                    <li>Pilih menu <strong>Bayar / Scan QRIS</strong>.</li>
                    <li>Arahkan kamera ke kode QRIS di halaman pembayaran invoice Anda.</li>
                    <li>Pastikan nominal tagihan sesuai, lalu selesaikan pembayaran.</li>
                    <li>Setelah pembayaran berhasil diverifikasi, sistem akan otomatis mengirimkan email berisi <strong>Magic Link Akses Produk Digital</strong> ke email Anda.</li>
                </ol>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">&copy; {{ date('Y') }} POS Market. Seluruh hak cipta dilindungi.</p>
            <p style="margin: 5px 0 0; font-size: 11px;">Email ini dibuat secara otomatis oleh sistem POS Market saat checkout berhasil dilakukan.</p>
        </div>
    </div>
</body>
</html>
