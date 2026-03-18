<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Confirmed – {{ $reservation->reservation_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f1ec;font-family:'Georgia',serif;">

{{-- Outer wrapper --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f1ec;">
  <tr>
    <td align="center" style="padding:40px 16px;">

      {{-- Card --}}
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-top:4px solid #C4922A;">

        {{-- ── HEADER ── --}}
        <tr>
          <td style="background:#0d0d0d;padding:40px 40px 32px;text-align:center;">
            <p style="margin:0 0 6px;font-family:'Georgia',serif;font-size:11px;letter-spacing:4px;text-transform:uppercase;color:#C4922A;">{{ $hotel['name'] }}</p>
            <h1 style="margin:0 0 8px;font-family:'Georgia',serif;font-size:26px;font-weight:400;color:#ffffff;letter-spacing:1px;">Booking Confirmed</h1>
            <p style="margin:0;font-family:Arial,sans-serif;font-size:12px;color:#999999;letter-spacing:2px;text-transform:uppercase;">Graceful Stays, Conscious Living</p>
            {{-- Gold divider --}}
            <table width="80" cellpadding="0" cellspacing="0" border="0" style="margin:20px auto 0;">
              <tr><td style="height:1px;background:linear-gradient(90deg,transparent,#C4922A,transparent);font-size:0;">&nbsp;</td></tr>
            </table>
          </td>
        </tr>

        {{-- ── CONFIRMATION BANNER ── --}}
        <tr>
          <td style="background:#C4922A;padding:16px 40px;text-align:center;">
            <p style="margin:0;font-family:Arial,sans-serif;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#ffffff;">Confirmation Number</p>
            <p style="margin:6px 0 0;font-family:'Georgia',serif;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:2px;">{{ $reservation->reservation_number }}</p>
          </td>
        </tr>

        {{-- ── GREETING ── --}}
        <tr>
          <td style="padding:36px 40px 8px;">
            <p style="margin:0 0 12px;font-family:'Georgia',serif;font-size:17px;color:#1a1a1a;">Dear {{ $reservation->guest->first_name }} {{ $reservation->guest->last_name }},</p>
            <p style="margin:0;font-family:Arial,sans-serif;font-size:14px;line-height:1.7;color:#555555;">
              We are delighted to confirm your reservation at <strong>{{ $hotel['name'] }}</strong>.
              Your stay is all set — we look forward to welcoming you.
            </p>
          </td>
        </tr>

        {{-- ── STAY SUMMARY ── --}}
        <tr>
          <td style="padding:24px 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e0d0;">
              {{-- Section heading --}}
              <tr>
                <td colspan="2" style="background:#faf8f4;padding:12px 20px;border-bottom:1px solid #e8e0d0;">
                  <p style="margin:0;font-family:Arial,sans-serif;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#C4922A;font-weight:700;">Stay Details</p>
                </td>
              </tr>
              {{-- Check-in --}}
              <tr>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:12px;color:#888;width:45%;border-bottom:1px solid #f0ebe2;text-transform:uppercase;letter-spacing:1px;">Check-in</td>
                <td style="padding:12px 20px;font-family:'Georgia',serif;font-size:15px;color:#1a1a1a;border-bottom:1px solid #f0ebe2;font-weight:bold;">{{ $reservation->check_in_date->format('l, F j, Y') }}</td>
              </tr>
              {{-- Check-out --}}
              <tr>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #f0ebe2;">Check-out</td>
                <td style="padding:12px 20px;font-family:'Georgia',serif;font-size:15px;color:#1a1a1a;border-bottom:1px solid #f0ebe2;">{{ $reservation->check_out_date->format('l, F j, Y') }}</td>
              </tr>
              {{-- Nights --}}
              <tr>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #f0ebe2;">Duration</td>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:14px;color:#1a1a1a;border-bottom:1px solid #f0ebe2;">{{ $reservation->nights }} Night{{ $reservation->nights != 1 ? 's' : '' }}</td>
              </tr>
              {{-- Room --}}
              <tr>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #f0ebe2;">Room Type</td>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:14px;color:#1a1a1a;border-bottom:1px solid #f0ebe2;">
                  {{ $reservation->roomType->name }}
                  @if($reservation->room)
                    &nbsp;<span style="color:#888;font-size:12px;">(Room {{ $reservation->room->room_number }})</span>
                  @endif
                </td>
              </tr>
              {{-- Guests --}}
              <tr>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;">Guests</td>
                <td style="padding:12px 20px;font-family:Arial,sans-serif;font-size:14px;color:#1a1a1a;">
                  {{ $reservation->number_of_adults }} Adult{{ $reservation->number_of_adults != 1 ? 's' : '' }}
                  @if($reservation->number_of_children > 0)
                    , {{ $reservation->number_of_children }} Child{{ $reservation->number_of_children != 1 ? 'ren' : '' }}
                  @endif
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- ── PRICING ── --}}
        <tr>
          <td style="padding:20px 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e0d0;">
              <tr>
                <td colspan="2" style="background:#faf8f4;padding:12px 20px;border-bottom:1px solid #e8e0d0;">
                  <p style="margin:0;font-family:Arial,sans-serif;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#C4922A;font-weight:700;">Pricing Summary</p>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#555;border-bottom:1px solid #f0ebe2;">Room Rate</td>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;text-align:right;border-bottom:1px solid #f0ebe2;">{{ $hotel['currency'] }} {{ number_format($reservation->room_rate, 0) }} / night</td>
              </tr>
              <tr>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#555;border-bottom:1px solid #f0ebe2;">Total Room Charges ({{ $reservation->nights }}n)</td>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;text-align:right;border-bottom:1px solid #f0ebe2;">{{ $hotel['currency'] }} {{ number_format($reservation->total_room_charges, 0) }}</td>
              </tr>
              @if($reservation->taxes > 0)
              <tr>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#555;border-bottom:1px solid #f0ebe2;">Taxes &amp; Charges</td>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;text-align:right;border-bottom:1px solid #f0ebe2;">{{ $hotel['currency'] }} {{ number_format($reservation->taxes + $reservation->service_charges, 0) }}</td>
              </tr>
              @endif
              @if(isset($reservation->discount_amount) && $reservation->discount_amount > 0)
              <tr>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#2e7d32;border-bottom:1px solid #f0ebe2;">Discount</td>
                <td style="padding:10px 20px;font-family:Arial,sans-serif;font-size:13px;color:#2e7d32;text-align:right;border-bottom:1px solid #f0ebe2;">− {{ $hotel['currency'] }} {{ number_format($reservation->discount_amount, 0) }}</td>
              </tr>
              @endif
              {{-- Total --}}
              <tr style="background:#faf8f4;">
                <td style="padding:14px 20px;font-family:'Georgia',serif;font-size:15px;font-weight:bold;color:#1a1a1a;border-top:2px solid #C4922A;">Total Amount</td>
                <td style="padding:14px 20px;font-family:'Georgia',serif;font-size:15px;font-weight:bold;color:#C4922A;text-align:right;border-top:2px solid #C4922A;">{{ $hotel['currency'] }} {{ number_format($reservation->total_amount, 0) }}</td>
              </tr>
              @if($reservation->paid_amount > 0)
              <tr>
                <td style="padding:8px 20px;font-family:Arial,sans-serif;font-size:12px;color:#888;">Deposit Paid</td>
                <td style="padding:8px 20px;font-family:Arial,sans-serif;font-size:12px;color:#2e7d32;text-align:right;">− {{ $hotel['currency'] }} {{ number_format($reservation->paid_amount, 0) }}</td>
              </tr>
              <tr>
                <td style="padding:8px 20px 14px;font-family:Arial,sans-serif;font-size:12px;color:#888;">Balance Due at Check-in</td>
                <td style="padding:8px 20px 14px;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;color:#1a1a1a;text-align:right;">{{ $hotel['currency'] }} {{ number_format($reservation->balance_amount, 0) }}</td>
              </tr>
              @endif
            </table>
          </td>
        </tr>

        {{-- ── SPECIAL REQUESTS ── --}}
        @if($reservation->special_requests)
        <tr>
          <td style="padding:20px 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e0d0;border-left:3px solid #C4922A;">
              <tr>
                <td style="padding:14px 20px;">
                  <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#C4922A;font-weight:700;">Special Requests</p>
                  <p style="margin:0;font-family:Arial,sans-serif;font-size:13px;color:#555;line-height:1.6;">{{ is_array($reservation->special_requests) ? implode(', ', array_filter($reservation->special_requests)) : $reservation->special_requests }}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        @endif

        {{-- ── CONFIRMATION TOKEN ── --}}
        <tr>
          <td style="padding:20px 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#faf8f4;border:1px solid #e8e0d0;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#888;font-weight:700;">Your Booking Reference Token</p>
                  <p style="margin:0 0 8px;font-family:'Courier New',monospace;font-size:15px;color:#1a1a1a;letter-spacing:2px;font-weight:bold;">{{ $confirmationToken }}</p>
                  <p style="margin:0;font-family:Arial,sans-serif;font-size:11px;color:#999;line-height:1.5;">Keep this token safe — you may need it to retrieve or manage your booking. Do not share it with others.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- ── WHAT TO EXPECT ── --}}
        <tr>
          <td style="padding:28px 40px 0;">
            <p style="margin:0 0 14px;font-family:'Georgia',serif;font-size:14px;color:#1a1a1a;letter-spacing:1px;">What to expect</p>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td valign="top" style="padding:0 12px 12px 0;width:33%;font-family:Arial,sans-serif;font-size:12px;color:#555;line-height:1.6;text-align:center;">
                  <p style="margin:0 0 4px;font-size:18px;">🕐</p>
                  <p style="margin:0 0 4px;font-weight:bold;color:#1a1a1a;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Check-in Time</p>
                  <p style="margin:0;">From 14:00</p>
                </td>
                <td valign="top" style="padding:0 12px 12px;width:33%;font-family:Arial,sans-serif;font-size:12px;color:#555;line-height:1.6;text-align:center;">
                  <p style="margin:0 0 4px;font-size:18px;">🕑</p>
                  <p style="margin:0 0 4px;font-weight:bold;color:#1a1a1a;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Check-out Time</p>
                  <p style="margin:0;">By 12:00</p>
                </td>
                <td valign="top" style="padding:0 0 12px 12px;width:33%;font-family:Arial,sans-serif;font-size:12px;color:#555;line-height:1.6;text-align:center;">
                  <p style="margin:0 0 4px;font-size:18px;">🪪</p>
                  <p style="margin:0 0 4px;font-weight:bold;color:#1a1a1a;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Please Bring</p>
                  <p style="margin:0;">Valid ID document</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- ── CONTACT ── --}}
        <tr>
          <td style="padding:24px 40px 0;">
            <p style="margin:0 0 10px;font-family:Arial,sans-serif;font-size:13px;color:#555;line-height:1.7;">
              If you have any questions or need to modify your reservation, we are always here to help.
            </p>
            <table cellpadding="0" cellspacing="0" border="0">
              @if($hotel['phone'])
              <tr>
                <td style="padding:2px 12px 2px 0;font-family:Arial,sans-serif;font-size:13px;color:#888;">📞</td>
                <td style="padding:2px 0;font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;"><a href="tel:{{ $hotel['phone'] }}" style="color:#C4922A;text-decoration:none;">{{ $hotel['phone'] }}</a></td>
              </tr>
              @endif
              @if($hotel['email'])
              <tr>
                <td style="padding:2px 12px 2px 0;font-family:Arial,sans-serif;font-size:13px;color:#888;">✉️</td>
                <td style="padding:2px 0;font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;"><a href="mailto:{{ $hotel['email'] }}" style="color:#C4922A;text-decoration:none;">{{ $hotel['email'] }}</a></td>
              </tr>
              @endif
              @if($hotel['address'])
              <tr>
                <td style="padding:2px 12px 2px 0;font-family:Arial,sans-serif;font-size:13px;color:#888;">📍</td>
                <td style="padding:2px 0;font-family:Arial,sans-serif;font-size:13px;color:#555;">{{ $hotel['address'] }}</td>
              </tr>
              @endif
            </table>
          </td>
        </tr>

        {{-- ── SIGN-OFF ── --}}
        <tr>
          <td style="padding:32px 40px 36px;">
            <p style="margin:0 0 4px;font-family:'Georgia',serif;font-size:14px;color:#1a1a1a;">With warm regards,</p>
            <p style="margin:0;font-family:'Georgia',serif;font-size:15px;font-weight:bold;color:#C4922A;">The {{ $hotel['name'] }} Team</p>
          </td>
        </tr>

        {{-- ── FOOTER ── --}}
        <tr>
          <td style="background:#0d0d0d;padding:20px 40px;text-align:center;">
            <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#C4922A;">{{ $hotel['name'] }}</p>
            @if($hotel['address'])
            <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:11px;color:#666;">{{ $hotel['address'] }}</p>
            @endif
            <p style="margin:0;font-family:Arial,sans-serif;font-size:10px;color:#444;">&copy; {{ date('Y') }} {{ $hotel['name'] }}. All rights reserved.</p>
          </td>
        </tr>

      </table>
      {{-- End card --}}

    </td>
  </tr>
</table>

</body>
</html>
