<?php

class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        // Dynamic CORS - Allow any localhost port
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        } else {
            header('Access-Control-Allow-Origin: http://localhost');
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        
        echo json_encode($data);
        exit;
    }

    public static function success($message = 'Success', $data = null, $statusCode = 200) {
        $response = ['status' => 'success', 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response, $statusCode);
    }

    public static function info($message = 'Info', $data = null, $statusCode = 200) {
        $response = ['status' => 'info', 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response, $statusCode);
    }

    public static function error($message = 'Error', $statusCode = 400, $errors = null) {
        $response = ['status' => 'error', 'message' => $message];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        self::json($response, $statusCode);
    }

    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }

    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }

    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }

    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
}

?>