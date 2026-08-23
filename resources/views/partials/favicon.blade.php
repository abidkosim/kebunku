@php
    // Identitas diambil dari SesiAktor (scoped per request), jadi partial ini tidak
    // lagi menjalankan Owner::find()/User::find() sendiri - datanya sudah diselesaikan
    // sekali untuk seluruh halaman.
    $faviconOwner = app(\App\Support\SesiAktor::class)->owner();
    $faviconInitial = $faviconOwner && $faviconOwner->nama_usaha ? strtoupper(substr($faviconOwner->nama_usaha, 0, 1)) : 'K';
    $faviconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
        .'<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0f172a"/><stop offset="1" stop-color="#334155"/></linearGradient></defs>'
        .'<rect width="100" height="100" rx="22" fill="url(#g)"/>'
        .'<text x="50" y="54" font-family="Arial, Helvetica, sans-serif" font-size="56" font-weight="800" fill="#ffffff" text-anchor="middle" dominant-baseline="middle">'.e($faviconInitial).'</text>'
        .'</svg>';
@endphp
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode($faviconSvg) }}">
