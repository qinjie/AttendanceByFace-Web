<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\common\models\Lecturer;
use api\modules\v1\models\Timetable;
use api\modules\v1\models\Lesson;
use api\common\models\Student;
use api\common\components\AccessRule;
use api\modules\v1\controllers\TimetableController;

use yii\rest\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class LessonController extends CustomActiveController {

    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['test'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'except' => ['test'],
            'rules' => [
                [   
                    'actions' => ['detail'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT],
                ],
                [
                    'actions' => ['list-class-section-for-lecturer'],
                    'allow' => true,
                    'roles' => [User::ROLE_LECTURER],
                ]
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        return $behaviors;
    }

    public function actionListClassSectionForLecturer() {
        $userId = Yii::$app->user->identity->id;
        $lecturer = Lecturer::findOne(['user_id' => $userId]);
        if(!$lecturer)
            throw new BadRequestHttpException('No lecturer with given user id');

        $listClassSection = Yii::$app->db->createCommand('
            select DISTINCT class_section
            from (select *
                from timetable
                where lecturer_id = :lecturerId) as a1 join lesson
            on lesson.id = lesson_id
            where semester = :semester
        ')
        ->bindValue(':lecturerId', $lecturer->id)
        ->bindValue(':semester', TimetableController::TEST_DEFAULT_SEMESTER)
        ->queryAll();

        $func = function($val) {
            return $val['class_section'];
        };
        $listClassSection = array_map($func, $listClassSection);
        return $listClassSection;
    }

    public function actionDetail($id) {
        $query = Yii::$app->db->createCommand('
            select lesson.id,
                   semester,
                   subject_area,
                   catalog_number,
                   class_section,
                   component,
                   facility,
                   weekday,
                   start_time,
                   end_time,
                   venue.location,
                   venue.name
             from (lesson left join venue on lesson.venue_id = venue.id) 
             where lesson.id = :lesson_id
        ')
        ->bindValue(':lesson_id', $id);
        return $query->queryOne();
    }

    public function actionTest() {
        return Lesson::find()
            ->with('venue')
            // ->select([
            //     'subject_area',
            //     'class_section',
            //     'location',
            //     'name'
            // ])
            ->one();
    }

    // public function afterAction($action, $result)
    // {
    //     $result = parent::afterAction($action, $result);
    //     // your custom code here
    //     return [
    //         'status' => 200,
    //         'data' => $result,
    //     ];
    // }
}
