<div class="fixed bottom-4 left-4 right-4 z-50 md:left-auto md:right-8 md:w-96">
    <div class="rounded-xl border border-success-500 bg-white p-4 shadow-2xl dark:bg-gray-900">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-success-100 text-success-600 dark:bg-success-900/30">
                <x-heroicon-o-check-circle class="h-6 w-6" />
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-gray-900 dark:text-white">Session Captured!</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Redirecting to camera in <span id="autoback-countdown" class="font-bold text-primary-600">3</span>s...
                </p>
            </div>
            <a href="{{ $backUrl }}" class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-500">
                Go Now
            </a>
        </div>
    </div>
</div>

<script>
    (function() {
        let seconds = 3;
        const countdownEl = document.getElementById('autoback-countdown');
        const interval = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "{{ $backUrl }}";
            }
        }, 1000);
    })();
</script>
