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
                    <p><strong>Need help?</strong> If you have any questions, reply to this email or contact support at <a href="mailto:support@adjustersondemand.com" style="color: #2E6668;">support@adjustersondemand.com</a>.</p>
                </div>
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p>
                   <svg width="90" height="48" viewBox="0 0 90 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <style type="text/css">
                            .st0{fill:#B19C71;}
                            .st1{fill:#E5CFAA;}
                            .st2{fill:#DFD9C7;}
                            .st3{fill:#B5AD99;}
                            .st4{fill:#B8A276;}
                            .st5{font-family:'Quantify-Bold';}
                            .st6{font-size:36.1855px;}
                            .st7{font-size:24.0522px;}
                            .st8{fill:#77623F;}
                            .st9{fill:#C09C5C;}
                            .st10{fill:#4C3C25;}
                        </style>
                        <g>
                            <g>
                                <g>
                                    <path class="st4" d="M325.6,129.2c-1.4,0.1-2.8,0-4.3,0h-6.3l5.3,13.9l0,0c0.1,0.2,0.2,0.4,0.3,0.6c2-4.7,3.7-9.1,5.6-13.5
                                        C326.6,129.4,326.6,129.1,325.6,129.2z"/>
                                    <path class="st4" d="M322.7,118.3l-4.9,5.2l-3.5,3.6c0,0-12.5-32-16.3-41.5c0-0.1-0.1-0.2-0.1-0.3c-0.1,0.1-12.4,31.1-14.6,36.6
                                        c-0.4,0.9-0.4,1.2,0.7,1.2c6.5,0,12.9,0,19.5,0l1.8,5.4l1.6,4.9c-9,0-17.9,0-26.9,0c-1,0-1.5,0.2-1.9,1.2
                                        c-1.5,3.9-3.1,7.8-4.7,11.6c-0.4,1-0.2,1.4,0.6,1.9c0.3,0.2,0.6,0.4,0.9,0.6c5.5,3.4,11.1,6.3,17.2,8.4c0.4,0.2,0.9,0.3,1.7,0.7
                                        c-0.7,0-1,0.1-1.3,0c-11.7-3.3-22.6-8-32.4-15.3c-4-2.9-6.8-6.7-7.6-11.7c-1.2-7.1,1.8-17.8,9.9-19.4c1.2-0.2,2.4-0.1,3.6,0.3
                                        c0.7,0.2,1.4,0.5,2,0.8c0.3,0.2,1,0.8,1.5,1c0,0,0,0,0,0c0.1,0.1,0.2,0.1,0.3,0.1c-7.1-0.1-11.6,5.5-12.3,11.6
                                        c-0.5,4.4,0.4,8.4,3,12c1.1,1.5,2.2,2.9,3.4,4.6c2.3-5.6,4.4-10.9,6.6-16.2c0.9-2.1,1.7-4.2,2.6-6.3c0.3-0.8,0.6-1.5,1.2-2.1
                                        c1.8-1.9,2.9-4.1,3.3-6.2c1-5.4-2.4-10.5-9.5-12.2c-4.5-1.1-8.8,0-12.8,2c-0.3,0.1-0.6,0.3-0.9,0.5c-7.9,4.2-12.8,10.9-14.6,19.7
                                        c-2,9.7,1.1,18,7.2,25.4c2.2,2.7,4.9,5,7.7,7.2c-1.6-0.5-3-1.5-4.3-2.4c-6.3-4.5-11.7-9.6-14.4-17.1c-3-8.3-2.5-16.5,1.4-24.3
                                        c0.1-0.2,0.2-0.4,0.3-0.6c5.1-9.7,13.4-15.2,24.3-16.5c7.3-0.8,14.5,2.3,17,8.8c0.1,0.2,0.1,0.3,0.3,0.7c1.6-3.2,2.7-6.2,3.9-9.1
                                        c2.7-6.8,5.4-13.5,8.1-20.3c0.4-0.9,0.7-1.3,1.7-1.2c3.4,0.1,6.8,0.1,10.1,0c0.9,0,1.3,0.3,1.6,1.1
                                        C308.3,81.3,322.7,118.3,322.7,118.3z"/>
                                </g>
                                <g>
                                    <path class="st2" d="M370.4,71.9c0-1.4-0.6-1.9-1.9-2c-2.5-0.3-4.9-0.6-7.4-1c-8.2-1.3-16.4-2.8-24.6-4.7
                                        c-12.9-3-25.4-7.1-37.6-12c-0.8-0.3-1.6-0.4-2.5-0.1c-4.9,1.9-9.8,3.8-14.8,5.4c-16.3,5.5-33,9.3-50.1,11.7
                                        c-1.7,0.2-3.4,0.8-5,0.8c-1.6,0-1.8,0.4-1.8,1.7c0,9.3,0,18.6,0,27.9c0,5.6,0,11.1,0,16.7c0,5.2-0.3,10.5,0.4,15.7
                                        c1.1,7.8,3.4,15.2,6.8,22.3c5.4,11.3,13.3,20.8,22.7,28.9c12.6,10.9,26.8,19.3,42.1,25.9c0.7,0.3,1.2,0.3,1.8,0
                                        c6.2-2.9,12.4-5.9,18.3-9.4c13.8-8.2,26.6-17.6,36.7-30.3c10.1-12.7,16.1-27.1,16.5-43.3C370.7,108.1,370.4,90,370.4,71.9z
                                        M364.1,121.4c0,2.5-0.4,5-0.5,7.4c-0.2,4.4-1,8.7-2.2,12.9c-3.8,13-11.1,23.7-20.9,32.9c-12.4,11.6-26.6,20.4-42,27.5
                                        c-0.6,0.3-1,0.4-1.7,0.1c-12-5.8-23.5-12.3-34-20.6c-10.8-8.6-19.9-18.5-25.7-31.2c-2.8-6-4.6-12.3-5.5-18.8
                                        c-0.7-5.2-0.5-10.5-0.5-15.8c0-4.7,0-9.4,0-14.1c0-8.2,0-16.5,0-24.7c0-0.9-0.1-1.5,1.2-1.6c6.7-0.6,13.2-2,19.7-3.4
                                        c15.3-3.2,30.3-7.8,44.9-13.3c0.6-0.2,1-0.2,1.5,0c13.8,5.5,28,9.6,42.5,12.8c6.9,1.5,13.9,2.9,20.9,3.9c1.6,0.2,2.3,0.7,2.3,2.6
                                        C364,92.5,364.1,107,364.1,121.4z"/>
                                    <path class="st8" d="M336.9,119.5h11.9c-10.9,14.3-21.5,28.2-32.2,42.2c-0.1,0-0.2-0.1-0.2-0.1c1.3-3.5,2.7-7,4-10.4
                                        c3-7.7,6.1-15.4,9.2-23.1c0.5-1.3,0.4-1.7-1.1-1.7c-3.5,0.1-7.1,0-11.1,0c12.4-13.3,24.6-26.4,36.8-39.4c0.1,0.1,0.1,0.1,0.1,0.1
                                        C348.6,97.9,342.8,108.6,336.9,119.5z"/>
                                    <path class="st4" d="M325.3,154.5c-0.4,0.5-0.4,1-0.2,1.7c0.8,1.8,1.5,3.7,2.2,5.6c0.2,0.5,0.3,0.9,0.9,0.9c3.6,0,7.2,0,11.1,0
                                        c-2.4-5.8-4.8-11.4-7.2-17.2C329.7,148.6,327.4,151.5,325.3,154.5z"/>
                                    <path class="st9" d="M295.5,166.9c-2.6-1.4-5.1-2.5-7.7-3.1c-4.7-1.1-9.4-1.9-14.2-1.7c-7.3,0.3-13.9-1.5-20-5.3
                                        c-0.2-0.1-0.4-0.2-0.6-0.2c0.3,0,0.7,0,1,0.1c4.7,0.3,9.3,0.6,13.9,0.3c10.5-0.6,19.4,3.1,27.4,9.4
                                        C295.3,166.4,295.4,166.5,295.5,166.9z"/>
                                    <path class="st4" d="M266.8,106.6c-6.1-0.3-10.2,2.8-13.7,7.1c-3.4,4.3-5,9.2-4.5,14.8c-1.7-4.6-2.2-9.2-0.4-14
                                        c1.9-5,5.3-8,10.3-9.1C261.5,104.7,264.6,105.2,266.8,106.6C266.8,106.6,266.8,106.6,266.8,106.6z"/>
                                </g>
                                <path class="st3" d="M369.6,89.4c0-5.7-0.1-11.7-0.1-17.5c0-0.9-0.2-1.1-1.1-1.2c-2.3-0.3-4.7-0.6-7.4-1
                                    c-8.6-1.3-16.9-2.9-24.6-4.7c-12.1-2.8-24.5-6.7-37.7-12.1c-0.4-0.1-0.7-0.2-0.9-0.2v5.1c0.3,0,0.7,0.1,1.1,0.2
                                    c13,5.1,26.8,9.3,42.4,12.8c8.4,1.9,14.8,3.1,20.8,3.8c1.8,0.2,3.1,0.9,3,3.5c-0.1,10.7-0.1,21.7-0.1,32.2c0,3.7,0,7.4,0,11.1
                                    c0,1.5-0.1,3-0.3,4.4c-0.1,1-0.2,2-0.2,3c-0.2,4.4-1,8.8-2.2,13.1c-3.7,12.5-10.6,23.4-21.1,33.3c-11.5,10.8-25.3,19.9-42.2,27.7
                                    c-0.3,0.2-0.7,0.3-1.1,0.3v5.1c0,0,0.1,0,0.1,0c0.1,0,0.3,0,0.5-0.1c6-2.8,12.3-5.8,18.2-9.3c16.5-9.8,27.7-19,36.5-30
                                    c10.4-13.1,15.9-27.5,16.4-42.8C369.7,113.9,369.7,101.4,369.6,89.4z"/>
                                <path class="st4" d="M336.9,119.5h11.9c-10.9,14.3-21.5,28.2-32.2,42.2l22.7-38.6h-12.1l27.1-36
                                    C348.6,97.9,342.8,108.6,336.9,119.5z"/>
                                <path class="st8" d="M277.7,110.9c1-5.3-2.4-10.5-9.5-12.2c-4.5-1.1-8.8,0-12.8,2c-8.4,4.2-13.6,11-15.5,20.2
                                    c-2,9.7,1.1,18,7.2,25.4c2.2,2.7,4.9,5,7.7,7.2c-0.6-0.3-19.8-10.2-17.6-32.8c0,0,1.8-24.4,30.5-25
                                    C267.8,95.7,280.7,98,277.7,110.9z"/>
                                <path class="st8" d="M266.8,106.6c-6.1-0.3-10.2,2.8-13.7,7.1c-3.4,4.3-5,9.2-4.5,14.8c-1.3-9.4,1-13.4,1-13.4
                                    C254.6,103,266.2,106.4,266.8,106.6C266.8,106.6,266.8,106.6,266.8,106.6z"/>
                                <path class="st8" d="M293.8,157.8c-0.7,0-1,0.1-1.3,0c-11.7-3.3-22.6-8-32.4-15.3c-4-2.9-6.8-6.7-7.6-11.7
                                    c-1.2-7.1,1.8-17.8,9.9-19.4c1.2-0.2,2.4-0.1,3.6,0.3c0.7,0.2,1.4,0.5,2,0.8c0.3,0.2,1,0.8,1.5,1c-1.7-0.5-10.4-2.3-14,7.1
                                    c-2.3,6-1,12.8,3.2,17.7C263.9,144.3,274.1,152.5,293.8,157.8z"/>
                                <path class="st8" d="M295.5,166.9c-2.6-1.4-5.1-2.5-7.7-3.1c-4.7-1.1-9.4-1.9-14.2-1.7c-7.3,0.3-13.9-1.5-20-5.3
                                    c-0.2-0.1-0.4-0.2-0.6-0.2c0.3,0,0.7,0,1,0.1c3.1,1,8.6,2.1,18.1,2.5C272.1,159.1,289.4,160.3,295.5,166.9z"/>
                                <path class="st8" d="M317.8,123.5l-3.5,3.6c0,0-12.6-32.2-16.4-41.8l2-5.7L317.8,123.5z"/>
                                <path class="st8" d="M306.9,133.4c-9,0-17.9,0-26.9,0c-1,0-1.5,0.2-1.9,1.2c-1.5,3.9-3.1,7.8-4.7,11.6c-0.4,1-0.2,1.4,0.6,1.9
                                    c0.3,0.2,0.6,0.4,0.9,0.6l-6-3.6l5.1-14c0.6-1.6,2.1-2.6,3.8-2.6h27.4L306.9,133.4z"/>
                            </g>
                        </g>
                    </svg>
                </p>
                <p class="signature">Warm regards,<br>The Adjusters On Demand Team<br><span style="font-size: 15px; color: #2E6668; font-weight: bold;">Verified. Vetted. On Demand.</span></p>
            </td>
        </tr>
    </table>
</body>
</html>
