<?php
/**
 * @var \yii\web\View $this
 * @var \yii\mail\MessageInterface $message
 * @var app\models\User $user
 */

use yii\helpers\Url;
use yii\helpers\Html;

$message->setSubject('Complete registration with My Application');

$url = Url::to(['/user/confirm-email',
    'token' => $user->email_confirmation_token,
], true);
?>

Hello <?= Html::encode($user->username) ?>,<br>

<p>Follow the link below to complete your registration:</p>

<?= Html::a(Html::encode($url), $url) ?>
