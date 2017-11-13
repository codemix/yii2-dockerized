<?php
namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;
use app\helpers\Mail;

/**
 * User signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','email','password'], 'required'],

            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'unique', 'targetClass' => User::className()],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className() ],

            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs up new user
     *
     * @return app\models\User|null the saved user model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new User($this->attributes);
        if ($user->save()) {
            Mail::toUser($user, 'confirm-email');
            return $user;
        } else {
            $errors = print_r($user->errors, true);
            Yii::warning("Could not save new user:\n$errors", __METHOD__);
            return null;
        }
    }
}
