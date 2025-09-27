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
            <td class="header">
                Welcome to I Will Drive 4 U
            </td>
        </tr>
        <tr>
            <td class="content">
                <p class="greeting">Hi <?php echo esc_html($user_name); ?>,</p>
                
                <p>Thank you for registering your company with I Will Drive 4 U.</p>
                
                <p class="next-steps">What's next?</p>
                <div class="steps">
                    <ol>
                        <li>Log in to your account to choose a membership plan</li>
                        <li>Once your plan is activated, you’ll gain immediate access to our network of verified premium adjusters</li>
                        <li>You can search adjusters, view profiles, and start messaging qualified candidates</li>
                    </ol>
                </div>
                
                <p>Once your documents are submitted and payment is received, our verification team will review your application.</p>
                
                <div class="support-info">
                    <p><strong>Need help?</strong> If you have any questions, reply to this email or contact support at <a href="mailto:support@iwilldrive4u.com" style="color: #2E6668;">support@iwilldrive4u.com</a>.</p>
                </div>
                
                <p>We look forward to helping you get hired by serious companies.</p>
                
                <p class="signature">— The I Will Drive 4 U Team</p>
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p><svg width="90" height="48" viewBox="0 0 90 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M65.4677 47.9999C59.9152 48.0001 54.5344 46.0751 50.2421 42.5527C45.9498 39.0304 43.0116 34.1288 41.9282 28.683C40.8447 23.2372 41.6831 17.5842 44.3003 12.6872C46.9176 7.7902 51.1519 3.95222 56.2817 1.82719C61.4115 -0.297837 67.1194 -0.578427 72.4329 1.03322C77.7464 2.64487 82.3367 6.04904 85.4216 10.6657C88.5066 15.2824 89.8953 20.8259 89.3512 26.3517C88.8072 31.8775 86.3639 37.0437 82.4378 40.97C80.2144 43.2056 77.5698 44.9781 74.6569 46.1847C71.744 47.3914 68.6207 48.0084 65.4677 47.9999ZM65.4677 6.06315C55.5772 6.06315 47.5309 14.1095 47.5309 24C47.5309 33.8905 55.5772 41.9368 65.4677 41.9368C75.3582 41.9368 83.4045 33.8896 83.4045 24C83.4045 14.1103 75.3574 6.06315 65.4677 6.06315Z" fill="#66AFB5"/>
<path d="M39.1041 14.3609C39.1042 12.8798 38.9347 11.4035 38.5988 9.96094H31.5706C32.0988 11.3677 32.3687 12.8583 32.3673 14.3609C32.3673 21.2797 26.7386 26.9083 19.8199 26.9083C12.9012 26.9083 7.27256 21.2797 7.27256 14.3609C7.27143 12.8582 7.54158 11.3677 8.07003 9.96094H1.03847C0.703007 11.4036 0.533487 12.8798 0.533203 14.3609C0.534027 18.8915 2.1292 23.2773 5.0392 26.7497C7.94919 30.2221 11.9884 32.5597 16.449 33.3529V47.1777H23.1858V33.3529C27.6464 32.5597 31.6856 30.2221 34.5956 26.7497C37.5056 23.2773 39.1007 18.8915 39.1016 14.3609H39.1041Z" fill="#66AFB5"/>
</svg></p>
                <p>I Will Drive 4 U<br><a href="mailto:support@iwilldrive4u.com" style="color: #ffffff;">support@iwilldrive4u.com</a></p>
           </td>
        </tr>
    </table>
</body>
</html>
