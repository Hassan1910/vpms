<?php
echo "<h2>Password Verification</h2>";

$stored_hash = 'f925916e2754e5e03f75dd58a5733251';
$possible_passwords = ['admin123', 'admin', 'password', '123456', 'test'];

echo "<h3>Testing possible passwords against stored hash:</h3>";
echo "Stored hash: <code>$stored_hash</code><br><br>";

foreach ($possible_passwords as $password) {
    $hash = md5($password);
    $match = ($hash === $stored_hash) ? '✅ MATCH!' : '❌ No match';
    echo "Password: <strong>$password</strong> → Hash: <code>$hash</code> → $match<br>";
}

echo "<hr>";
echo "<h3>Admin Login Information:</h3>";
echo "<strong>Username:</strong> admin<br>";
echo "<strong>Password:</strong> admin123<br>";
echo "<strong>Login URL:</strong> <a href='index.php'>http://localhost/vpms/admin/index.php</a>";
?>
