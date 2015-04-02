<?php
namespace tests\codeception\unit\models\behaviors;

use Yii;
use yii\codeception\DbTestCase;
use tests\codeception\fixtures\UserFixture;
use app\models\User;

class TimestampBehaviorTest extends DbTestCase
{
    use \Codeception\Specify;

    const DATE_PATTERN = '/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/';

    public function fixtures()
    {
        return [
            'users' => UserFixture::className(),
        ];
    }

    public function testTimestampBehavior()
    {
        $this->specify('create and update time are set on creation', function() {
            $user = new User([
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => 'testpw',
            ]);
            $time = time();
            expect('new record can be saved', $user->save())->true();
            $user = User::findOne($user->id);
            expect('saved record can be found', $user)->notNull();
            expect('creation time is a datetime string', preg_match(self::DATE_PATTERN, $user->created_at))->equals(1);
            expect('update time is a datetime string', preg_match(self::DATE_PATTERN, $user->updated_at))->equals(1);
            expect('creation time is set correctly', strtotime($user->created_at))->greaterOrEquals($time);
            expect('update time is set correctly', strtotime($user->updated_at))->greaterOrEquals($time);
        });

        $this->specify('only update time is set on update', function() {
            $user = $this->users('default');
            $user->username = 'modified';
            $created_at = $user->created_at;
            $updated_at = $user->updated_at;
            $time = time();
            expect('user record can be updated', $user->save())->true();
            $user = User::findOne($user->id);
            expect('updated record can be found', $user)->notNull();
            expect('creation time is unchanged', $user->created_at)->equals($created_at);
            expect('update time is updated', $user->updated_at)->notEquals($updated_at);
            expect('update time is a datetime string', preg_match(self::DATE_PATTERN, $user->updated_at))->equals(1);
            expect('update time is set correctly', strtotime($user->updated_at))->greaterOrEquals($time);
        });
    }
}

