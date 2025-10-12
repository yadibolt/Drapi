<?php

// project globals
const D9M7_PROJECT_NAME = 'Drift Eleven';
const D9M7_PROJECT_ID = 'drift_eleven';

// directory discovery
const D9M7_CORE_ROUTES_DIR = __DIR__ . '\src\Core\Routes';

// configuration
const D9M7_CONFIG_KEY = 'drift_eleven.settings';
const D9M7_LOGGER_KEY = 'drift_eleven.logger';
const D9M7_CACHE_KEY = 'drift_eleven.cache';
const D9M7_CACHE_BIN_KEY = 'drift_eleven';


// v2
const LOGGER_DEBUG = true;
const LOGGER_CHANNEL_DEFAULT = 'de_channel';
const LOGGER_LEVEL_DEFAULT = 'debug';

const CACHE_BIN_KEY_DEFAULT = 'de_bin';
const CACHE_TAGS_BIN_KEY_DEFAULT = 'de_tags_bin';
const CACHE_DURATION_DEFAULT = 3600;

const JWT_CONFIG_NAME_DEFAULT = 'de_jwt_config';
const JWT_SECRET_DEFAULT = 'change_me_please';
const JWT_ACCESS_TOKEN_TTL_DEFAULT = 3600;
const JWT_REFRESH_TOKEN_TTL_DEFAULT = 604800;
const JWT_RESET_PASSWORD_TTL_DEFAULT = 900;
const JWT_SIGNATURE_HASH_DEFAULT = 'de-994993lke91049m23';
const JWT_ISSUER_DEFAULT = 'drift_eleven.com';

const HTTP_HEADER_CACHE_NAME_DEFAULT = 'X-DriftEleven-Cache';
const HTTP_HEADER_CACHED_DEFAULT = 'CACHED';
const HTTP_HEADER_NOT_CACHED_DEFAULT = 'NOT-CACHED';
const HTTP_HEADER_CACHE_HIT_NAME_DEFAULT = 'X-DriftEleven-Cache-Hit';
const HTTP_HEADER_CACHE_HIT_DEFAULT = 'HIT';
const HTTP_HEADER_CACHE_MISS_DEFAULT = 'MISS';
const HTTP_HEADER_CACHEABLE_NAME_DEFAULT = 'X-DriftEleven-Cacheable';
const HTTP_HEADER_CACHEABLE_DEFAULT = 'CACHEABLE';
const HTTP_HEADER_NOT_CACHEABLE_DEFAULT = 'NOT-CACHEABLE';

const ROUTE_CONFIG_NAME_DEFAULT = 'de_routes_config';
const ROUTE_CACHE_TOKEN_ADDER_DEFAULT = '--add-token:';

const SESSION_TABLE_NAME_DEFAULT = 'drift_eleven_sessions';
const SESSION_RECORD_TTL_DEFAULT = 604800 * 2;

const SUBJECT_RESET_PASSWORD_TABLE_NAME_DEFAULT = 'drift_eleven_password_resets';

