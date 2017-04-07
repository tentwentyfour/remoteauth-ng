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
    ```

3. Sit back and let the magic sink in. :sunglasses:
