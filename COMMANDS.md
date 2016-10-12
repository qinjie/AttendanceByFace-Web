# Helpful commands

## Intro
- Go to project folder
- Run ```./yii help gii``` to get all commands for generating models, forms, controllers in Yii
- Run ```./yii help gii/model``` to get documentation of how to generate models

## Generate CRUD controller (recommended)

```
./yii gii/crud --controllerClass="api\modules\v1\controllers\TimetableController" --modelClass="common\models\Timetable" --searchModelClass="common\models\search\TimetableSearch" --baseControllerClass="api\components\CustomActiveController"
```

## Generate controller

```
./yii gii/controller --controllerClass="api\modules\v1\controllers\LessonController" --baseClass="api\components\CustomActiveController"
```

## Generate ActiveRecord class

```
./yii gii/model --tableName="attendance" --ns="common\models" --modelClass="Attendance"
```

## Generate attendance for semester

```
./yii attendance/generate --fromDate="YYYY-MM-DD" --toDate="YYYY-MM-DD" --semester="number"
```
