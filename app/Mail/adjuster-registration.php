<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjuster Registration Confirmation</title>
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
                <p>Thank you for joining <strong>Adjusters On Demand</strong>!</p>
                <p>We’ve received your application and documents. Our verification team is currently reviewing your profile to ensure all licenses and credentials meet our premium standards.</p>
                <p><strong>Here’s what happens next:</strong></p>
                <ol style="margin: 18px 0 18px 24px; padding: 0;">
                    <li>You’ll receive an email once your verification is complete.</li>
                    <li>If additional information is needed, our team will reach out directly.</li>
                    <li>Once approved, your profile will appear in our verified adjuster network, visible to hiring companies.</li>
                </ol>
                <p>You can log in at any time to check your status: <a href="<?php echo esc_url($login_url); ?>" style="background: #2E6668; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Login to Your Dashboard</a></p>
                <p>We appreciate your patience — we take pride in maintaining the most trusted network of seasoned, professional adjusters in the industry.</p>
                <div class="support-info">
                    <p><strong>Need help?</strong> If you have any questions, reply to this email or contact support at <a href="mailto:support@adjustersondemand.com" style="color: #2E6668;">support@adjustersondemand.com</a>.</p>
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
