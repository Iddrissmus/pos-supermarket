<!DOCTYPE html>
<html>
<head>
    <title>Welcome to POS Supermarket</title>
</head>
<body>
    <h1>Welcome, {{ $user->name }}!</h1>
    <p>Your business account has been successfully created.</p>
    <p>Here are your login credentials:</p>
    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>
    <p>Please login and change your password immediately.</p>
    <p>Thank you for choosing POS Supermarket!</p>
</body>
</html>
