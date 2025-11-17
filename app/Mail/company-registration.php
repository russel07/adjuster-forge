<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Registration Confirmation</title>
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
        .steps {
            margin: 20px 0;
            padding-left: 20px;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 8px 0;
            font-weight: 500;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .next-steps {
            font-weight: bold;
            font-size: 16px;
            margin: 25px 0 10px 0;
        }
        .support-info {
            margin: 25px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #2E6668;
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
            <td class="content">
                <p class="greeting">Hello <?php echo esc_html($user_name); ?>,</p>
                <p>Welcome to <strong>Adjusters On Demand</strong>, the premier network connecting insurance companies with verified, deployment-ready adjusters.</p>
                <p>Your company account has been successfully created. You can now:</p>
                <ul style="margin: 18px 0 18px 24px; padding: 0;">
                    <li>Post open deployments or storm assignments</li>
                    <li>Search and filter for verified adjusters</li>
                    <li>Build your team faster with licensed professionals you can trust</li>
                </ul>
                <p>We’re excited to help you find your next great adjuster.</p>
                <p style="margin: 28px 0 18px 0;"><a href="<?php echo esc_url($dashboard_url); ?>" style="background: #2E6668; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Get Started: Login to Your Dashboard</a></p>
                <p>Thank you for joining the platform built to bring confidence, speed, and reliability back to the adjusting industry.</p>
                <div class="support-info">
                    <p><strong>Need help?</strong> If you have any questions, reply to this email or contact support at <a href="mailto:solutions@adjustersondemand.com" style="color: #2E6668;">solutions@adjustersondemand.com</a>.</p>
                </div>
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
