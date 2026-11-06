@php
    // Brand values resolved server-side (email clients need literal hex).
    // Source: Appearance → Theme colors (dashboard-colors.json) + branding config.
    $brand = \App\Support\EmailBrand::all();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? '' }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse !important; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        @media screen and (max-width: 525px) {
            .mobile-padding { padding: 28px 24px !important; }
            .mobile-title { font-size: 22px !important; }
            .mobile-button { padding: 14px 28px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $brand['page_bg'] }}; font-family: 'Aeonik', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: {{ $brand['page_bg'] }};">
<tr>
    <td align="center" style="padding: 48px 16px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 560px; background-color: {{ $brand['card_bg'] }}; border-radius: 12px; border: 1px solid {{ $brand['border'] }}; overflow: hidden;">
            {{-- Branded header band --}}
            <tr>
                <td style="padding: 20px 40px; background-color: {{ $brand['accent_tint'] }}; border-bottom: 1px solid {{ $brand['border'] }};" align="left">
                    @if($brand['logo'])
                        <img src="{{ $brand['logo'] }}" alt="{{ $brand['app_name'] }}" height="28" style="display: block; height: 28px; width: auto;">
                    @else
                        <span style="font-size: 15px; font-weight: 800; color: {{ $brand['text'] }}; letter-spacing: -0.01em;">
                            {{ $brand['app_name'] }}<span style="color: {{ $brand['primary'] }};">.</span>
                        </span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="mobile-padding" style="padding: 40px 40px 32px 40px;" align="left">
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: {{ $brand['primary'] }}; padding-bottom: 12px;">
                        {{ $brand['app_name'] }}
                    </div>
                    <h1 class="mobile-title" style="margin: 0 0 20px 0; font-size: 26px; line-height: 32px; color: {{ $brand['text'] }}; font-weight: 700;">
                        {{ $title }}
                    </h1>

                    @foreach(($lines ?? []) as $line)
                        <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: {{ $brand['muted_text'] }};">{!! $line !!}</p>
                    @endforeach

                    @if(!empty($actionUrl))
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 24px 0;">
                            <tr>
                                <td align="center" bgcolor="{{ $brand['primary'] }}" style="border-radius: 8px;">
                                    <a href="{{ $actionUrl }}" class="mobile-button" style="display: inline-block; padding: 12px 24px; font-size: 15px; font-weight: 600; color: {{ $brand['primary_foreground'] }}; text-decoration: none; border-radius: 8px;">
                                        {{ $actionLabel ?? 'View details' }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    @endif

                    @yield('footer')
                </td>
            </tr>
        </table>
        <p style="margin: 24px 0 0 0; font-size: 12px; line-height: 18px; color: {{ $brand['footer_text'] }}; text-align: center;">
            © {{ now()->year }} {{ $brand['app_name'] }} · <a href="{{ url('/') }}" style="color: {{ $brand['footer_text'] }};">{{ url('/') }}</a>
            @if($brand['support_email'])
                · <a href="mailto:{{ $brand['support_email'] }}" style="color: {{ $brand['footer_text'] }};">Support</a>
            @endif
            · <a href="{{ url('/terms') }}" style="color: {{ $brand['footer_text'] }};">Terms</a>
            · <a href="{{ url('/privacy') }}" style="color: {{ $brand['footer_text'] }};">Privacy</a>
        </p>
    </td>
</tr>
</table>
</body>
</html>
