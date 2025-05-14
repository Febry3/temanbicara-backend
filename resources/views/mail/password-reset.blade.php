<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <title>OTP Email</title>
</head>

<body style="margin: 0; padding: 0; font-family: Poppins, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <th>
            <th>
        </tr>
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="600px" cellspacing="0" cellpadding="0"
                    border="0" sty le="background-color: #ffffff; border-radius: 10px; padding: 20px;">
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <img src="https://qzsrrlobwlisodbasdqi.supabase.co/storage/v1/object/public/asset//admin-bicara.png"
                                alt="Logo Teman Bicara" width="100" style="display: block;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 18px; font-weight: bold; color: #333;">
                            Your OTP
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 10px; font-size: 16px; color: #555;">
                            Hello {{ $name }}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 15px; font-size: 14px; color: #555;">
                            Use the following OTP to complete the procedure to change your password. OTP is valid for 5
                            minutes. Do not share this code with others.
                        </td>
                    </tr>
                    <tr>
                        <td align="center"
                            style="padding: 20px; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #7D944D; background-color: #f9f9f9; border-radius: 5px;">
                            {{ $otp }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
