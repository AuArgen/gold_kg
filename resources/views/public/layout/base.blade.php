@extends('app')

@section('title')
    @yield('title', 'Кыргызстандын алтын баалары')
@endsection

@section('main')
@include('public.layout.header')
<div class="flex-grow">
    @yield('content')
</div>
@include('public.layout.footer')

{{-- БӨЛҮШҮҮ БАСКЫЧЫ (Floating Action Button) --}}
<div class="fixed bottom-6 right-6 z-50">
    <button onclick="openShareModal()" class="btn btn-circle btn-lg btn-primary shadow-2xl hover:scale-110 transition-transform tooltip tooltip-left" data-tip="Бөлүшүү">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
    </button>
</div>

{{-- БӨЛҮШҮҮ МОДАЛДЫК ТЕРЕЗЕСИ --}}
<input type="checkbox" id="share-modal-toggle" class="modal-toggle" />
<div class="modal" role="dialog">
    <div class="modal-box text-center">
        <h3 class="font-bold text-2xl mb-4">Сайт менен бөлүшүү</h3>

        {{-- QR Code --}}
        <div class="flex justify-center mb-6">
            <canvas id="qrcode-canvas"></canvas>
        </div>

        {{-- Шилтеме --}}
        <div class="join w-full mb-4">
            <input type="text" id="share-link-input" class="input input-bordered join-item w-full" value="{{ url()->current() }}" readonly />
            <button onclick="copyShareLink()" class="btn btn-primary join-item">Көчүрүү</button>
        </div>

        <p class="text-sm text-base-content/70">Досторуңузга QR-кодду көрсөтүңүз же шилтемени жөнөтүңүз.</p>

        <div class="modal-action justify-center">
            <label for="share-modal-toggle" class="btn">Жабуу</label>
        </div>
    </div>
    <label class="modal-backdrop" for="share-modal-toggle">Жабуу</label>
</div>

{{-- QR Code китепканасы (QRious) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<script>
    function openShareModal() {
        const modalCheckbox = document.getElementById('share-modal-toggle');
        const canvas = document.getElementById('qrcode-canvas');
        const currentUrl = window.location.href;

        // QR кодду генерациялоо
        new QRious({
            element: canvas,
            value: currentUrl,
            size: 200,
            level: 'H' // Жогорку сапат
        });

        // Модалды ачуу
        modalCheckbox.checked = true;
    }

    function copyShareLink() {
        const input = document.getElementById('share-link-input');
        input.select();
        input.setSelectionRange(0, 99999); // Мобилдик түзмөктөр үчүн

        navigator.clipboard.writeText(input.value).then(() => {
            // Баскычтын текстин убактылуу өзгөртүү
            const btn = document.querySelector('button[onclick="copyShareLink()"]');
            const originalText = btn.innerText;
            btn.innerText = 'Көчүрүлдү!';
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.remove('btn-success');
            }, 2000);
        });
    }
</script>

@endsection
