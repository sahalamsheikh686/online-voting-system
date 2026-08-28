<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif;">
    <div style="max-width:480px; margin:0 auto; padding:32px 24px;">
        <div style="background:#ffffff; border-radius:20px; padding:32px; box-shadow:0 12px 28px rgba(15,23,42,0.08);">
            <div style="font-size:12px; letter-spacing:0.14em; text-transform:uppercase; color:#dc3545; font-weight:700;">
                Online Voting System
            </div>
            <h1 style="font-size:22px; margin:12px 0 4px;">You are rejected</h1>
            <p style="color:#475569; margin:0 0 24px;">Hi {{ $name }}, your registration was reviewed and rejected for the reason below.</p>

            <div style="background:#dc35450d; border:1px solid #dc354533; border-radius:16px; padding:18px 20px; margin-bottom:24px;">
                <span style="color:#7f1d1d; font-size:15px;">{{ $reason }}</span>
            </div>

            <p style="color:#475569; margin:0 0 8px;">You can fix the issue above and try registering again.</p>
            <p style="color:#94a3b8; font-size:13px; margin:0;">If you believe this is a mistake, please contact the admin.</p>
        </div>
    </div>
</body>
</html>
