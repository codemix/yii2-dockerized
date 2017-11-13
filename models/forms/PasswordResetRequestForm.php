<?php
namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;
use app\helpers\Mail;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\app\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There is no user with such email.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was sent
     */
    public function sendEmail()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = User::find()
            ->canLogin()
            ->email($this->email)
            ->one();

        if ($user && $user->generatePasswordResetToken(true) && Mail::toUser($user, 'reset-password')) {
            return true;
        }

        $this->addError('email', 'We can not reset the password for this user');
        return false;
    }
}
