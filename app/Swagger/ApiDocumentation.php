<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Lakhtar News API"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local API Server"
 * )
 *
 * @OA\Server(
 *     url="https://news.flipcodesolutions.com/api",
 *     description="Production API Server"
 * )
 * 
 * @OA\Server(
 *     url="https://lakhtarnewsupdate.in/api",
 *     description="Live API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum",
 *     description="Enter the Bearer token received from POST /verify-otp"
 * )
 */
final class ApiDocumentation {}
