<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\forms\SignupForm;
use app\models\forms\LoginForm;
use app\models\forms\PasswordResetRequestForm;
use app\models\forms\ResetPasswordForm;

class UserController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response the login form or a redirect response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @return \yii\web\Response a redirect response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * @return string|\yii\web\Response the signup form, the signup message or
     * a redirect response
     */
    public function actionSignup()
    {
        if (Yii::$app->session->hasFlash('user-signed-up')) {
            return $this->render('signed-up');
        }

        $model = new SignupForm;
        if ($model->load(Yii::$app->request->post()) && $model->signup() !== null) {
            Yii::$app->session->setFlash('user-signed-up');
            return $this->refresh();
        } else {
            return $this->render('signup', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @return string|\yii\web\Response the confirmation failure message or a
     * redirect response
     */
    public function actionConfirmEmail($token)
    {
        if (Yii::$app->session->hasFlash('user-confirmed-email')) {
            return $this->render('confirmed-email');
        }

        $user = User::find()
            ->emailConfirmationToken($token)
            ->one();

        if ($user !== null && $user->confirmEmail()) {
            Yii::$app->session->setFlash('user-confirmed-email');
            return $this->refresh();
        } else {
            return $this->render('email-confirmation-failed');
        }
    }

    /**
     * @return string|\yii\web\Response the form to request a password reset or
     * a redirect response
     */
    public function actionRequestPasswordReset()
    {
        if (Yii::$app->session->hasFlash('user-requested-password-reset')) {
            return $this->render('requested-password-reset');
        }

        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->sendEmail()) {
            Yii::$app->session->setFlash('user-requested-password-reset');
            return $this->refresh();
        } else {
            return $this->render('request-password-reset', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @return string|\yii\web\Response the form to reset the password or a
     * redirect response
     */
    public function actionResetPassword($token)
    {
        if (Yii::$app->session->hasFlash('user-password-was-reset')) {
            return $this->render('password-was-reset');
        }

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->resetPassword()) {
            Yii::$app->session->setFlash('user-password-was-reset');
            return $this->refresh();
        }

        return $this->render('reset-password', [
            'model' => $model,
        ]);
    }

}
