<?php
namespace api\common\models;

use Yii;
use yii\helpers\Url;
use yii\web\Link;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use api\common\helpers\TokenHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class User extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
	const STATUS_BLOCKED = 1;
	const STATUS_WAIT = 5;
    const STATUS_ACTIVE = 10;

    public static $roles = [
        10 => 'user',
        20 => 'student',
        30 => 'teacher',
    ];

    const ROLE_USER = 10;
    const ROLE_STUDENT = 20;
    const ROLE_TEACHER = 30;

    public static function tableName()
    {
        return 'user';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at']
                ],
                // 'value' => new Expression('NOW()'),
                'value' => time(),
            ],
        ];
    }

    public function rules()
    {
        return [
            ['username', 'required', 'message' => 'Please enter an username.'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i', 'message' => 'Invalid username. Only alphanumeric characters are allowed.'],
            ['username', 'unique', 'targetClass' => self::className(), 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 4, 'max' => 255, 'message' => 'Min 4 characters; Max 255 characters.'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'message' => 'Please enter an email.'],
            ['email', 'email', 'message' => 'Invalid email address.'],
            ['email', 'unique', 'targetClass' => self::className(), 'message' => 'This email address has already been taken.'],
            ['email', 'string', 'max' => 255, 'message' => 'Max 255 characters.'],
            
            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getStatusesArray())],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['auth_key'], $fields['password_hash'], 
            $fields['updated_at'], $fields['created_at']);
        return $fields;
    }

    public function getStatusName()
    {
        return ArrayHelper:: getValue(self:: getStatusesArray(), $this->status);
    }

    public static function getStatusesArray()
    {
        return [
            self::STATUS_DELETED => 'Deleted',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_WAIT => 'Pending Confirmation',];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $id = TokenHelper::authenticateToken($token, true);
        if ($id) {
            return static::findIdentity($id);
        } else {
            return null;
        }
    }

    public static function findByUsername($username, $status = NULL)
    {
        if (!$status) $status = self::STATUS_ACTIVE;
        return static::findOne(['username' => $username, 'status' => $status]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function beforeSave($insert)
    {
        if (isset($this->password))
            $this->setPassword($this->password);
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateAuthKey();
            }
            return true;
        }
        return false;
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->refresh();
        parent::afterSave($insert, $changedAttributes);
    }
}
