<?php

namespace App\Http\Helper;

class ResponseBuilder
{
    public static function success($status = 200, $message = "", $data = null, $success = true, $is_post = false)
    {
        if ($data !== null) {
            return response()->json([
                "status" => $status,
                "success" => $success,
                "message" => $message,
                "total_data" => $is_post ? 0 : (is_array($data) || $data instanceof \Countable ? count($data) : 1),
                "data" => $data
            ], $status);
        } else {
            return response()->json([
                "status" => $status,
                "success" => $success,
                "message" => $message
            ], $status);
        }
    }

    public static function error($status = 400, $message = "")
    {
        return response()->json([
            "status" => $status,
            "success" => false,
            "message" => $message
        ], $status);
    }
}
