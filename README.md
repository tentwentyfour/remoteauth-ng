__DEPRECATED!__

Please note that this extension is deprecated in favor or the extension developed and discussed on the WikiMedia Phabricator instance: https://phabricator.wikimedia.org/T110292


Remote Auth MediaWiki extension
===============================

Integrate MediaWiki with third-party authentication services using the REMOTE_USER environment variable.

Requirements
------------

- MediaWiki >= 1.27.0

Installation
------------

1. Run `composer` inside your MediaWiki `extensions` directory:
    ```
    $ composer require tentwentyfour/remoteauth-ng
    ```

2. Add the following lines at the end of your `LocalSettings.php`:
    ```
    wfloadExtension( 'RemoteauthNg' );
    // enable/disable auto user creation
    $wgRemoteAuthNgAutoCreateUser = false;
    // force remote user
    $wgRemoteAuthNgForceRemoteUser = true;
    ```

3. Sit back and let the magic sink in. :sunglasses:
