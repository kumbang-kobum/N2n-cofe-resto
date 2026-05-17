@extends($layout ?? 'layouts.dashboard')

@section('content')
<div class="mx-auto max-w-6xl space-y-4">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Kiosk Absensi</h1>
            <p class="text-sm text-slate-600">Mode tablet untuk absen masuk dan pulang dengan selfie. Sekarang sudah ada verifikasi ringan berbasis perbandingan selfie dengan foto referensi.</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            Pastikan tablet mengizinkan akses kamera dan jalankan <code class="rounded bg-white px-1 py-0.5">php artisan storage:link</code> jika foto tidak tampil.
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_30px_90px_-55px_rgba(15,23,42,0.45)]">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Live Camera</div>
                <h2 class="mt-2 text-lg font-semibold text-slate-900">Ambil selfie absensi</h2>
            </div>
            <div class="p-5">
                <div class="relative overflow-hidden rounded-[24px] bg-slate-950 aspect-[4/3]">
                    <video id="attendance-video" class="h-full w-full object-cover" autoplay playsinline muted></video>
                    <div id="camera-placeholder" class="absolute inset-0 flex items-center justify-center bg-slate-950/75 px-6 text-center text-sm text-slate-200">
                        Kamera akan aktif setelah halaman mendapatkan izin akses kamera.
                    </div>
                </div>

                <canvas id="attendance-canvas" class="hidden"></canvas>
                <div class="mt-4 hidden rounded-2xl border border-slate-200 bg-slate-50 p-3" id="selfie-preview-wrap">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Preview selfie</div>
                    <img id="selfie-preview" alt="Preview selfie" class="mx-auto max-h-60 rounded-2xl object-cover">
                </div>
            </div>
        </section>

        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_30px_90px_-55px_rgba(15,23,42,0.45)]">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Form Absensi</div>
            <h2 class="mt-2 text-lg font-semibold text-slate-900">Masuk / Pulang</h2>
            <form method="POST" action="{{ $storeRoute ?? route('attendance.kiosk.store') }}" class="mt-5 space-y-4" id="attendance-kiosk-form">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Karyawan</label>
                    <select name="employee_id" id="employee-select" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                        <option value="">Pilih karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee['id'] }}"
                                    data-face-reference-url="{{ $employee['face_reference_url'] ?? '' }}">
                                {{ $employee['name'] }}{{ $employee['position'] ? ' - ' . $employee['position'] : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Foto referensi wajah</div>
                        <div id="reference-photo-empty" class="flex min-h-40 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-4 text-center text-sm text-slate-500">
                            Pilih karyawan untuk melihat foto referensi.
                        </div>
                        <img id="reference-photo-preview" alt="Foto referensi wajah" class="hidden min-h-40 w-full rounded-2xl object-cover">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Verifikasi ringan</div>
                        <div id="verification-result" class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">
                            Ambil selfie setelah memilih karyawan. Sistem akan membandingkan selfie dengan foto referensi untuk memberi status awal.
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Jenis Absensi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer rounded-2xl border border-slate-200 px-4 py-4 text-center shadow-sm transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700">
                            <input type="radio" name="action_type" value="CLOCK_IN" class="sr-only" checked>
                            <div class="text-sm font-semibold">Clock In</div>
                            <div class="mt-1 text-xs text-slate-500">Absen masuk</div>
                        </label>
                        <label class="cursor-pointer rounded-2xl border border-slate-200 px-4 py-4 text-center shadow-sm transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                            <input type="radio" name="action_type" value="CLOCK_OUT" class="sr-only">
                            <div class="text-sm font-semibold">Clock Out</div>
                            <div class="mt-1 text-xs text-slate-500">Absen pulang</div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                    <textarea name="note" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Opsional, misalnya kondisi jaringan, review manual, atau catatan petugas."></textarea>
                </div>

                <input type="hidden" name="selfie_image" id="selfie-image-input" required>
                <input type="hidden" name="verification_status" id="verification-status-input" value="PHOTO_ONLY">
                <input type="hidden" name="verification_score" id="verification-score-input">
                <input type="hidden" name="verification_note" id="verification-note-input">

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="capture-selfie-button" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Ambil Selfie</button>
                    <button type="submit" class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Absensi</button>
                </div>
                <p class="text-xs text-slate-500">Selfie wajib diambil sebelum absensi disimpan. Status face verification ringan akan otomatis terisi jika foto referensi tersedia.</p>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const video = document.getElementById('attendance-video');
        const canvas = document.getElementById('attendance-canvas');
        const previewWrap = document.getElementById('selfie-preview-wrap');
        const preview = document.getElementById('selfie-preview');
        const hiddenInput = document.getElementById('selfie-image-input');
        const verificationStatusInput = document.getElementById('verification-status-input');
        const verificationScoreInput = document.getElementById('verification-score-input');
        const verificationNoteInput = document.getElementById('verification-note-input');
        const employeeSelect = document.getElementById('employee-select');
        const referencePhotoPreview = document.getElementById('reference-photo-preview');
        const referencePhotoEmpty = document.getElementById('reference-photo-empty');
        const verificationResult = document.getElementById('verification-result');
        const captureButton = document.getElementById('capture-selfie-button');
        const placeholder = document.getElementById('camera-placeholder');
        const form = document.getElementById('attendance-kiosk-form');
        const comparisonCanvas = document.createElement('canvas');
        const selfieMaxSize = 720;
        const selfieJpegQuality = 0.76;

        const setVerificationState = (status, message, tone = 'slate', score = null) => {
            verificationStatusInput.value = status;
            verificationScoreInput.value = score === null ? '' : Number(score).toFixed(2);
            verificationNoteInput.value = message;
            verificationResult.className = `rounded-2xl border px-4 py-5 text-sm ${
                tone === 'green'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    : tone === 'amber'
                        ? 'border-amber-200 bg-amber-50 text-amber-800'
                        : 'border-slate-200 bg-white text-slate-600'
            }`;
            verificationResult.textContent = message;
        };

        const updateReferencePreview = () => {
            const selected = employeeSelect?.selectedOptions?.[0];
            const url = selected?.dataset?.faceReferenceUrl;

            if (url) {
                referencePhotoPreview.src = url;
                referencePhotoPreview.classList.remove('hidden');
                referencePhotoEmpty.classList.add('hidden');
                setVerificationState('PHOTO_ONLY', 'Foto referensi tersedia. Ambil selfie untuk menjalankan verifikasi ringan.', 'slate');
            } else {
                referencePhotoPreview.src = '';
                referencePhotoPreview.classList.add('hidden');
                referencePhotoEmpty.classList.remove('hidden');
                setVerificationState('PHOTO_ONLY', 'Karyawan ini belum memiliki foto referensi. Absensi tetap bisa disimpan, tetapi statusnya selfie saja.', 'amber');
            }
        };

        const loadImage = (src) => new Promise((resolve, reject) => {
            const image = new Image();
            image.crossOrigin = 'anonymous';
            image.onload = () => resolve(image);
            image.onerror = reject;
            image.src = src;
        });

        const cropSource = async (image) => {
            const width = image.videoWidth || image.naturalWidth || image.width;
            const height = image.videoHeight || image.naturalHeight || image.height;

            let cropWidth = Math.min(width, height);
            let cropHeight = cropWidth;
            let cropX = Math.max(0, (width - cropWidth) / 2);
            let cropY = Math.max(0, (height - cropHeight) / 2);

            if ('FaceDetector' in window) {
                try {
                    const detector = new FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
                    const faces = await detector.detect(image);
                    const face = faces?.[0];
                    if (face?.boundingBox) {
                        const box = face.boundingBox;
                        const padding = Math.max(box.width, box.height) * 0.35;
                        cropX = Math.max(0, box.x - padding);
                        cropY = Math.max(0, box.y - padding);
                        cropWidth = Math.min(width - cropX, box.width + padding * 2);
                        cropHeight = Math.min(height - cropY, box.height + padding * 2);
                    }
                } catch (error) {
                    // Fallback ke center crop jika browser tidak mendukung atau deteksi gagal.
                }
            }

            return { cropX, cropY, cropWidth, cropHeight };
        };

        const getGrayscaleVector = async (image) => {
            const { cropX, cropY, cropWidth, cropHeight } = await cropSource(image);
            const size = 32;
            comparisonCanvas.width = size;
            comparisonCanvas.height = size;
            const ctx = comparisonCanvas.getContext('2d', { willReadFrequently: true });
            ctx.clearRect(0, 0, size, size);
            ctx.drawImage(image, cropX, cropY, cropWidth, cropHeight, 0, 0, size, size);

            const { data } = ctx.getImageData(0, 0, size, size);
            const vector = [];

            for (let index = 0; index < data.length; index += 4) {
                const grayscale = (data[index] * 0.299) + (data[index + 1] * 0.587) + (data[index + 2] * 0.114);
                vector.push(grayscale);
            }

            return vector;
        };

        const compareFacesLightweight = async (selfieDataUrl, referenceUrl) => {
            const [selfieImage, referenceImage] = await Promise.all([
                loadImage(selfieDataUrl),
                loadImage(referenceUrl),
            ]);

            const [selfieVector, referenceVector] = await Promise.all([
                getGrayscaleVector(selfieImage),
                getGrayscaleVector(referenceImage),
            ]);

            let difference = 0;
            for (let index = 0; index < selfieVector.length; index += 1) {
                difference += Math.abs(selfieVector[index] - referenceVector[index]);
            }

            const averageDifference = difference / selfieVector.length;
            const score = Math.max(0, Math.min(100, 100 - ((averageDifference / 255) * 100)));

            if (score >= 82) {
                return {
                    status: 'FACE_VERIFIED',
                    tone: 'green',
                    message: `Verifikasi ringan cocok (${score.toFixed(1)}%). Selfie dan foto referensi cukup mirip.`,
                    score,
                };
            }

            return {
                status: 'REVIEW_REQUIRED',
                tone: 'amber',
                message: `Perlu review (${score.toFixed(1)}%). Selfie tetap tersimpan, tetapi sebaiknya dicek manual oleh admin/manager.`,
                score,
            };
        };

        const startCamera = async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                placeholder.textContent = 'Browser ini belum mendukung akses kamera.';
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false,
                });

                video.srcObject = stream;
                placeholder.classList.add('hidden');
            } catch (error) {
                placeholder.textContent = 'Kamera tidak bisa diakses. Pastikan izin kamera pada tablet sudah diberikan.';
            }
        };

        captureButton?.addEventListener('click', () => {
            if (!video.videoWidth || !video.videoHeight) {
                alert('Kamera belum siap. Tunggu beberapa detik lalu coba lagi.');
                return;
            }

            const scale = Math.min(1, selfieMaxSize / Math.max(video.videoWidth, video.videoHeight));
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', selfieJpegQuality);
            preview.src = dataUrl;
            hiddenInput.value = dataUrl;
            previewWrap.classList.remove('hidden');

            const referenceUrl = employeeSelect?.selectedOptions?.[0]?.dataset?.faceReferenceUrl;
            if (!referenceUrl) {
                setVerificationState('PHOTO_ONLY', 'Selfie berhasil diambil. Foto referensi belum tersedia, jadi status absensi menggunakan selfie saja.', 'amber');
                return;
            }

            setVerificationState('PHOTO_ONLY', 'Selfie berhasil diambil. Sistem sedang membandingkan dengan foto referensi...', 'slate');
            compareFacesLightweight(dataUrl, referenceUrl)
                .then((result) => {
                    setVerificationState(result.status, result.message, result.tone, result.score);
                })
                .catch(() => {
                    setVerificationState('REVIEW_REQUIRED', 'Perbandingan otomatis tidak berhasil dijalankan. Selfie tetap tersimpan dan absensi akan ditandai perlu review.', 'amber');
                });
        });

        form?.addEventListener('submit', (event) => {
            if (!hiddenInput.value) {
                event.preventDefault();
                alert('Silakan ambil selfie terlebih dahulu sebelum menyimpan absensi.');
            }
        });

        employeeSelect?.addEventListener('change', updateReferencePreview);
        updateReferencePreview();
        startCamera();
    })();
</script>
@endpush
