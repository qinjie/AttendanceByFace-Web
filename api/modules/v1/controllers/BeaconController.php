<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\modules\v1\models\Timetable;
use api\modules\v1\models\Lesson;
use api\common\models\Student;
use api\common\components\AccessRule;

use yii\rest\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class BeaconController extends CustomActiveController {

    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'rules' => [
                [   
                    'actions' => ['check'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT],
                ],
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'check' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionCheck() {
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $timetable_id = $bodyParams['timetable_id'];
        $uuid = $bodyParams['uuid'];
        $major = $bodyParams['major'];
        $minor = $bodyParams['minor'];
        $userId = Yii::$app->user->identity->id;

        $query = Yii::$app->db->createCommand('
            select venue.id as venue_id,
                   beacon_id 
             from timetable join lesson on timetable.lesson_id = lesson.id
             join venue on lesson.venue_id = venue.id
             join venue_beacon on venue.id = venue_beacon.venue_id
             join beacon on beacon.id = venue_beacon.beacon_id
             join student on timetable.student_id = student.id
             where beacon.major = :major and 
                   beacon.minor = :minor and 
                   beacon.uuid = :uuid and 
                   timetable.id = :timetable_id and 
                   student.user_id = :user_id 
        ')
        ->bindValue(':major', $major)
        ->bindValue(':minor', $minor)
        ->bindValue(':uuid', $uuid)
        ->bindValue(':timetable_id', $timetable_id)
        ->bindValue(':user_id', $userId);
        $result = $query->queryOne();
        if (!$result)
            throw new BadRequestHttpException('Invalid beacon');
        return $result;
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        // your custom code here
        return [
            'status' => 200,
            'data' => $result,
        ];
    }
}
