@php
    $faviconVersion = '20260603';
    $socialImage = asset('assets/nexo-og-image.png') . '?v=' . $faviconVersion;
@endphp

<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $faviconVersion }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('favicon-32x32.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ $faviconVersion }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Nexo Saúde">
<meta property="og:title" content="Nexo Saúde">
<meta property="og:description" content="Plataforma Nexo Saúde para corretores de planos de saúde.">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $socialImage }}">
<meta property="og:image:secure_url" content="{{ $socialImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Nexo Saúde">
<meta name="twitter:description" content="Plataforma Nexo Saúde para corretores de planos de saúde.">
<meta name="twitter:image" content="{{ $socialImage }}">
