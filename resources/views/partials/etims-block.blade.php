{{-- KRA eTIMS signature and QR code for a signed invoice. @include('partials.etims-block', ['invoice' => $invoice]) --}}
@php $etims = $invoice?->etimsReceipt; @endphp
@if ($etims?->isSent())
    @php
        $qr = (new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(132, 0),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        )))->writeString($etims->verify_url);
    @endphp
    <section class="mt-8 flex items-center gap-5 rounded-lg border border-gray-200 p-4 text-xs text-gray-600" aria-label="KRA eTIMS">
        <div class="h-[132px] w-[132px] shrink-0">{!! $qr !!}</div>
        <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
            <dt class="font-semibold text-gray-800">KRA eTIMS</dt><dd>Scan to verify this invoice with KRA</dd>
            <dt>Receipt no.</dt><dd>{{ $etims->rcpt_no }}{{ $etims->tot_rcpt_no ? ' / ' . $etims->tot_rcpt_no : '' }}</dd>
            <dt>SCU ID</dt><dd>{{ $etims->sdc_id }}</dd>
            @if ($etims->mrc_no)<dt>MRC no.</dt><dd>{{ $etims->mrc_no }}</dd>@endif
            <dt>Internal data</dt><dd class="break-all font-mono">{{ $etims->intrl_data }}</dd>
            <dt>Signature</dt><dd class="break-all font-mono">{{ $etims->rcpt_sign }}</dd>
            <dt>Signed</dt><dd>{{ $etims->receipt_at?->format('d M Y, H:i') }}</dd>
        </dl>
    </section>
@endif
