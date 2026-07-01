@php
    $payment = $order->payments->first()?->method ?? 'card';
    $payment = \Illuminate\Support\Str::of($payment)
        ->replaceFirst('stripe-', '')
        ->replaceFirst('icenox-', '')
        ->replace('_', '-')
        ->headline()
        ->toString();
    $payment = $payment === 'Eps' ? 'EPS' : $payment;
    $accent = '#2bff95';
    $bg = '#0a0a0b';
    $card = '#121218';
    $muted = '#9a9aa5';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0;padding:0;background:{{ $bg }};font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }};padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:{{ $card }};border:1px solid rgba(255,255,255,0.08);border-radius:18px;overflow:hidden;">
                    <!-- header -->
                    <tr>
                        <td style="padding:28px 32px;border-bottom:1px solid rgba(255,255,255,0.07);background:linear-gradient(180deg,rgba(43,255,149,0.10),transparent);">
                            <span style="font-size:22px;font-weight:800;letter-spacing:0.04em;color:#ffffff;">LOOT<span style="color:{{ $accent }};">4</span>YOU</span>
                        </td>
                    </tr>
                    <!-- heading -->
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <h1 style="margin:0;font-size:24px;line-height:1.3;color:#ffffff;">{{ $heading }}</h1>
                            <p style="margin:14px 0 0;font-size:14px;line-height:1.7;color:{{ $muted }};">{{ $intro }}</p>
                        </td>
                    </tr>
                    <!-- meta -->
                    <tr>
                        <td style="padding:24px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;font-size:13px;color:{{ $muted }};">Order number</td>
                                    <td style="padding:16px 18px;font-size:13px;color:#ffffff;text-align:right;font-weight:700;">#{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 18px 16px;font-size:13px;color:{{ $muted }};">Date</td>
                                    <td style="padding:0 18px 16px;font-size:13px;color:#ffffff;text-align:right;">{{ $order->created_at?->format('M j, Y · H:i') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 18px 16px;font-size:13px;color:{{ $muted }};">Email</td>
                                    <td style="padding:0 18px 16px;font-size:13px;color:#ffffff;text-align:right;">{{ $order->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 18px 16px;font-size:13px;color:{{ $muted }};">Payment</td>
                                    <td style="padding:0 18px 16px;font-size:13px;color:#ffffff;text-align:right;">{{ $payment }}</td>
                                </tr>
                                @unless($forCustomer)
                                    <tr>
                                        <td style="padding:0 18px 16px;font-size:13px;color:{{ $muted }};">Source</td>
                                        <td style="padding:0 18px 16px;font-size:13px;color:#ffffff;text-align:right;">{{ \Illuminate\Support\Str::headline($order->source ?? 'storefront') }}</td>
                                    </tr>
                                @endunless
                            </table>
                        </td>
                    </tr>
                    <!-- items -->
                    <tr>
                        <td style="padding:16px 32px 8px;">
                            <p style="margin:0 0 12px;font-size:12px;text-transform:uppercase;letter-spacing:0.1em;color:{{ $muted }};">Order details</p>
                            @foreach($order->items as $item)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
                                    <tr>
                                        <td style="font-size:14px;color:#ffffff;font-weight:600;">{{ $item->product_name }} <span style="color:{{ $muted }};font-weight:400;">× {{ $item->quantity }}</span></td>
                                        <td style="font-size:14px;color:#ffffff;text-align:right;white-space:nowrap;">{{ number_format((float) $item->price, 2) }} {{ $order->currency }}</td>
                                    </tr>
                                    @foreach($item->detailLines() as $line)
                                        <tr><td colspan="2" style="font-size:12px;color:{{ $muted }};padding-top:2px;">— @if($line['label']){{ $line['label'] }}: @endif{{ $line['value'] }}</td></tr>
                                    @endforeach
                                </table>
                            @endforeach
                        </td>
                    </tr>
                    <!-- totals -->
                    <tr>
                        <td style="padding:8px 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid rgba(255,255,255,0.08);">
                                @if((float) $order->discount > 0)
                                    <tr>
                                        <td style="padding:14px 0 0;font-size:13px;color:{{ $muted }};">Subtotal</td>
                                        <td style="padding:14px 0 0;font-size:13px;color:#ffffff;text-align:right;">{{ number_format((float) $order->subtotal, 2) }} {{ $order->currency }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0 0;font-size:13px;color:{{ $muted }};">Discount</td>
                                        <td style="padding:6px 0 0;font-size:13px;color:{{ $accent }};text-align:right;">−{{ number_format((float) $order->discount, 2) }} {{ $order->currency }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:14px 0 0;font-size:16px;color:#ffffff;font-weight:700;">Total</td>
                                    <td style="padding:14px 0 0;font-size:18px;color:{{ $accent }};text-align:right;font-weight:800;">{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($ctaUrl ?? null)
                        <tr>
                            <td style="padding:4px 32px 30px;text-align:center;">
                                <a href="{{ $ctaUrl }}" style="display:inline-block;padding:15px 42px;border-radius:12px;background:{{ $accent }};color:#05221a;font-size:15px;font-weight:800;text-decoration:none;">{{ $ctaLabel ?? 'Complete your order' }}</a>
                            </td>
                        </tr>
                    @endif
                    @if($forCustomer && ($showContact ?? false))
                        <tr>
                            <td style="padding:0 32px 28px;">
                                <div style="padding:18px;border:1px solid rgba(43,255,149,0.2);border-radius:12px;background:rgba(43,255,149,0.05);">
                                    <p style="margin:0;font-size:13px;line-height:1.7;color:#dfe1e6;">Need help or want to speed things up? Reply to this email or reach us at <a href="mailto:support@loot4you.gg" style="color:{{ $accent }};text-decoration:none;">support@loot4you.gg</a> with your order number.</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 28px;">
                                <div style="padding:22px;border:1px solid rgba(255,255,255,0.08);border-radius:14px;background:rgba(255,255,255,0.03);text-align:center;">
                                    <p style="margin:0 0 16px;font-size:14px;font-weight:700;color:#ffffff;">To receive your order, please contact us:</p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
                                        <tr>
                                            <td style="padding:0 5px;">
                                                <a href="https://discord.gg/AyTrerusGZ" style="display:inline-block;padding:12px 26px;border-radius:10px;background:#5865f2;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">Discord</a>
                                            </td>
                                            <td style="padding:0 5px;">
                                                <a href="https://wa.me/380730882668?text=Hello!%20I%20want%20to%20get%20my%20order" style="display:inline-block;padding:12px 26px;border-radius:10px;background:#25d366;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">WhatsApp</a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endif
                    <!-- footer -->
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid rgba(255,255,255,0.07);">
                            <p style="margin:0;font-size:11px;color:#5a5a63;">© {{ date('Y') }} Loot4You — All rights reserved. The fastest, safest way to buy gaming credits and coins.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
