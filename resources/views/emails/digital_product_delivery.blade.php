<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengiriman Produk Digital - POS Market Kita</title>
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
            background: linear-gradient(135deg, #059669 0%, #0284c7 100%);
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
        .badge-success {
            display: inline-block;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
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
            padding: 6px 0;
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
            margin: 30px 0 20px;
            padding: 20px;
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.3);
            border-radius: 12px;
        }
        .btn-download {
            display: inline-block;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 15px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
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
            <p>Terima kasih atas pesanan produk digital Anda!</p>
        </div>

        <!-- Body -->
        <div class="content">
            <span class="badge-success">✓ Pembayaran QRIS Berhasil Diverifikasi</span>
            <h2 style="color: #ffffff; margin-top: 0; font-size: 18px;">Halo, {{ $customerName }}!</h2>
            <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6;">
                Pembayaran Anda melalui <strong>QRIS Midtrans</strong> telah berhasil diverifikasi oleh sistem. Produk digital yang Anda beli kini siap diunduh dan digunakan.
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
                    <span class="label">Total Bayar</span>
                    <span class="value" style="color: #34d399;">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span class="label">Metode Pembayaran</span>
                    <span class="value">QRIS (Midtrans)</span>
                </div>
                <div class="row">
                    <span class="label">Waktu Pembayaran</span>
                    <span class="value">{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <!-- Download CTA Box -->
            @if (!empty($downloadUrl))
                <div class="cta-box">
                    <h3 style="color: #ffffff; margin-top: 0; font-size: 16px;">Link Unduhan Asset Digital</h3>
                    <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">
                        Klik tombol di bawah ini untuk langsung mengunduh file produk Anda:
                    </p>
                    <a href="{{ $downloadUrl }}" class="btn-download" target="_blank">
                        Download File Produk Digital
                    </a>
                    <p style="color: #64748b; font-size: 11px; margin-top: 15px;">
                        Atau salin tautan berikut ke browser Anda:<br>
                        <span style="color: #38bdf8; word-break: break-all;">{{ $downloadUrl }}</span>
                    </p>
                </div>
            @else
                <div class="cta-box">
                    <p style="color: #f59e0b; font-size: 13px; margin: 0;">
                        Tautan file sedang dipersiapkan oleh penjual. Anda juga dapat menghubungi penjual melalui email resmi mereka.
                    </p>
                </div>
            @endif

            <p style="color: #94a3b8; font-size: 12px; margin-top: 25px; line-height: 1.5;">
                Jika Anda memiliki pertanyaan mengenai produk ini, silakan balas email ini atau hubungi layanan pelanggan POS Market Kita.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">&copy; {{ date('Y') }} POS Market Kita &bull; Transaksi Digital Aman & Terpercaya</p>
        </div>
    </div>
</body>
</html>
