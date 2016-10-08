<?php
namespace api\models;


use common\models\User;
use common\models\Student;
use common\models\Lecturer;

use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    const SCENARIO_STUDENT = 'student';
    const SCENARIO_LECTURER = 'lecturer';

    public $username;
    public $email;
    public $student_id;
    public $password;
    public $role;
    public $device_hash;

    private $_user;
    private $_student;
    private $_lecturer;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_STUDENT => [
                'username',
                'email',
                'student_id',
                'password',
                'role',
                'device_hash'
            ],
            self::SCENARIO_LECTURER => [
                'username',
                'email',
                'password',
                'role'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => User::className(), 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 4, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::className(), 'message' => 'This email address has already been taken.'],
            ['email', 'exist', 'targetClass' => Lecturer::className(),
                'message' => 'No lecturer with given email.', 'on' => self::SCENARIO_LECTURER],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['role', 'in', 'range' => array_keys(User::$roleValues)],
            ['role', 'default', 'value' => User::ROLE_STUDENT,
                'on' => self::SCENARIO_STUDENT],
            ['role', 'default', 'value' => User::ROLE_LECTURER,
                'on' => self::SCENARIO_LECTURER],

            ['student_id', 'required'],
            ['student_id', 'validateStudentId'],

            ['device_hash', 'required'],
            ['device_hash', 'unique', 'targetClass' => User::className(), 'message' => 'This device has already been taken.'],
        ];
    }

    public function validateStudentId($attribute, $params) {
        if (!$this->hasErrors()) {
            $student = $this->getStudent();
            if (!$student) $this->addError($attribute, 'No valid student with given id.');
        }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User([
                'scenario' => $this->scenario
            ]);
            $user->load($this->toArray(), '');
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->name = User::$roleValues[$this->role];

            if ($user->save()) {
                return $user;
            }
        }
        return null;
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function getStudent() {
        if ($this->_student === null) {
            $this->_student = Student::findOne([
                'id' => $this->student_id,
                'user_id' => null
            ]);
        }
        return $this->_student;
    }

    public function getLecturer() {
        if ($this->_lecturer === null) {
            $this->_lecturer = Lecturer::findOne([
                'email' => $this->email,
                'user_id' => null
            ]);
        }
        return $this->_lecturer;
    }
}
