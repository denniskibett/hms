<!doctype html>
<html>
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;">
        <tr>
            <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;font-size:18px;font-weight:bold;">{{ SystemHelper::appName() }}</td>
        </tr>
        <tr>
            <td style="padding:24px 28px;font-size:15px;line-height:1.6;">{!! nl2br(e($guestMessage->body)) !!}</td>
        </tr>
        <tr>
            <td style="padding:16px 28px;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
                {{ SystemHelper::appName() }}@if (SystemHelper::contactPhone()) · {{ SystemHelper::contactPhone() }}@endif @if (SystemHelper::contactEmail()) · {{ SystemHelper::contactEmail() }}@endif
            </td>
        </tr>
    </table>
</body>
</html>
