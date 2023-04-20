<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;

class HttpStatus
{
    const BAD_REQUEST = ['code' => Response::HTTP_BAD_REQUEST, 'message' => 'bad_request'];
    const NOT_FOUND = ['code' => Response::HTTP_NOT_FOUND, 'message' => 'not_found'];
    const INTERNAL_SERVER_ERROR = ['code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'server_error'];
    const FORBIDDEN = ['code' => Response::HTTP_FORBIDDEN, 'message' => 'forbidden'];
}
