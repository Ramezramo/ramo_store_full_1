<?php

namespace App\Helpers;

use App\Constants\AppConstants;

class ResponseHandlerRam
{
    /**
     * Whether debugging mode is enabled.
     *
     * @var bool
     */
    protected static $debugging = AppConstants::DEBUG_MODE;

    /**
     * Set debugging mode.
     *
     * @param bool $debugging
     * @return void
     */
    public static function setDebugging(bool $debugging)
    {
        self::$debugging = $debugging;
    }

    /**
     * Return a standardized success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, string $message = 'Operation successful', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a standardized error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @param bool $forceMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message = 'An error occurred', int $statusCode = 400, $errors = null, bool $forceViewMessageDetails = false)
    {
        $errorData = self::$debugging ? $errors : null;
        $displayMessage = (self::$debugging || $forceViewMessageDetails) ? $message : 'An error occurred';

        return response()->json([
            'success' => false,
            'data' => $displayMessage,
            'errors' => $errorData,
        ], $statusCode);
    }

    /**
     * Return a validation error response.
     *
     * @param \Illuminate\Support\MessageBag|array $errors
     * @param string $message
     * @param int $statusCode
     * @param bool $forceMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public static function validationError($errors, string $message = 'Validation failed', int $statusCode = 422, bool $forceMessage = false)
    {
        return self::error($message, $statusCode, $errors, $forceMessage);
    }

    /**
     * Return a not found error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param bool $forceMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public static function notFound(string $message = 'Resource not found', int $statusCode = 404, bool $forceMessage = false)
    {
        return self::error($message, $statusCode, null, $forceMessage);
    }

    /**
     * Return an unauthorized error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param bool $forceMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized access', int $statusCode = 401, bool $forceMessage = false)
    {
        return self::error($message, $statusCode, null, $forceMessage);
    }
}