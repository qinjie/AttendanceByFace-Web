<?php

namespace common\helpers;

use common\models\UserToken;
use Yii;

class TokenHelper
{
    const TOKEN_ACTION_ACTIVATE_ACCOUNT = 1;
    const TOKEN_ACTION_RESET_PASSWORD = 2;
    const TOKEN_ACTION_CHANGE_EMAIL = 3;
    const TOKEN_ACTION_ACCESS = 4;
    const TOKEN_ACTION_TRAIN_FACE = 5;

    public static $actions = [
        1 => 'ACTION_ACTIVATE_ACCOUNT',
        2 => 'ACTION_RESET_PASSWORD',
        3 => 'ACTION_CHANGE_EMAIL',
        4 => 'ACTION_ACCESS',
        5 => 'ACTION_TRAIN_FACE',
    ];

    const CACHE_DURATION = 3600; // one hour
    const CACHE_PREFIX = "api-token-";
    const TOKEN_MISSING = -1;
    const TOKEN_EXPIRED = -2;
    const TOKEN_INVALID = -3;

    public static function createUserToken($userId, $action = null)
    {
        $model = new UserToken();
        $model->user_id = $userId;
        $model->token = self::generateToken();
        $interval = 30 * 24 * 60 * 60;
        if ($action == self::TOKEN_ACTION_TRAIN_FACE) $interval = 10 * 60;
        $model->expire_date = date('Y-m-d H:i:s', time() + $interval);
        $model->action = is_null($action) ? self::TOKEN_ACTION_ACCESS : $action;
        $model->title = self::$actions[$model->action];
        $model->ip_address = \Yii::$app->getRequest()->getUserIP();
        if ($model->validate() && $model->save())
            return $model;
        else
            return null;
    }

    /**
     * Is this temporary token still valid? Returns false if token is not found or
     * new token is needed forcing client to re-authenticate.
     *
     * @param string $token
     * @return int $userId if authentication is successful
     */
    public static function authenticateToken($token, $checkExpire = false, $action = null, $ipAddress = null)
    {
        // empty key cannot be authenticated
        if ($token == null || strlen($token) == 0) {
            return self::TOKEN_MISSING;
        }

        // $record = self::lookupCachedToken($token);
        $record = null;
        if ($record == null) {
            // lookup auth token in database
            $params = array('token' => $token);
            if ($action)
                $params['action'] = $action;
            else
                $params['action'] = self::TOKEN_ACTION_ACCESS;
            if ($ipAddress)
                $params['ip_address'] = $ipAddress;

            $record = UserToken::findOne($params);
        }

        // if no such token found
        if ($record == null) {
            return self::TOKEN_INVALID;
        }
        // if need to check whether token has expired.
        $current = time();
        $expire_date = strtotime($record->expire_date);
        if ($checkExpire && $expire_date < $current) {
            self::deleteCachedToken($token);
            UserToken::model()->deleteByPk($record->id);
            return self::TOKEN_EXPIRED;
        }
        self::updateExpire($record);
        self::cacheToken($token, $record);

        return $record->user_id;
    }

    /**
     * Update a expiry time of the token in a UserToken
     * @param UserToken $record
     */
    private static function updateExpire($record)
    {
//        $params = Yii::$app->getParams();
//        $interval = $params['restful_token_expired_seconds'];
        $interval = 30 * 24 * 60 * 60;
        $record->expire_date = date('Y-m-d H:i:s', time() + $interval);
        $record->save(false, array('expire_date'));
    }

    /**
     * Use cache to store token and connected entity record (database columns of fr_api_device table)
     * @param String $token
     * @return UserToken $record
     */
    private static function lookupCachedToken($token)
    {
        if (!isset(Yii::$app->cache)) {
            return null;
        }
        // get token from cache
        $record = Yii::$app->cache->get(self::CACHE_PREFIX . $token);
        return $record;
    }

    private static function deleteCachedToken($token)
    {
        if (isset(Yii::$app->cache)) {
            Yii::$app->cache->delete(self::CACHE_PREFIX . $token);
        }
    }

    private static function cacheToken($token, $record)
    {
        if (isset(Yii::$app->cache)) {
            Yii::$app->cache->set(self::CACHE_PREFIX . $token, $record, self::CACHE_DURATION);
        }
    }

    /**
     * Generate a token of 32 byte
     */
    public static function generateToken($length = 32)
    {
        $security = new \yii\base\Security();
        $token = $security->generateRandomString($length);
        return $token;
    }
}
