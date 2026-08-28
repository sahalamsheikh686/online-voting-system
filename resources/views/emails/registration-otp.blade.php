<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif;">
    <div style="max-width:480px; margin:0 auto; padding:32px 24px;">
        <div style="background:#ffffff; border-radius:20px; padding:32px; box-shadow:0 12px 28px rgba(15,23,42,0.08);">
            <div style="font-size:12px; letter-spacing:0.14em; text-transform:uppercase; color:#0d6efd; font-weight:700;">
                Online Voting System
            </div>
            <h1 style="font-size:22px; margin:12px 0 4px;">Verify your email</h1>
            <p style="color:#475569; margin:0 0 24px;">Hi {{ $name }}, use the code below to finish your registration.</p>

            <div style="background:#0d6efd0d; border:1px dashed #0d6efd; border-radius:16px; padding:20px; text-align:center; margin-bottom:24px;">
                <span style="font-size:32px; font-weight:700; letter-spacing:0.3em; color:#172554;">{{ $otp }}</span>
            </div>

            <p style="color:#475569; margin:0 0 8px;">This code expires in {{ $expiresInMinutes }} minutes.</p>
            <p style="color:#94a3b8; font-size:13px; margin:0;">If you did not request this, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
