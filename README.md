Remote Auth MediaWiki extension
===============================

*Work in Progress*

Integrate MediaWiki with third-party authentication services using the REMOTE_USER environment variable.

Requirements
------------

- MediaWiki >= 1.27.0

Installation
------------

1. Clone this repository inside your MediaWiki `extensions` directory:
```
    $ git clone https://github.com/tentwentyfour/remoteauth-ng
```

2. Add this line at the end of your `LocalSettings.php`:
```
    wfLoadExtension('remoteauth-ng');
```

3.
