<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance QR Code</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            color: #173122;
            background:
                radial-gradient(circle at top, rgba(39, 109, 73, 0.18), transparent 28%),
                linear-gradient(180deg, #f6f7f3 0%, #eef2eb 100%);
        }

        .qr-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px 16px;
        }

        .qr-card {
            width: min(100%, 540px);
            background: #ffffff;
            border: 1px solid rgba(23, 49, 34, 0.08);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(16, 28, 23, 0.12);
            overflow: hidden;
        }

        .qr-card__header {
            padding: 24px 24px 18px;
            background: linear-gradient(135deg, #11311f 0%, #1f5a39 65%, #26729b 100%);
            color: #f6f1e8;
        }

        .qr-card__eyebrow {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.82;
        }

        .qr-card__title {
            margin: 0;
            font-size: 28px;
            line-height: 1.15;
        }

        .qr-card__body {
            display: grid;
            gap: 18px;
            padding: 24px;
        }

        .qr-card__text {
            margin: 0;
            line-height: 1.7;
            color: #4c6055;
        }

        .qr-panel {
            display: grid;
            justify-items: center;
            gap: 14px;
            padding: 20px;
            border-radius: 20px;
            background: #f8faf8;
            border: 1px solid rgba(23, 49, 34, 0.08);
        }

        .qr-panel img {
            width: min(100%, 320px);
            height: auto;
            border-radius: 18px;
            background: #fff;
            border: 1px solid rgba(23, 49, 34, 0.08);
            padding: 12px;
        }

        .qr-meta {
            display: grid;
            gap: 10px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(38, 114, 155, 0.06);
            border: 1px solid rgba(38, 114, 155, 0.12);
        }

        .qr-meta p {
            margin: 0;
            line-height: 1.65;
        }

        .qr-actions {
            display: grid;
            gap: 12px;
        }

        .qr-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 14px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.16s ease, opacity 0.16s ease;
        }

        .qr-action:hover {
            transform: translateY(-1px);
        }

        .qr-action--primary {
            background: #156c45;
            color: #f6f1e8;
        }

        .qr-action--secondary {
            background: #ffffff;
            color: #173122;
            border: 1px solid rgba(23, 49, 34, 0.14);
        }

        .qr-note {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
            color: #617469;
        }

        @media (min-width: 768px) {
            .qr-card__header,
            .qr-card__body {
                padding: 28px;
            }

            .qr-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
    <main class="qr-page">
        <section class="qr-card">
            <header class="qr-card__header">
                <p class="qr-card__eyebrow">CPSU Hinoba-an SSG</p>
                <h1 class="qr-card__title">Attendance QR Code</h1>
            </header>

            <div class="qr-card__body">
                <p class="qr-card__text">
                    This QR code is linked to <strong>{{ $eventQr->user?->name ?? 'the selected student' }}</strong>
                    for <strong>{{ $eventQr->event?->event_title ?? 'the selected event' }}</strong>.
                    Open it here, or download the image to your phone before check-in.
                </p>

                <div class="qr-panel">
                    <img src="{{ $imageUrl }}" alt="Attendance QR Code">
                </div>

                <div class="qr-meta">
                    <p><strong>Student:</strong> {{ $eventQr->user?->name ?? 'Unknown' }}</p>
                    <p><strong>Event:</strong> {{ $eventQr->event?->event_title ?? 'Unknown' }}</p>
                    <p><strong>Expires:</strong> {{ optional($eventQr->expires_at)->format('F j, Y h:i A') ?? 'N/A' }}</p>
                </div>

                <div class="qr-actions">
                    <a class="qr-action qr-action--primary" href="{{ $downloadUrl }}">Download QR Image</a>
                </div>

                <p class="qr-note">
                    If the image does not appear inside Gmail, tap <strong>Download QR Image</strong> first. Show the saved image to the Admin or SSG Officer during attendance scanning.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
