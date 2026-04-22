@extends('layouts.app')

@section('title', 'QR Scanner')
@section('page_title', 'QR Code Scanner')
@section('page_subtitle', 'Scan event QR codes to mark attendance.')

@section('content')
    <div class="card">
        <div id="reader" style="width: min(100%, 420px); margin: 0 auto;"></div>
        <div id="result" style="margin-top: var(--sp-lg);"></div>
        <button id="start-scan" class="button">Start Scanning</button>
    </div>
@endsection

@push('page-js')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let scanner = null;
    const resultDiv = document.getElementById('result');
    const startButton = document.getElementById('start-scan');
    const readerId = 'reader';
    let scanning = false;

    const stopScanner = async () => {
        if (!scanner || !scanning) {
            return;
        }

        await scanner.stop();
        await scanner.clear();
        scanning = false;
        startButton.textContent = 'Start Scanning';
    };

    startButton.addEventListener('click', async function() {
        if (scanning) {
            await stopScanner();
            scanner = null;
            resultDiv.innerHTML = '';
            return;
        }

        scanner = new Html5Qrcode(readerId);

        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: 220 },
                async function(decodedText) {
                    resultDiv.innerHTML = '<p>Scanned token: <code>' + decodedText + '</code></p>';
                    await stopScanner();

                    fetch('/student/qr-scan/' + encodeURIComponent(decodedText), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        resultDiv.innerHTML += '<p style="color:' + (data.success ? 'green' : 'red') + ';">' + data.message + '</p>';
                    })
                    .catch(() => {
                        resultDiv.innerHTML += '<p style="color: red;">Error processing scan.</p>';
                    });
                }
            );

            scanning = true;
            startButton.textContent = 'Stop Scanning';
        } catch (error) {
            startButton.textContent = 'Start Scanning';
            resultDiv.innerHTML = '<p style="color: red;">Camera access is unavailable on this device or browser.</p>';
        }
    });
});
</script>
@endpush
