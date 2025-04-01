<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        body {
            font-family: "Poppins";
            padding: 0;
            margin: 0;
        }

        p {
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .inner-container {
            padding: 1.25rem;
            width: 50%;
            border-radius: 2rem;
            background-color: aqua;
            background-image: url('https://img.freepik.com/free-vector/medical-healthcare-blue-color_1017-26807.jpg');
            object-fit: contain;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header p {
            font-size: 1rem;
        }

        .header img {
            object-fit: contain;
        }

        .body {
            margin-top: 2rem;
            border-radius: 2rem;
            padding: 1.75rem;
            background-color: white;
            display: flex;
            align-items: center;
            flex-direction: column;
        }

        .title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .body-text {
            margin-top: 0.5rem;
            text-align: center;
        }

        .otp-code {
            padding: 1.5rem;
            color: #7D944D;
        }

        .otp-code p {
            font-size: 2.75rem;
            font-weight: 600;
            letter-spacing: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="inner-container">
            <div class="header">
                <img src="https://qzsrrlobwlisodbasdqi.supabase.co/storage/v1/object/public/asset//admin-bicara.png"
                    alt="logo teman bicara">

            </div>
            <div class="body">
                <p class="title">Your OTP</p>
                <p> Hello Asep,</p>
                <p class="body-text">Use the following OTP to complete the procedure to change your password. OTP is
                    valid for 5 minutes. Do not share this code with others.</p>
                <div class="otp-code">
                    <p>123456</p>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
