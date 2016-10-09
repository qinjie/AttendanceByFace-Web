<?php
namespace api\models;


use common\models\User;
use api\modules\v1\controllers\UserController;

use Yii;
use yii\base\Model;

/**
 * Change password form
 */
class ChangePasswordForm extends Model
{
    public $oldPassword;
    public $newPassword;

    private $_user;

    /**
     * Creates a form model given a user.
     */
    public function __construct($user, $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['oldPassword', 'required'],
            ['oldPassword', 'validatePassword'],
            ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => 6],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->_user;
            if ($user && !$user->validatePassword($this->oldPassword))
                $this->addError($attribute, 'Incorrect password.');
        }
    }

    public function changePassword()
    {
        if ($this->validate()) {
            $user = $this->_user;
            $user->setPassword($this->newPassword);
            return $user->save();
        }
        return null;
    }

    public function attributeLabels()
    {
        return [
        ];
    }
}
