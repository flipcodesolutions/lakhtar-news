<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>

<body style="margin: 0; padding: 24px; background: #f8fafc; font-family: Arial, sans-serif; color: #1e293b;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="background: #b7131a; color: #ffffff; padding: 20px 24px;">
            <h2 style="margin: 0; font-size: 24px;">Lakhtar News Admin</h2>
        </div>

        <div style="padding: 24px;">
            <p style="margin-top: 0;">Hello {{ $user->name ?? 'Admin' }},</p>
            <p>We received a request to reset your admin account password.</p>
            <p>Click the button below to set a new password:</p>

            <p style="margin: 24px 0;">
                <a href="{{ $resetUrl }}" style="display: inline-block; background: #b7131a; color: #ffffff; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-weight: 600;">
                    Reset Password
                </a>
            </p>

            <p style="margin-bottom: 8px;">If the button does not work, copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #0f5ad1; margin-top: 0;">
                <a href="{{ $resetUrl }}" style="color: #0f5ad1;">{{ $resetUrl }}</a>
            </p>

            <p style="margin-bottom: 0;">If you did not request this password reset, you can safely ignore this email.</p>
        </div>
    </div>
</body>

</html>
