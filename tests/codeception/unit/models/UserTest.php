<?php
namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\DbTestCase;
use app\models\User;
use tests\codeception\fixtures\UserFixture;

class UserTest extends DbTestCase
{
    use \Codeception\Specify;

    public function fixtures()
    {
        return [
            'users' => UserFixture::className(),
        ];
    }

    public function testUserQueries()
    {
        $this->specify('i can query for users that can login', function() {
            expect('3 users can login', User::find()->canLogin()->count())->equals(3);
        });

        $this->specify('i can query for a user by email', function() {
            $user = $this->users['default'];
            expect('1 user is found by email', User::find()->email($user['email'])->count())->equals(1);
        });

        $this->specify('i can query a user by username', function() {
            $user = $this->users['default'];
            expect('1 user is found by email', User::find()->username($user['username'])->count())->equals(1);
        });

        $this->specify('i can not query a user by an expired password reset token', function() {
            $user = $this->users['password_token_expired'];
            expect('no user is found by an expired password reset token', User::find()->passwordResetToken($user['password_reset_token'])->count())->equals(0);
        });

        $this->specify('i can not query a user by an expired email confirmation token', function() {
            $user = $this->users['email_token_expired'];
            expect('no user is found by an expired email confirmation token', User::find()->emailConfirmationToken($user['email_confirmation_token'])->count())->equals(0);
        });
    }

    public function testUserPasswordValidation()
    {
        $user = new User([
            'username' => 'test',
            'email' => 'test@example.com',
            'password' => 'secretpassword',
        ]);
        $this->assertTrue($user->save());
        $this->assertNotNull($user->auth_key);
        $this->specify('i can validate passwords', function() use ($user) {
            expect('invalid password is not verified', $user->validatePassword('wrongpw'))->false();
            expect('valid password is verified', $user->validatePassword('secretpassword'))->true();
        });
    }

    public function testUserPasswordReset()
    {
        $user = new User([
            'username' => 'test',
            'email' => 'test@example.com',
            'password' => 'testpw',
        ]);
        $this->assertTrue($user->save());

        $this->specify('i can genereate a password reset token for a user', function() use ($user) {
            expect('password reset token is empty for a new user', $user->password_reset_token)->null();
            expect('password reset token can be created', $user->generatePasswordResetToken(true))->true();
            $user = User::findOne($user->id);
            expect('updated user can be found', $user)->notNull();
            expect('password reset token is not empty after creation', $user->password_reset_token)->notNull();
        });


        $this->specify('i can query a user by a password reset token', function() use ($user) {
            expect('1 user is found by a valid password reset token', User::find()->passwordResetToken($user->password_reset_token)->count())->equals(1);
        });

        $this->specify('i can reset a password', function() use ($user) {
            $hash = $user->password_hash;
            expect('password reset token is not empty', $user->password_reset_token)->notNull();
            expect('password can be reset', $user->resetPassword('newpass'))->true();
            $user = User::findOne($user->id);
            expect('updated user can be found', $user)->notNull();
            expect('password reset token is empty after reset', $user->password_reset_token)->null();
            expect('password was changed', $user->password_hash)->notEquals($hash);
        });
    }

    public function testUserEmailConfirmation()
    {
        $user = new User([
            'username' => 'test',
            'email' => 'test@example.com',
            'password' => 'testpw',
        ]);
        $this->assertTrue($user->save());

        $this->specify('the email verification process is started for new users', function() use ($user) {
            expect('email is not verified', $user->is_email_verified)->equals(0);
            expect('email confirmation token is not empty', $user->email_confirmation_token)->notNull();
            // @todo: test that email was sent?
        });

        $this->specify('i can query a user by a email confirmation token', function() use ($user) {
            expect('1 user is found by a valid email confirmation token', User::find()->emailConfirmationToken($user->email_confirmation_token)->count())->equals(1);
        });

        $this->specify('i can confirm a user email', function() use ($user) {
            expect('email confirmation token is not empty', $user->email_confirmation_token)->notNull();
            expect('email can be confirmed', $user->confirmEmail())->true();
            $user = User::findOne($user->id);
            expect('updated user can be found', $user)->notNull();
            expect('email confirmation token is empty after confirmation', $user->email_confirmation_token)->null();
            expect('email is verified', $user->is_email_verified)->equals(1);
        });
    }
}
