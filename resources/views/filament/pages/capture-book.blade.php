<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-6">
        <textarea id="frontImageData" wire:model.live="frontImageData" class="hidden"></textarea>
        <textarea id="backImageData" wire:model.live="backImageData" class="hidden"></textarea>
        <input id="frontImageWidth" wire:model.live="frontImageWidth" type="hidden">
        <input id="frontImageHeight" wire:model.live="frontImageHeight" type="hidden">
        <input id="backImageWidth" wire:model.live="backImageWidth" type="hidden">
        <input id="backImageHeight" wire:model.live="backImageHeight" type="hidden">

        <div wire:ignore class="grid gap-4 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="relative aspect-[4/3] bg-black">
                    <video id="captureVideo" class="h-full w-full object-cover" autoplay playsinline muted></video>
                    <div id="captureStatus" class="absolute left-4 top-4 rounded-full bg-gray-900/80 px-3 py-1 text-sm font-semibold text-white">
                        Camera idle
                    </div>
                    <div id="cameraHelp" class="absolute inset-x-4 top-16 hidden rounded-lg bg-danger-600/95 p-3 text-sm font-medium text-white shadow-lg"></div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <div class="mb-2 flex items-center justify-between text-xs font-medium text-white">
                            <span id="captureSideLabel">Front</span>
                            <span id="stabilityPercent">0%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-white/25">
                            <div id="stabilityBar" class="h-full w-0 rounded-full bg-primary-500 transition-all"></div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 p-4 dark:border-white/10">
                    <button id="manualCaptureButton" type="button" class="w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-950" disabled>
                        Capture Now
                    </button>
                </div>
            </section>

            <aside class="space-y-4">
                <button id="submitCaptureButton" type="submit" class="w-full rounded-lg bg-success-600 px-4 py-3 text-sm font-semibold text-white hover:bg-success-500 disabled:opacity-50" disabled wire:loading.attr="disabled">
                    Submit Book
                </button>

                <div class="flex flex-row gap-3 lg:flex-col lg:gap-4">
                    <div class="w-1/2 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900 lg:w-full sm:p-4">
                        <div class="mb-2 flex items-center justify-between gap-2 sm:mb-3 sm:gap-4">
                            <h2 class="truncate text-[10px] font-semibold text-gray-950 dark:text-white sm:text-base">Front</h2>
                            <button id="retakeFrontButton" type="button" class="shrink-0 text-[10px] font-semibold text-primary-600 disabled:text-gray-400 sm:text-sm">
                                Retake
                            </button>
                        </div>
                        <div class="aspect-[4/3] overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                            <img id="frontPreview" class="hidden h-full w-full object-cover" alt="Front preview">
                            <div id="frontPlaceholder" class="flex h-full items-center justify-center px-1 text-center text-[9px] text-gray-500 sm:px-4 sm:text-sm">
                                Waiting...
                            </div>
                        </div>
                    </div>

                    <div class="w-1/2 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900 lg:w-full sm:p-4">
                        <div class="mb-2 flex items-center justify-between gap-2 sm:mb-3 sm:gap-4">
                            <h2 class="truncate text-[10px] font-semibold text-gray-950 dark:text-white sm:text-base">ISBN</h2>
                            <button id="retakeBackButton" type="button" class="shrink-0 text-[10px] font-semibold text-primary-600 disabled:text-gray-400 sm:text-sm">
                                Retake
                            </button>
                        </div>
                        <div class="aspect-[4/3] overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                            <img id="backPreview" class="hidden h-full w-full object-cover" alt="ISBN preview">
                            <div id="backPlaceholder" class="flex h-full items-center justify-center px-1 text-center text-[9px] text-gray-500 sm:px-4 sm:text-sm">
                                Waiting...
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        @error('frontImageData')
            <p class="text-sm font-medium text-danger-600">{{ $message }}</p>
        @enderror

        @error('backImageData')
            <p class="text-sm font-medium text-danger-600">{{ $message }}</p>
        @enderror
    </form>

    <canvas id="mathCanvas" width="64" height="48" class="hidden"></canvas>
    <canvas id="snapshotCanvas" class="hidden"></canvas>

    <script>
        (() => {
            if (window.kutubioCapturePageInitialized) {
                return;
            }

            window.kutubioCapturePageInitialized = true;

            const video = document.getElementById('captureVideo');
            const mathCanvas = document.getElementById('mathCanvas');
            const mathContext = mathCanvas.getContext('2d', { willReadFrequently: true });
            const snapshotCanvas = document.getElementById('snapshotCanvas');
            const snapshotContext = snapshotCanvas.getContext('2d');

            const statusBadge = document.getElementById('captureStatus');
            const cameraHelp = document.getElementById('cameraHelp');
            const sideLabel = document.getElementById('captureSideLabel');
            const stabilityPercent = document.getElementById('stabilityPercent');
            const stabilityBar = document.getElementById('stabilityBar');

            const manualCaptureButton = document.getElementById('manualCaptureButton');
            const submitCaptureButton = document.getElementById('submitCaptureButton');
            const retakeFrontButton = document.getElementById('retakeFrontButton');
            const retakeBackButton = document.getElementById('retakeBackButton');

            const fields = {
                front: {
                    data: document.getElementById('frontImageData'),
                    width: document.getElementById('frontImageWidth'),
                    height: document.getElementById('frontImageHeight'),
                    preview: document.getElementById('frontPreview'),
                    placeholder: document.getElementById('frontPlaceholder'),
                    label: 'Front',
                },
                back: {
                    data: document.getElementById('backImageData'),
                    width: document.getElementById('backImageWidth'),
                    height: document.getElementById('backImageHeight'),
                    preview: document.getElementById('backPreview'),
                    placeholder: document.getElementById('backPlaceholder'),
                    label: 'ISBN',
                },
            };

            let previousPixels = [];
            let state = 'IDLE';
            let stableFrames = 0;
            let loopId = null;
            let stream = null;
            let activeSide = 'front';

            const motionThresholdHigh = 15;
            const motionThresholdLow = 4;
            const framesToStabilize = 10;

            const setStatus = (text, colorClass = 'bg-gray-900/80') => {
                statusBadge.className = `absolute left-4 top-4 rounded-full px-3 py-1 text-sm font-semibold text-white ${colorClass}`;
                statusBadge.textContent = text;
            };

            const setCameraHelp = (text = '') => {
                cameraHelp.textContent = text;
                cameraHelp.classList.toggle('hidden', text.length === 0);
            };

            const setFieldValue = (element, value) => {
                element.value = value;
                element.dispatchEvent(new Event('input', { bubbles: true }));
            };

            const setActiveSide = (side) => {
                activeSide = side;
                sideLabel.textContent = fields[side].label;
                state = 'IDLE';
                stableFrames = 0;
                updateStability();
                setStatus(`Ready for ${fields[side].label}`);
            };

            const updateStability = () => {
                const percent = Math.min((stableFrames / framesToStabilize) * 100, 100);
                stabilityBar.style.width = `${percent}%`;
                stabilityPercent.textContent = `${Math.round(percent)}%`;
            };

            const refreshActions = () => {
                const hasFront = Boolean(fields.front.data.value);
                const hasBack = Boolean(fields.back.data.value);

                retakeFrontButton.disabled = !hasFront;
                retakeBackButton.disabled = !hasBack;
                submitCaptureButton.disabled = !(hasFront && hasBack);

                if (hasFront && !hasBack && activeSide === 'front') {
                    setActiveSide('back');
                }
            };

            const startLoop = () => {
                if (loopId) {
                    return;
                }

                loopId = window.setInterval(() => {
                    if (!stream) {
                        return;
                    }

                    analyzeFrame();
                }, 100);
            };

            const startCamera = async () => {
                setCameraHelp();

                if (!window.isSecureContext) {
                    setStatus('Camera blocked', 'bg-danger-600');
                    setCameraHelp('Camera access requires HTTPS on mobile browsers. Open this page through an HTTPS tunnel or a trusted local HTTPS URL.');
                    return;
                }

                if (!navigator.mediaDevices?.getUserMedia) {
                    setStatus('Camera unsupported', 'bg-danger-600');
                    setCameraHelp('This browser cannot access the camera from the current page context.');
                    return;
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: { ideal: 1280 },
                            height: { ideal: 960 },
                            facingMode: { ideal: 'environment' },
                        },
                        audio: false,
                    });

                    video.srcObject = stream;
                    manualCaptureButton.disabled = false;
                    setStatus(`Ready for ${fields[activeSide].label}`);
                    startLoop();
                } catch (error) {
                    console.error(error);
                    setStatus('Camera unavailable', 'bg-danger-600');
                    setCameraHelp(error?.message || 'The browser denied or could not start the camera.');
                }
            };

            const analyzeFrame = () => {
                if (!video.videoWidth || !video.videoHeight || video.offsetParent === null) {
                    return;
                }

                mathContext.drawImage(video, 0, 0, mathCanvas.width, mathCanvas.height);
                const pixels = mathContext.getImageData(0, 0, mathCanvas.width, mathCanvas.height).data;
                const currentPixels = [];
                let totalDiff = 0;

                for (let index = 0; index < pixels.length; index += 4) {
                    const gray = (pixels[index] * 0.299) + (pixels[index + 1] * 0.587) + (pixels[index + 2] * 0.114);
                    currentPixels.push(gray);

                    if (previousPixels.length > 0) {
                        totalDiff += Math.abs(gray - previousPixels[index / 4]);
                    }
                }

                previousPixels = currentPixels;

                const motionScore = currentPixels.length > 0 ? totalDiff / currentPixels.length : 0;
                handleStateMachine(motionScore);
            };

            const handleStateMachine = (motionScore) => {
                if (state === 'IDLE' && motionScore > motionThresholdHigh) {
                    state = 'MOVING';
                    setStatus('Movement detected', 'bg-warning-600');
                    return;
                }

                if (state !== 'MOVING' && state !== 'STABILIZING') {
                    return;
                }

                if (motionScore > motionThresholdLow) {
                    state = 'MOVING';
                    stableFrames = 0;
                    updateStability();
                    setStatus('Waiting to stabilize', 'bg-warning-600');
                    return;
                }

                state = 'STABILIZING';
                stableFrames += 1;
                updateStability();
                setStatus('Holding still', 'bg-primary-600');

                if (stableFrames >= framesToStabilize) {
                    captureSnapshot();
                }
            };

            const captureSnapshot = () => {
                if (!video.videoWidth || !video.videoHeight) {
                    return;
                }

                // Cap resolution at 1600px while maintaining aspect ratio
                const maxDimension = 1600;
                let width = video.videoWidth;
                let height = video.videoHeight;

                if (width > maxDimension || height > maxDimension) {
                    if (width > height) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }
                }

                snapshotCanvas.width = width;
                snapshotCanvas.height = height;
                snapshotContext.drawImage(video, 0, 0, width, height);

                const dataUrl = snapshotCanvas.toDataURL('image/jpeg', 0.75);
                const field = fields[activeSide];

                setFieldValue(field.data, dataUrl);
                setFieldValue(field.width, String(width));
                setFieldValue(field.height, String(height));

                field.preview.src = dataUrl;
                field.preview.classList.remove('hidden');
                field.placeholder.classList.add('hidden');

                setStatus(`${field.label} captured`, 'bg-success-600');
                state = 'CAPTURED';
                stableFrames = 0;
                updateStability();
                refreshActions();

                const sideCaptured = activeSide;
                window.setTimeout(() => {
                    if (sideCaptured === 'back' || !stream) {
                        // Don't return to IDLE if we're done or stream lost
                        return;
                    }

                    if (activeSide === sideCaptured) {
                        setActiveSide('back');
                    }
                }, 350);
            };

            const retake = (side) => {
                const field = fields[side];

                setFieldValue(field.data, '');
                setFieldValue(field.width, '');
                setFieldValue(field.height, '');
                field.preview.removeAttribute('src');
                field.preview.classList.add('hidden');
                field.placeholder.classList.remove('hidden');
                setActiveSide(side);
                refreshActions();
            };

            manualCaptureButton.addEventListener('click', captureSnapshot);
            retakeFrontButton.addEventListener('click', () => retake('front'));
            retakeBackButton.addEventListener('click', () => retake('back'));

            window.addEventListener('reset-capture', () => {
                fields.front.preview.src = '';
                fields.front.preview.classList.add('hidden');
                fields.front.placeholder.classList.remove('hidden');
                fields.back.preview.src = '';
                fields.back.preview.classList.add('hidden');
                fields.back.placeholder.classList.remove('hidden');

                state = 'IDLE';
                stableFrames = 0;
                updateStability();
                setActiveSide('front');
                refreshActions();
            });

            setActiveSide('front');
            refreshActions();
            startCamera();
        })();
    </script>
</x-filament-panels::page>
