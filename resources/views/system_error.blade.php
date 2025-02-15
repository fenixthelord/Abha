!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error Notification</title>
</head>

<body>
    <h1>System Error Notification</h1>
    <p>An error occurred in the system. Please review the details below:</p>

    <h2>Error Message</h2>
    <p>{{ $message }}</p>

    <h2>Error Context</h2>
    <pre>{{ print_r($context, true) }}</pre>

    <p>Thank you,</p>
    <p>{{ config('app.name') }}</p>
</body>

</html>