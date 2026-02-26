<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0056b3;
        }
        p {
            margin-bottom: 15px;
        }
        .activation-code {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Account Activation Code</h1>
        <p>Hi <?php echo htmlspecialchars($first_name ?? 'User'); ?> <?php echo htmlspecialchars($last_name ?? ''); ?>,</p>
        <p>Thank you for registering with <?php echo htmlspecialchars($company_name ?? 'Our Service'); ?>!</p>
        <p>To activate your account, please use the following activation code:</p>
        <p class="activation-code"><?php echo htmlspecialchars($activation_code ?? 'N/A'); ?></p>
        <!-- <p>Alternatively, you can click the link below to activate your account directly:</p>
        <p><a href="<?php echo htmlspecialchars($activation_link ?? '#'); ?>" class="button">Activate Your Account</a></p> -->
        <p>This code is valid for a limited time (usually 24 hours). If you did not request this, please ignore this email.</p>
        <div class="footer">
            <p>Best regards,<br><?php echo htmlspecialchars($company_name ?? 'Our Service'); ?> Team</p>
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company_name ?? 'Our Service'); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>