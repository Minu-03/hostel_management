<?php

$password = 'MyNewPassword123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo $hashedPassword;
