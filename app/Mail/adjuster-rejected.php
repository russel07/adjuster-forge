<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update on Your Verification Status</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #2E6668, #87CFC3);
            color: white;
            padding: 40px 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }
        .content {
            padding: 30px 20px;
            text-align: left;
        }
        .footer {
            background-color: #2E6668;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 12px;
        }
        .reason-list {
            margin: 20px 0;
            padding-left: 18px;
        }
        .signature {
            margin-top: 30px;
            font-weight: 500;
        }
        @media only screen and (max-width: 600px) {
            table {
                width: 100% !important;
            }
            .content {
                padding: 20px 15px !important;
            }
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="header">
                Update on Your Verification Status
            </td>
        </tr>
        <tr>
            <td class="content">
                <p class="greeting">Hello <?php echo esc_html($user_name); ?>,</p>
                <p>Thank you for applying to join <strong>Adjusters On Demand</strong>.</p>
                <p>After reviewing your application, we’re unable to approve it at this time. Our platform is designed exclusively for seasoned, verified adjusters who meet our current credentialing and experience requirements.</p>
                <p>If your status or experience changes in the future, we encourage you to reapply. You can review our qualification guidelines here: <a href="<?php echo esc_url($requirements_url); ?>" style="color: #2E6668; font-weight: bold;">Requirements Link</a></p>
                <p>We appreciate your interest in being part of our community and wish you continued success in your adjusting career.</p>
                <p class="signature">Warm regards,<br>The Adjusters On Demand Team</p>
            </td>
        </tr>        
        <tr>
            <td class="footer">
                <p class="signature">Sincerely,<br>The Adjusters On Demand Team<br><span style="font-size: 15px; color: #2E6668; font-weight: bold;">Verified. Vetted. On Demand.</span></p>
            </td>
        </tr>
    </table>
</body>
</html>
