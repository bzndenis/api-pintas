<?php

namespace  App\Http\Helper;

class ResponseBuilder
{
    public static function success($status = 200, $message = "", $data = [], $success = true, $is_post = false)
    {
        if ($data) {
            # code...
            return response()->json([
                "status" => $status,
                "success" => $success,
                "message" => $message,
                "total_data" => $is_post ? 0 : count($data),
                "data" => $data
            ], $status);
        } else {
            return response()->json([
                "status" => $status,
                "success" => $success,
                "message" => $message,
                "total_data" => 0,
                "data" => []
            ], $status);
        }
    }
    public static function error($status = "", $error = "", $data = [])
    {
        return [
            "status" => $status,
            "info" => $error,
            "error" => $data
        ];
    }
}
