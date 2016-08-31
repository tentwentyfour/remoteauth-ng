<?php

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
        $couldAuthenticate = parent::provideSessionInfo($request);

        if ($couldAuthenticate !== null) {
            return $couldAuthenticate;
        } else {
            // see if we can authenticate the user by looking at the REMOTE_USER
            // variable

            try {
                $userInfo = UserInfo::newFromName($this->getRemoteUsername());
            } catch (\InvalidArgumentException $ex) {
                return null;
            }

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
        return isset($_SERVER['REMOTE_USER']) ?  $_SERVER['REMOTE_USER'] : "";
    }
}
