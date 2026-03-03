<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Assessment Request</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #1a1a1a;">Security Assessment Request</h1>

    <p>Hello {{ $questionnaire->vendor->poc_name }},</p>

    <p>{{ $questionnaire->user->name }} has requested that <strong>{{ $questionnaire->vendor->name }}</strong> complete a security assessment questionnaire as part of their vendor risk management process.</p>

    <p>Please click the button below to access and complete the questionnaire. This should take approximately 20–30 minutes.</p>

    <p style="margin: 30px 0;">
        <a href="{{ $questionnaireUrl }}" style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">Complete Questionnaire</a>
    </p>

    <p>If you have any questions, please contact {{ $questionnaire->user->email }}.</p>

    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
