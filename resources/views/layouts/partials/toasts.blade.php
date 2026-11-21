<div class="rf-toasts" aria-live="polite">
    @foreach (['status' => 'ok', 'error' => 'error', 'info' => 'info'] as $key => $kind)
        @if (session($key))
            <div class="rf-alert rf-alert--{{ $kind }} rf-toast" role="{{ $kind === 'error' ? 'alert' : 'status' }}">
                <x-icon :name="['ok' => 'check-circle', 'error' => 'alert-circle', 'info' => 'info'][$kind]" />
                <div class="rf-alert__body">
                    <span class="rf-alert__kind">{{ ['ok' => 'Berhasil', 'error' => 'Perlu diperbaiki', 'info' => 'Info'][$kind] }}</span>
                    {{ session($key) }}
                </div>
                <button type="button" class="rf-btn rf-btn--tertiary rf-btn--icon" data-toast-close aria-label="Tutup pemberitahuan"><x-icon name="x" /></button>
            </div>
        @endif
    @endforeach
</div>