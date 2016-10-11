<?php
namespace api\models;


use common\models\User;
use common\models\Student;
use common\models\Attendance;
use common\components\Util;
use api\modules\v1\controllers\UserController;
use api\components\Facepp;

use Yii;
use yii\base\Model;

/**
 * Take attendance by face form
 */
class FaceAttendanceForm extends Model
{
    const FIFTEEN_MINUTES = 15 * 60;
    const FACE_THRESHOLD = 50;

    public $id;
    public $face_id;

    private $_user;
    private $_student;
    private $_attendance;

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
            [['id', 'face_id'], 'required'],
            ['id', 'validateAttendanceId'],
            // ['face_id', 'validateFaceId']
        ];
    }

    public function validateAttendanceId($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $attendance = $this->getAttendance();
            if (!$attendance) {
                $this->addError($attribute, 'Invalid attendance info.');
                return;
            }

            $currentTime = date('H:i');
            $lessonFrom = strtotime($attendance->lesson->start_time);
            $lessonTo = strtotime($attendance->lesson->end_time);
            $allowedFrom = date('H:i', $lessonFrom - self::FIFTEEN_MINUTES);
            $allowedTo = date('H:i', $lessonTo);

            // For testing, comment these 2 lines
            // if ($currentTime < $allowedFrom || $currentTime > $allowedTo)
            //     $this->addError($attribute, 'Invalid attendance info.');
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function takeAttendance()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            $facepp = new Facepp();
            $facepp->api_key = Yii::$app->params['FACEPP_API_KEY'];
            $facepp->api_secret = Yii::$app->params['FACEPP_API_SECRET'];
            $response = $facepp->verifyFace($user, $this->face_id);
            if ($response['http_code'] != 200) {
                $this->addError('face_id', 'Invalid face.');
                return;
            }
            $facepp->trainNewFace($user, $this->face_id);
            $result = json_decode($response['body']);

            if ($result->confidence < self::FACE_THRESHOLD) {
                $this->addError('face_id', 'Face is not matched.');
            } else {
                $attendance = $this->getAttendance();
                $attendance->is_absent = 0;
                $lateMinutes = Util::getDifferenceInMinutes(
                    $attendance->lesson->start_time, date('H:i'));
                $attendance->is_late = intval($lateMinutes > 0);
                $attendance->late_min = $lateMinutes;
                $attendance->recorded_time = date('H:i');
                if ($attendance->save())
                    return $attendance;
                else return null;
            }
        }
        if ($this->hasErrors()) return false;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    public function getStudent() {
        if ($this->_student === null) {
            $this->_student = Student::findOne([
                'user_id' => $this->_user->id
            ]);
        }
        return $this->_student;
    }

    public function getAttendance() {
        if ($this->_attendance === null) {
            $student = $this->getStudent();
            $this->_attendance = Attendance::findOne([
                'id' => $this->id,
                'student_id' => $student->id,
                'recorded_date' => date('Y-m-d'),   // Only allow take attendance in today
                'recorded_time' => null     // Not taking attendance yet
            ]);
        }
        return $this->_attendance;
    }

    public function attributeLabels()
    {
        return [
        ];
    }
}
