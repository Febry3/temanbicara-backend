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

<body style="margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f4f4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td colspan="2" align="center">
                <img src="https://qzsrrlobwlisodbasdqi.supabase.co/storage/v1/object/public/asset//admin-bicara.png"
                    alt="Logo Teman Bicara" width="100" style="display: block;" />
            </td>
        </tr>

        <tr>
            <td colspan="2" style="padding: 20px 0;">
                <p style="margin: 0;">Hallo, {{ $name }}</p>
                <p style="margin: 5px 0 0;">Menunggu pembayaran dengan {{ $bank }} Virtual Account sebelum
                    {{ $expired_date }}</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="margin-bottom: 10px; background-color: #E5E5E5; border-radius: 20px; padding: 15px;">
                    <table width="100%">
                        <tr>
                            <td style="font-weight: bold; color: #7D944D; font-size: 16px;">Total Bayar</td>
                            <td style="font-weight: bold; color: red; font-size: 16px;">{{ $amount }}</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px; background-color: #E5E5E5; border-radius: 20px; padding: 15px;">
                    <table width="100%">
                        <tr>
                            <td style="font-weight: bold; color: #7D944D; font-size: 16px;">Virtual Account</td>
                            <td style="font-weight: bold; color: #7D944D; font-size: 16px;">{{ $va_number }}</td>
                        </tr>
                    </table>
                </div>

                <div style="background-color: #E5E5E5; border-radius: 20px; padding: 15px;">
                    <table width="100%">
                        <tr>
                            <td style="font-weight: bold; color: #7D944D; font-size: 16px;">Metode Pembayaran</td>
                            <td style="font-weight: bold; color: #7D944D; font-size: 16px;">{{ $payment_method }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
