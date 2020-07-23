<?php

namespace App;

class ApiCode {
    public const SUCCESS_OK = 200;
    public const SUCCESS_CREATED = 201;
    public const SUCCESS_NO_CONTENT = 204;
    public const REDIRECTION_NOT_MODIFIED = 304;
    public const CLIENT_BAD_REQUEST = 400;
    public const CLIENT_UNAUTHORIZED = 401;
    public const CLIENT_FORBIDDEN = 403;
    public const CLIENT_NOT_FOUND = 404;
    public const CLIENT_METHOD_NOT_ALLOWED = 405;
    public const CLIENT_CONFLICT = 409;
    public const CLIENT_PRECONDITION_FAILED = 412;
    public const SERVER_ERROR = 500;
}
