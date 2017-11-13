<?php
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var \frontend\models\User $model */

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup alert alert-info">
    <h1>Password Was Reset</h1>

    <p>You can now <?= Html::a('login', ['user/login']) ?> with your new password.</p>

</div>
