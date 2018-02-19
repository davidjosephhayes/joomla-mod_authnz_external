# Authenticate 3rd Party Applications with Joomla

The cli script provided here allows Apache mod_authnz_external to authenticate with Joomla's built in authentication mechanisms. This was tested with Joomla 3.8.5, Apache 2.4.18, and Ubuntu 16.04.

## Joomla Configuration

Upload `modauthnzexternal.php` to the `cli` directory in a Joomla installation. Make this file executable (`chmod +x modauthnzexternal.php`).

## Server Configuration

Install mod_authnz_external (`apt install libapache2-mod-authnz-external`) and enable it (`authnz_external`). If you want to use this with a reverse proxy, you will also need proxy and proxy_http and possibly headers. These came with Apache on Ubuntu 16.04, just had to enable them (`a2enmod proxy` `a2enmod proxy_http` `a2enmod headers`). Restart Apache (`systemctl restart apache2`).

## Apache Configuration

Add the following to the bottom of the Apache sites file (before `</VirtualHost>`) (`/etc/apache2/sites-enabled/sitename.tld.conf`) and restart Apache.

```
AddExternalAuth joomla /path/to/joomla/cli/modauthnzexternal.php
SetExternalAuthMethod joomla pipe
```

### Directory

To restrict a directory, add the following to the Apache sites file inside a `<Directory /path/to/restricted/directory>...</Directory>` restart Apache.

```
allow from all
AuthType Basic
AuthName "Joomla"
AuthBasicProvider external
AuthExternal joomla
Require valid-use
```

I believe you can also use the directives in a `.htaccess` inside a directory if your configuration allows it.

### Proxy

Add the following to to the bottom of the sites file.

```
ProxyPass / https://proxied.sitename.tld/
ProxyPassReverse / https://proxied.sitename.tld/
SSLProxyEngine on
<Proxy *>
allow from all
AuthType Basic
AuthName "SeniorsDev"
AuthBasicProvider external
AuthExternal joomla
Require valid-user
# add below if you want to forward the username of who was authenticated to the proxied application
RequestHeader add X-Forwarded-Remote-User %{REMOTE_USER}e #http
RequestHeader add X-Forwarded-Remote-User %{REMOTE_USER}s #https
</Proxy>
```

## Possible Enhancements

Right now any valid Joomla login with authenticate. To segment users, a Joomla component with ACL is needed. The cli script can then no only check if the login is valid but also if the user is authorized with to authenticate via mod_authnz_external.