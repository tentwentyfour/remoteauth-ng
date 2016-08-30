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
        $sessionId = $this->getCookie($request, $this->params['sessionName'], '');
        $info = [
            'provider' => $this,
            'forceHTTPS' => $this->getCookie($request, 'forceHTTPS', '', false)
        ];
        if (SessionManager::validateSessionId($sessionId)) {
            $info['id'] = $sessionId;
            $info['persisted'] = true;
        }

        list($userId, $userName, $token) = $this->getUserInfoFromCookies($request);
        if ($userId !== null) {
            try {
                $userInfo = UserInfo::newFromId($userId);
            } catch (\InvalidArgumentException $ex) {
                return null;
            }

            // Sanity check
            if ($userName !== null && $userInfo->getName() !== $userName) {
                $this->logger->warning(
                    'Session "{session}" requested with mismatched UserID and UserName cookies.',
                    [
                        'session' => $sessionId,
                        'mismatch' => [
                            'userid' => $userId,
                            'cookie_username' => $userName,
                            'username' => $userInfo->getName(),
                        ],
                    ]
                );
                return null;
            }

            if ($token !== null) {
                if (!hash_equals($userInfo->getToken(), $token)) {
                    $this->logger->warning(
                        'Session "{session}" requested with invalid Token cookie.',
                        [
                            'session' => $sessionId,
                            'userid' => $userId,
                            'username' => $userInfo->getName(),
                        ]
                    );
                    return null;
                }
                $info['userInfo'] = $userInfo->verified();
                $info['persisted'] = true; // If we have user+token, it should be
            } elseif (isset( $info['id'])) {
                $info['userInfo'] = $userInfo;
            } else {
                // No point in returning, loadSessionInfoFromStore() will
                // reject it anyway.
                return null;
            }
        } elseif (strlen($this->getRemoteUsername()) > 0) {
            try {
                $userInfo = UserInfo::newFromName($this->getRemoteUsername());
            } catch (\InvalidArgumentException $ex) {
                return null;
            }

            $token = $userInfo->getToken();
            $info['userInfo'] = $userInfo->verified();
            $info['persisted'] = true;

        } elseif (isset( $info['id'])) {
            // No UserID cookie, so insist that the session is anonymous.
            // Note: this event occurs for several normal activities:
            // * anon visits Special:UserLogin
            // * anon browsing after seeing Special:UserLogin
            // * anon browsing after edit or preview
            $this->logger->debug(
                'Session "{session}" requested without UserID cookie',
                [
                    'session' => $info['id'],
                ]
            );
            $info['userInfo'] = UserInfo::newAnonymous();
        } else {
            // No session ID and no user is the same as an empty session, so
            // there's no point.
            return null;
        }
        
        return new SessionInfo($this->priority, $info);
    }

    /**
     * Get the REMOTE_USER environment variable, if it exists
     * @return String representing the REMOTE_USER or an empty string
     */
    public static function getRemoteUsername()
    {
        if (isset( $_SERVER['REMOTE_USER'])) {
            return $_SERVER['REMOTE_USER'];
        } else {
            return "";
        }
    }
}
