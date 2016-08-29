<?php

use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;
use MediaWiki\Session\SessionProvider;

class RemoteAuthNG extends SessionProvider {
    const ID = 'remoteuserng-session-provider';

    public function __construct( $params = [] ) {
        parent::__construct();
    }

    /**
     * [provideSessionInfo description]
     * @param  WebRequest $request [description]
     * @return [type]              [description]
     */
    public function provideSessionInfo( WebRequest $request ) {

        // return null when we can't get user info

        $username = $this->getRemoteUsername();
        if (!$username) {
            return null;
        }

        $user = $this->getUserFromSession( $username );
        if ($user === null) {
            return null;
        }

        return new SessionInfo( SessionInfo::MIN_PRIORITY, [
            'provider' => $this,
            'persisted' => true,
            'userInfo' => $user->verified(),
        ] );
    }

    public function persistsSessionId() {
        return true;
    }

    public function canChangeUser() {
        return $this->persistsSessionId();
    }

    public function persistSession( SessionBackend $session, WebRequest $request ) {
    }

    public function unpersistSession( WebRequest $request ) {

    }

    public function immutableSessionCouldExistForUser( $user ) {
        return false;
    }

    public function preventImmutableSessionsForUser( $user ) {
    }

    public function suggestLoginUsername( WebRequest $request ) {
        return $request->getCookie( 'UserName' );
    }

    /**
     * Get the REMOTE_USER environment variable, if it exists
     * @return String representing the REMOTE_USER or an empty string
     */
    public static function getRemoteUsername() {
        if ( isset( $_SERVER['REMOTE_USER'] ) ) {
            return $_SERVER['REMOTE_USER'];
        } else {
            return "";
        }
    }

    /**
     * Gets the user from the current session.
     * @param  String $username The username.
     * @return UserInfo         Object holding data about a session's user.
     */
    public function getUserFromSession( $username ) {
        $username = $this->getRemoteUsername();
        if (!$username) {
            return null;
        }

        return UserInfo::newFromName($username);
    }

    /**
     * Formats the string so MediaWiki won't be sad.
     * @param  String $username The username.
     * @return String           The Canonical username.
     */
    private static function getCanonicalName( $username ) {
        $username = strtolower( $username );
        return ucfirst( $username );
    }
}
