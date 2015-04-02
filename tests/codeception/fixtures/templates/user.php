<?php
/**
 * This is the yii2-faker template for the "user" table
 */
$security = Yii::$app->getSecurity();
return [
    'username' => $faker->userName,
    'is_email_verified' => 1,
    'auth_key' => $security->generateRandomString(),
    'password_hash' => $security->generatePasswordHash('password_' . $index),
    'password_reset_token' => $security->generateRandomString() . '_' . time(),
    'email_confirmation_token' => $security->generateRandomString() . '_' . time(),
    'email' => $faker->email,
    'role' => 10,
    'status' => 10,
    'updated_at' => date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s'),
];
