<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Notification</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body style="margin: 0; padding: 20px; font-family: 'Poppins', sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px;">
        <tr>
            <th>
            <th>
        </tr>
        <tr>
            <td align="center">
                <img src="https://qzsrrlobwlisodbasdqi.supabase.co/storage/v1/object/public/asset//admin-bicara.png"
                    alt="Logo Teman Bicara" width="100" style="display: block;" />
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 0;">
                <p style="margin: 0;">Hallo, {{ $customer_name }}</p>
                <p style="margin: 5px 0 0;">Selamat pembayaran konsultasimu dengan id {{ $order_id }} telah
                    berhasil.</p>
            </td>
        </tr>

    </table>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="padding: 0 20px;">
                <table width="100%" cellpadding="7.5" cellspacing="0" border="0"
                    style="background-color: #E5E5E5; border-radius:10px; color: #7D944D;">
                    <tr>
                        <td align="left">Total Bayar</td>
                        <td align="right">{{ $amount }}</td>
                    </tr>
                    <tr>
                        <td align="left">Nama Customer</td>
                        <td align="right">{{ $customer_name }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
