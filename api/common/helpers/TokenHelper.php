<?php

namespace api\common\helpers;

use api\common\models\UserToken;
use Yii;

class TokenHelper
{
    const CACHE_DURATION = 3600; // one hour
    const CACHE_PREFIX = "api-auth-token-";
    const TOKEN_MISSING = -1;
    const TOKEN_EXPIRED = -2;
    const TOKEN_INVALID = -3;

    public static function createUserToken($userId)
    {
        $model = new UserToken();
        $model->userId = $userId;
        $model->token = self::generateToken();
//        $params = Yii::$app()->getParams();
//        $interval = $params['restful_token_expired_seconds'];
        $interval = 30 * 24 * 60 * 60;
        $model->expire = date('Y-m-d H:i:s', time() + $interval);
        $model->ipAddress = \Yii::$app->getRequest()->getUserIP();
        //UserToken::model()->deleteAllByAttributes(array("userId" => $model->userId, "label" => $model->label, "ipAddress" => $model->ipAddress));
        if ($model->save())
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
    public static function authenticateToken($token, $checkExpire = false, $label = null, $ipAddress = null)
    {
        // empty key cannot be authenticated
        if ($token == null || strlen($token) == 0) {
            return self::TOKEN_MISSING;
        }

        $record = self::lookupCachedToken($token);
        if ($record == null) {
            // lookup auth token in database
            $params = array('token' => $token);
            if ($label)
                $params['label'] = $label;
            if ($ipAddress)
                $params['ipAddress'] = $ipAddress;

            $record = UserToken::findOne($params);
        }

        // if no such token found
        if ($record == null) {
            return self::TOKEN_INVALID;
        }
        // if need to check whether token has expired.
        $current = time();
        $expire = strtotime($record->expire);
        if ($checkExpire && $expire < $current) {
            self::deleteCachedToken($token);
            UserToken::model()->deleteByPk($record->id);
            return self::TOKEN_EXPIRED;
        }

        self::updateExpire($record);
        self::cacheToken($token, $record);

        return $record->userId;
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
        $record->expire = date('Y-m-d H:i:s', time() + $interval);
        $record->save(false, array('expire'));
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