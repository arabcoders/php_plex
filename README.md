Plex Lazy Scanner
-------------------

The aim of this project is to do partial plex scan instead of doing full scan, this script is mainly for rclone users.

The general idea is by retrieving plex sections and their associated locations. We can monitor changes in rclone cache
via either VFS or the cache backend. After that we then compare the location to our list from plex.

if the filepath matches, then we trigger partial scan, usually within minute or so you could see your updated library.

Configuration.
-------------------

Configuration is done via ENV variables.
 
 `export PP_LOG_TYPE=file; php PHPPlex.php run -vvv`
 
```shell script

# string Where your Rclone is mounted must match what is added into plex.
PP_MEDIA_PATH= '/mnt/gdrive/';
    
# string How is Rclone log stored file or journal?
PP_LOG_TYPE= 'file';

# string Full path to logfile or service name if log is stored in journalctrl.
PP_LOG_LOCATION = '/var/log/rclone.log';

# string Rclone log type (VFS|cache).
PP_LOG_MATCH_TYPE= 'cache';

# bool Whether to use SSL for plex connection.
PP_PLEX_SSL = false;

# int Plex port.
PP_PLEX_PORT = 32400;,

# string Plex host or ip.
PP_PLEX_HOST = 'localhost';

# string Plex Token.
PP_PLEX_TOKEN = 'X-Token-Token',

# string Plex Scanner Command. it will get passed two variables {section}=(int)sectionId {directory}=(string)directory.
PP_SCANNER_CMD = '/usr/lib/plexmediaserver/Plex\ Media\ Scanner --scan --refresh --section {section} --directory {directory}';

# string Those variables  required to trigger plex scan.
PP_SCANNER_ENV_LD = '/usr/lib/plexmediaserver/lib';

# string Those variables  required to trigger plex scan.
PP_SCANNER_ENV_DIR = '/var/lib/plexmediaserver/Library/Application Support';
```

Requirement.
-------------------
* PHP 7.1+.
* PHP curl extension.
* PHP pnctl extension.
* PHP simplexml extension.

Typical rClone config.
----------------------

```ini
[gdrive]
type = drive
scope = drive
token = {}
client_id = 
client_secret = 

[gcache]
type = cache
remote = gdrive:/gdrive
chunk_size = 128M
info_age = 1d
chunk_total_size = 10G
plex_token = 

[gcrypt]
type = crypt
remote = gcache:/crypt
filename_encryption = standard
directory_name_encryption = true
password = 
password2 =

[gvfs]
type = crypt
remote = gdrive:/gdrive/crypt
filename_encryption = standard
directory_name_encryption = true
password = 
password2 = 
```

Typical rclone mount command.
----------------------------

* cache backend base.

```shell script
/usr/bin/rclone mount gcrypt: /mnt/gdrive/ \
--allow-other \
--fast-list \
--buffer-size 0M \
--dir-cache-time 100h \
--attr-timeout 100h \
--log-level INFO \
--log-file /var/log/rclone.log  \
--timeout 1h \
--umask 002 \
--poll-interval 15s \
--read-only
```

* VFS cache based

```shell script
/usr/bin/rclone mount gvfs: /mnt/gdrive/ \
--allow-other \
--fast-list \
--buffer-size 128M \
--vfs-cache-mode minimal \
--vfs-cache-max-age 7h \
--vfs-cache-poll-interval 1m \
--vfs-cache-max-size 10G \
--dir-cache-time 100h \
--attr-timeout 100h \
--syslog \
--log-level INFO \
--timeout 1h \
--umask 002 \
--poll-interval 15s \
--read-only
```

Installation and Usage
----------------------

You can use the script via phpplex.phar or by cloning the repository

```shell script
git clone https://github.com/ArabCoders/php_plex.git /opt/php_plex
```

Assuming you have already modified your edited your ENV, you can run this script in many ways.

* SCREEN

```shell script
# cloned Repo.
screen -S php_plex php /opt/php_plex/app/PHPPlex.php run -vvv

# Phar
screen -S php_plex php /opt/phphar.phar run -vvv
```

*  Systemd service.

```shell script
cp /opt/php_plex/example/php_plex.service /etc/systemd/system/
systemctrl enable php_plex.service
systemctrl start  php_plex.service
```

* supervisor

```shell script
cp ./example/php_plex/php_plex.supervisor.conf /etc/supervisor/conf.d/php_plex.conf
systemctrl restart supervisor

```

What ever you choose, you should run the script in the same user as plex is running on, otherwise the scanner 
would not run.

FAQ
---

* i cant see file changes in VFS log messages.

For VFS based cache you need to run rclone with `---log-level DEBUG` to be able to see file changes notification.

* I am not seeing any library updates.

There could be many reasons, but usually it's one of the following:

1. the script is not running in same user as plex.
2. the script isn't able to read rclone logs.
3. rclone is not configured to the required `--log-level`. 