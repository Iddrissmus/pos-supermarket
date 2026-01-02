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
    <p><a href="{{ url('/login') }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Login to your Dashboard</a></p>
    <p>Thank you for choosing POS Supermarket!</p>
</body>
</html>
