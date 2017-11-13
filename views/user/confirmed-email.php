<?php
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var \frontend\models\User $model */

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup alert alert-info">
    <h1>Email Confirmed</h1>

    <p>You successfully confirmed your email address and can now 
    <?= Html::a('login', ['user/login']) ?>.
    </p>

</div>
