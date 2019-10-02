<?php

return [
    /**
     * @var string Where your Rclone is mounted must match what is added into plex.
     */
    'media_path' => (string)env('PP_MEDIA_PATH', '/mnt/gdrive/'),

    /**
     * @var int log offset. Useful for debugging.
     */
    'log_offset' => (int)env('PP_LOG_OFFSET', 0),

    /**
     * @var string how is Rclone log stored file or journal?
     */
    'log_type' => (string)env('PP_LOG_TYPE', 'file'),

    /**
     * @var string Full path to logfile or service name if log is stored in journalctrl.
     */
    'log_location' => (string)env('PP_LOG_LOCATION', '/var/log/rclone.log'),

    /**
     * @var string Rclone log type (VFS|cache).
     */
    'log_match_type' => (string)env('PP_LOG_MATCH_TYPE', 'cache'),

    /**
     * @var string Match Rclone cache backend filename string.
     */
    'log_match_cache' => env('PP_LOG_MATCH_BACKEND', '/: (.+): received cache expiry notification/'),

    /**
     * @var bool warning, symfony/process dont dont well with piped commands, you will have zombie processes.
     */
    'log_cmd_grep' => env('PP_LOG_CMD_GREP', false),

    /**
     * @var string filter lines to reduce memory.
     */
    'log_cache_grep' => 'received cache expiry notification',

    /**
     * @var string Match Rclone VFS cache filename string.
     */
    'log_match_vfs' => env('PP_LOG_MATCH_VFS', '/: (.+): >Attr:/'),

    /**
     * @var string filter lines to reduce memory.
     */
    'log_vfs_grep' => '>Attr:',

    /**
     * @var bool whether to use SSL for plex connection.
     */
    'plex_ssl' => (bool)env('PP_PLEX_SSL', false),

    /**
     * @var int Plex Port.
     */
    'plex_port' => (int)env('PP_PLEX_PORT', 32400),

    /**
     * @var string Plex host or ip.
     */
    'plex_host' => (string)env('PP_PLEX_HOST', 'localhost'),

    /**
     * @var string Plex Token.
     * @link https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token/
     */
    'plex_token' => (string)env('PP_PLEX_TOKEN', 'X-Token-Token'),

    /**
     * @var string Allowed File extensions.
     */
    'files_allow' => '/\.(mkv|mpv|idx|sub|mp4|srt|ogg|rmvb|mpeg4|avi|mpeg)/',

    /**
     * @var string Disallowed File extensions.
     */
    'files_exclude' => '.xattr|.filename|.metadata',

    'scanner' => [

        /**
         * @var string Plex Scanner Command. it will get passed two variables {section}=(int)sectionId {directory}=(string)directory.
         */
        'cmd' => (string)env('PP_SCANNER_CMD',
            '/usr/lib/plexmediaserver/Plex\ Media\ Scanner --scan --refresh --section {section} --directory {directory}'),

        /**
         * @var array those ENV are required to trigger plex scan.
         */
        'env' => [
            /**
             * @var string
             */
            'LD_LIBRARY_PATH' => (string)env('PP_SCANNER_ENV_LD', '/usr/lib/plexmediaserver/lib'),

            /**
             * @var string
             */
            'PLEX_MEDIA_SERVER_APPLICATION_SUPPORT_DIR' => (string)env('PP_SCANNER_ENV_DIR',
                '/var/lib/plexmediaserver/Library/Application Support'),
        ],
    ]
];