<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Verify Email</title>
</head>

<body style="font-family: Arial, sans-serif; max-width: 640px; margin: 0 auto; padding: 16px; color: #1f2937;">
	<h1 style="margin-bottom: 8px;">📧 Verify Email</h1>
	<p style="margin-top: 0;">Hi {{ $user?->name ?? 'there' }}, please verify your email address to activate your account.</p>

	<div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #ecfdf5;">
		<p style="margin-top: 0;">Please click the button below to verify your email:</p>

		<p style="margin: 20px 0; text-align: center;">
			<a href="{{ $verificationUrl }}"
				style="display: inline-block; background: #16a34a; color: #ffffff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600;">
				Verify email
			</a>
		</p>

		<p style="margin-bottom: 0; color: #4b5563; font-size: 13px;">
			If the button does not work, copy and paste this URL into your browser:<br>
			<a href="{{ $verificationUrl }}" style="word-break: break-all; color: #2563eb;">{{ $verificationUrl }}</a>
		</p>
	</div>

	<p style="margin-top: 16px; color: #6b7280; font-size: 12px;">
		This link will expire automatically for your security.
	</p>
</body>

</html>
