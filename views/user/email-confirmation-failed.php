<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var yii\web\View $this */

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
$params = Yii::$app->params;
?>
<div class="site-signup alert alert-danger">
    <h1>Could not complete registration</h1>

    <p>You either supplied an invalid confirmation link or the link has meanwhile expired.</p>
    </p>

</div>
