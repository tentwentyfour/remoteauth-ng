<?php

use User;
use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;
use MediaWiki\Session\CookieSessionProvider;
use MediaWiki\Session\SessionManager;

class RemoteAuthNG extends CookieSessionProvider
{
    /**
     * Provide session info for a request
     * @param  WebRequest $request
     * @return SessionInfo
     */
    public function provideSessionInfo(WebRequest $request)
    {
        // load config
        global $wgRemoteAuthNgAutoCreateUser;

        $couldAuthenticate = parent::provideSessionInfo($request);

        if ($couldAuthenticate !== null) {
            return $couldAuthenticate;
        } else {
            // see if we can authenticate the user by looking at the REMOTE_USER
            // variable

            $user = User::newFromName($this->getRemoteUsername());
            if (!$user) {
                return null;
            }

            // create user if not exists
            if (!$user->isLoggedIn()) {
                if ($wgRemoteAuthNgAutoCreateUser) {
                    $user->addToDatabase();
                } else {
                    return null;
                }
            }

            // get user info
            $userInfo = UserInfo::newFromUser($user);

            $sessionId = $this->getCookie($request, $this->params['sessionName'], '');
            $info = [
                'provider' => $this,
                'forceHTTPS' => $this->getCookie($request, 'forceHTTPS', '', false)
            ];
            if (SessionManager::validateSessionId($sessionId)) {
                $info['id'] = $sessionId;
                $info['persisted'] = true;
            }

            $token = $userInfo->getToken();
            $info['userInfo'] = $userInfo->verified();
            $info['persisted'] = true;

            return new SessionInfo($this->priority, $info);
        }
    }

    /**
     * Get the REMOTE_USER environment variable, if it exists
     * @return String representing the REMOTE_USER or an empty string
     */
    public static function getRemoteUsername()
    {
        if (isset($_SERVER['REMOTE_USER'])) {
            return $_SERVER['REMOTE_USER'];
        }
        if (isset($_SERVER['REDIRECT_REMOTE_USER'])) {
            return $_SERVER['REDIRECT_REMOTE_USER'];
        }
        return "";
    }
}
