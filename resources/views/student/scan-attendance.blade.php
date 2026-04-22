@extends('layouts.student')

@section('title', 'Scan Attendance')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Scan Event QR Code</h1>
        <p class="text-gray-600 mb-8">Use your phone camera or the scanner below to mark your attendance</p>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                {{ session('info') }}
            </div>
        @endif

        <!-- Scanner Container -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Live Scanner -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">📷 Live QR Scanner</h2>
                    <div id="reader" style="width: 100%; height: 400px;" class="rounded overflow-hidden"></div>

                    <div id="scannerStatus" class="mt-4 p-4 bg-blue-50 rounded text-blue-700 hidden">
                        <p id="scannerStatusText"></p>
                    </div>

                    <div class="mt-4 text-sm text-gray-600">
                        <p>✓ Point your camera at the QR code</p>
                        <p>✓ Make sure the QR code is clearly visible</p>
                        <p>✓ The scanner will automatically process it</p>
                    </div>
                </div>

                <!-- Manual Entry Fallback -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <h2 class="text-xl font-semibold mb-4">🔗 Manual Entry</h2>
                    <p class="text-gray-600 mb-4">If you received a QR code link via email, paste it here:</p>
                    <form method="GET" action="{{ route('attendance.scan') }}" class="flex gap-2">
                        <input
                            type="text"
                            name="token"
                            placeholder="Paste QR token here..."
                            class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        <button
                            type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                        >
                            Submit
                        </button>
                    </form>
                </div>
            </div>

            <!-- Instructions Sidebar -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">📌 Instructions</h2>

                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-blue-600 mb-1">1️⃣ Receive Email</h3>
                        <p class="text-sm text-gray-600">Check your email for the event QR code</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-blue-600 mb-1">2️⃣ Two Ways to Scan</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>Option A:</strong> Use the phone camera to scan the QR directly</p>
                        <p class="text-sm text-gray-600"><strong>Option B:</strong> Use the scanner on this page</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-blue-600 mb-1">3️⃣ Confirm Attendance</h3>
                        <p class="text-sm text-gray-600">Your attendance will be marked automatically</p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mt-6">
                        <p class="text-sm text-yellow-800"><strong>⏰ Note:</strong> Each QR code is unique and expires 4 hours after creation. Use it only once.</p>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded p-3">
                        <p class="text-sm text-green-800"><strong>✓ Tip:</strong> Arrive early to avoid scanning issues due to high traffic</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner;
    let isScannerActive = false;

    // Initialize QR scanner when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeScanner();
    });

    function initializeScanner() {
        try {
            html5QrcodeScanner = new Html5Qrcode("reader");

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
            };

            html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanError
            );

            isScannerActive = true;
            updateScannerStatus('Scanner ready. Point camera at QR code...', 'ready');
        } catch (err) {
            console.error("Failed to initialize scanner:", err);
            updateScannerStatus('Camera not available. Use manual entry below.', 'error');
        }
    }

    function onScanSuccess(decodedText) {
        // Extract token from URL if it's a full URL
        let token = decodedText;

        if (decodedText.includes('token=')) {
            const params = new URL(decodedText).searchParams;
            token = params.get('token');
        }

        if (token) {
            updateScannerStatus('QR Code detected! Processing...', 'scanning');

            // Stop scanner during submission
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
            }

            // Submit to server
            fetch('{{ route('attendance.scan') }}' + '?token=' + encodeURIComponent(token), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateScannerStatus('✅ ' + data.message, 'success');

                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '{{ route('student.events.index') }}';
                    }, 2000);
                } else {
                    updateScannerStatus('❌ ' + (data.message || 'Scan failed'), 'error');

                    // Restart scanner after 2 seconds
                    setTimeout(() => {
                        if (!isScannerActive) {
                            initializeScanner();
                        }
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                updateScannerStatus('❌ Error: ' + error.message, 'error');

                // Restart scanner
                setTimeout(() => {
                    if (!isScannerActive) {
                        initializeScanner();
                    }
                }, 2000);
            });
        }
    }

    function onScanError(error) {
        // Suppress error logs for continuous scanning
        console.debug("Scan error (this is normal):", error);
    }

    function updateScannerStatus(message, status) {
        const statusDiv = document.getElementById('scannerStatus');
        const statusText = document.getElementById('scannerStatusText');

        statusText.textContent = message;
        statusDiv.classList.remove('hidden');

        // Update styling based on status
        statusDiv.className = 'mt-4 p-4 rounded ' + getStatusClass(status);
    }

    function getStatusClass(status) {
        const baseClasses = 'mt-4 p-4 rounded';

        switch(status) {
            case 'success':
                return baseClasses + ' bg-green-50 text-green-700 border border-green-200';
            case 'error':
                return baseClasses + ' bg-red-50 text-red-700 border border-red-200';
            case 'scanning':
                return baseClasses + ' bg-yellow-50 text-yellow-700 border border-yellow-200';
            case 'ready':
            default:
                return baseClasses + ' bg-blue-50 text-blue-700 border border-blue-200';
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (html5QrcodeScanner && isScannerActive) {
            html5QrcodeScanner.stop();
        }
    });
</script>
@endsection
