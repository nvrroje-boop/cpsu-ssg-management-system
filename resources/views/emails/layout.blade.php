@php use App\Support\AppUrl; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('mail.from.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .email-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .email-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .email-body {
            padding: 30px 20px;
        }

        .greeting {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .content {
            margin-bottom: 25px;
            line-height: 1.8;
        }

        .content p {
            margin-bottom: 15px;
            color: #555;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
        }

        .highlight {
            background-color: #ecf0f1;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin: 15px 0;
            border-radius: 4px;
        }

        .btn-container {
            text-align: center;
            margin: 25px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }

        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #27ae60;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .warning-box {
            background-color: #fff5e6;
            border-left: 4px solid #e67e22;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ecf0f1;
            font-size: 12px;
            color: #7f8c8d;
        }

        .footer-divider {
            height: 1px;
            background-color: #ecf0f1;
            margin: 15px 0;
        }

        .footer-links {
            margin: 10px 0;
        }

        .footer-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
            font-size: 12px;
        }

        .school-info {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
            }

            .email-header {
                padding: 20px 15px;
            }

            .email-header h1 {
                font-size: 24px;
            }

            .email-body {
                padding: 20px 15px;
            }

            .section-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ config('mail.from.name') }}</h1>
            <p>Student Services and Governance</p>
        </div>

        <div class="email-body">
            @yield('content')
        </div>

        <div class="email-footer">
            <div class="footer-divider"></div>
            <div class="school-info">CPSU Hinoba-an</div>
            <p style="margin: 5px 0; color: #95a5a6;">
                This is an automated message from the SSG Management System.
            </p>
            <div class="footer-links">
                <a href="{{ AppUrl::path('/') }}">Portal</a>
                <a href="mailto:cpsuhinobaan.ssg.office@gmail.com">Contact</a>
            </div>
            <p style="margin-top: 15px; font-size: 11px; color: #bdc3c7;">
                &copy; {{ date('Y') }} CPSU Hinoba-an SSG. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
