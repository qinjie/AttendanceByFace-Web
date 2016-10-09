# Helpful Yii commands

## Intro
- Go to project folder
- Run ```./yii help gii``` to get all commands for generating models, forms, controllers in Yii
- Run ```./yii help gii/model``` to get documentation of how to generate models

## Generate API controller

```
./yii gii/crud --controllerClass="api\modules\v1\controllers\TimetableController" --modelClass="common\models\Timetable" --searchModelClass="common\models\search\TimetableSearch" --baseControllerClass="api\components\CustomActiveController"
```

## Generate ActiveRecord class

```
./yii gii/model --tableName="attendance" --ns="common\models" --modelClass="Attendance"
```