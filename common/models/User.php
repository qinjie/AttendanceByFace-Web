<?php
namespace common\models;

use common\components\CustomActiveRecord;
use common\components\TokenHelper;
use common\models\Student;
use common\models\Lecturer;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $person_id
 * @property string $face_id
 * @property string $username
 * @property string $auth_key
 * @property string $device_hash
 * @property string $password_hash
 * @property string $email
 * @property string $profileImg
 * @property integer $status
 * @property integer $role
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Student $student
 * @property UserToken[] $userTokens
 */
class User extends CustomActiveRecord implements IdentityInterface
{
    const SCENARIO_STUDENT = 'student';
    const SCENARIO_LECTURER = 'lecturer';

    const STATUS_WAIT_EMAIL_DEVICE = 0;
    const STATUS_WAIT_DEVICE = 1;
    const STATUS_WAIT_EMAIL = 2;
    const STATUS_ACTIVE = 10;
    const STATUS_BLOCKED = 11;
    const STATUS_DELETED = 12;

    public static $statusValues = [
        self::STATUS_DELETED => 'Deleted',
        self::STATUS_BLOCKED => 'Blocked',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_WAIT_EMAIL => 'Pending Email Verification',
        self::STATUS_WAIT_DEVICE => 'Pending Device Verification',
        self::STATUS_WAIT_EMAIL_DEVICE => 'Pending Email and Device Verification',
    ];

    const ROLE_STUDENT = 20;
    const ROLE_LECTURER = 30;
    const ROLE_ADMINISTRATOR = 40;

    public static $roleValues = [
        self::ROLE_STUDENT => 'Student',
        self::ROLE_LECTURER => 'Lecturer',
        self::ROLE_ADMINISTRATOR => 'Administrator',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_STUDENT] = [
            'username',
            'auth_key',
            'device_hash',
            'password_hash',
            'email',
            'status',
            'role'
        ];
        $scenarios[self::SCENARIO_LECTURER] = [
            'username',
            'auth_key',
            'password_hash',
            'email',
            'status',
            'role'
        ];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email'], 'required'],
            [['status', 'role', 'created_at', 'updated_at'], 'integer'],
            [['person_id', 'username', 'device_hash', 'password_hash', 'email', 'profileImg', 'name'], 'string', 'max' => 255],
            [['face_id'], 'string', 'max' => 1000],
            [['auth_key'], 'string', 'max' => 32],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'message' => 'Please enter an email.'],
            ['email', 'email', 'message' => 'Invalid email address.'],
            ['email', 'unique', 'message' => 'This email address has already been taken.'],
            ['email', 'string', 'max' => 255, 'message' => 'Max 255 characters.'],

            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_WAIT_EMAIL_DEVICE,
                'on' => self::SCENARIO_STUDENT],
            ['status', 'default', 'value' => self::STATUS_WAIT_EMAIL,
                'on' => self::SCENARIO_LECTURER],
            ['status', 'in', 'range' => array_keys(self::$statusValues)],

            ['username', 'required', 'message' => 'Please enter an username.'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i', 'message' => 'Invalid username. Only alphanumeric characters are allowed.'],
            ['username', 'unique', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 4, 'max' => 255, 'message' => 'Min 4 characters; Max 255 characters.'],

            [['device_hash'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'person_id' => 'Person ID',
            'face_id' => 'Face ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'device_hash' => 'Device Hash',
            'password_hash' => 'Password Hash',
            'email' => 'Email',
            'profileImg' => 'Profile Img',
            'status' => 'Status',
            'role' => 'Role',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['auth_key'], $fields['password_hash'],
            $fields['updated_at'], $fields['created_at']);
        $fields['face_id'] = function() {
            $faceId = json_decode($this->face_id);
            if (!$faceId) $faceId = [];
            return $faceId;
        };
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $id = TokenHelper::authenticateToken($token, true);
        if ($id >= 0) {
            return static::findIdentity($id);
        } else {
            return null;
        }
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validateDevice($device_hash) {
        return $this->device_hash === $device_hash;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLecturer()
    {
        return $this->hasOne(Lecturer::className(), ['user_id' => 'id']);
    }
}
