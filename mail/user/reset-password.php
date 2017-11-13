<?php
/**
 * @var \yii\web\View $this
 * @var \yii\mail\MessageInterface $message
 * @var app\models\User $user
 */

use yii\helpers\Url;
use yii\helpers\Html;

$message->setSubject('Password Reset for My Application');

$url = Url::to(['user/reset-password',
    'token' => $user->password_reset_token,
], true);
?>

Hello <?= Html::encode($user->username) ?>,<br>

<p>Follow the link below to reset your password:</p>

<?= Html::a(Html::encode($url), $url) ?>
