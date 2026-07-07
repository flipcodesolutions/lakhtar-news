<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cms;
use App\Models\User;
use App\Models\UserBookmark;
use App\Models\UserFavoriteCategory;
use App\Models\WatchHistory;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/send-otp",
     *     summary="Send OTP to a mobile number",
     *     description="Sends a 4-digit OTP to the provided mobile number and stores it in cache for 5 minutes.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 description="10-digit mobile number",
     *                 example="9999999999"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OTP sent successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The phone field is required."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"phone": {"The phone must be 10 digits."}}
     *             )
     *         )
     *     )
     * )
     */
    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|digits:10'
            ]);

            $mobile = $request->mobile;

            if (
                $mobile == '9999999999' || $mobile == '9316130308'
                || $mobile == '9687574999' || $mobile == '8758585996'
                || $mobile == '1818181818' || $mobile == '1111111111'
                || $mobile == '2222222222' || $mobile == '3333333333'
                || $mobile == '4444444444' || $mobile == '5555555555'
                || $mobile == '6666666666' || $mobile == '7777777777'
                || $mobile == '8888888888'
            ) {

                $otp = 1234;

                Cache::put('otp_' . $mobile, [
                    'mobile' => $mobile,
                    'otp' => $otp,
                ], now()->addMinutes(5));

                return Util::getSuccessMessage('OTP sent successfully.');
            }
            $otp = random_int(1000, 9999);
            $message = "Dear member, OTP forMindful Youth Program Registration is {$otp}. Do not share it with anyone -CCCTST";

            $data = array(
                'mobile' => "9879301004",
                "pass" => urlencode(env('OTP_API_KEY')),
                "senderid" => "CCCTST",
                "to" => $mobile,
                "msg" => $message,
            );

            // Send the POST request with cURL
            $ch = curl_init('http://smsmaster.bhavanisoftware.com/smsstatuswithid.aspx');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);


            Cache::put('otp_' . $mobile, [
                'mobile' => $mobile,
                'otp' => $otp,
            ], now()->addMinutes(5));

            return Util::getSuccessMessage('OTP sent successfully.');
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/verify-otp",
     *     summary="Verify OTP for a mobile number",
     *     description="Verifies the OTP sent to the mobile number. Returns a token for registered users or indicates new user status.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile","otp"},
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 description="10-digit mobile number",
     *                 example="9999999999"
     *             ),
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 description="6-digit OTP",
     *                 example="1234"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="success"),
     *                     @OA\Property(property="message", type="string", example="OTP verified for unregistered user"),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="isNewUser", type="boolean", example=true),
     *                         @OA\Property(property="mobile", type="string", example="9876543210")
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="success"),
     *                     @OA\Property(property="message", type="string", example="OTP verified for registered user"),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="isNewUser", type="boolean", example=false),
     *                         @OA\Property(property="user", type="object"),
     *                         @OA\Property(property="token", type="string", example="plainTextToken123")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The mobile field is required."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"phone": {"The phone must be 10 digits."}, "otp": {"The otp must be 6 digits."}}
     *             )
     *         )
     *     )
     * )
     */

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|digits:10',
                'otp' => 'required|digits:4',
                'fcm_token' => 'nullable|string',
            ]);

            $mobile = $request->mobile;

            $cacheData = Cache::get('otp_' . $mobile);

            if (!$cacheData) {
                return Util::getErrorMessage('OTP expired or not found.');
            }

            if ($mobile == $cacheData['mobile'] && $request->otp == $cacheData['otp']) {
                $user = User::where('mobile', $mobile)->first();

                if (!$user) {
                    Cache::forget('otp_' . $mobile);

                    return Util::getSuccessMessage('OTP verified for unregistered user', [
                        'isNewUser' => true,
                        'mobile' => $mobile
                    ]);
                } else {
                    if ($request->filled('fcm_token')) {
                        app(\App\Services\FcmTokenService::class)->syncToken($user, $request->fcm_token);
                    }

                    $token = $user->createToken('token')->plainTextToken;
                    Cache::forget('otp_' . $mobile);
                    return Util::getSuccessMessage('OTP verified for registered user', [
                        'isNewUser' => false,
                        'user' => $user,
                        'token' => $token
                    ]);
                }
            } else {
                return Util::getErrorMessage('Invalid OTP or mobile number.');
            }
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="User Registration",
     *     description="Register a new user with selected interest categories.",
     *     operationId="registerUser",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile","name","email","language","interest"},
     *
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 example="9876543210"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="john@example.com"
     *             ),
     *             @OA\Property(
     *                 property="language",
     *                 type="string",
     *                 enum={"eng","hin","guj"},
     *                 example="eng"
     *             ),
     *             @OA\Property(
     *                 property="interest",
     *                 type="array",
     *                 description="Array of category IDs",
     *                 @OA\Items(
     *                     type="integer",
     *                     example=1
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User registered successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="mobile", type="string", example="9876543210"),
     *                     @OA\Property(property="language", type="string", example="eng"),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         format="date-time"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        try {

            $request->validate([
                'mobile' => 'required|digits:10|unique:users,mobile',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'language' => 'required|in:eng,hin,guj',
                'interest' => 'required|array|min:1',
                'interest.*' => 'exists:categories,id',
                'fcm_token' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'mobile' => $request->mobile,
                'name' => $request->name,
                'email' => $request->email,
                'language' => $request->language,
                'role' => 'user',
                'password' => Hash::make('123456'),
                'fcm_token' => $request->fcm_token,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            foreach ($request->interest as $categoryId) {
                UserFavoriteCategory::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId,
                ]);
            }

            DB::commit();

            if ($request->filled('fcm_token')) {
                app(\App\Services\FcmTokenService::class)->syncToken($user, $request->fcm_token);
            }

            return Util::getSuccessMessage('User registered successfully.', [
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/profile",
     *     tags={"User"},
     *     summary="Get User Profile",
     *     description="Retrieve the authenticated user's profile information.",
     *     operationId="getProfile",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User profile retrieved successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="mobile", type="string", example="9876543210"),
     *                     @OA\Property(
     *                         property="language",
     *                         type="string",
     *                         enum={"eng","hin","guj"},
     *                         example="eng"
     *                     ),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2026-06-08T12:00:00.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2026-06-08T12:00:00.000000Z"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     )
     * )
     */

    public function getProfile()
    {
        $user = Auth::user();

        $language = $user->language;
        $message = match ($language) {
            'eng' => 'User profile retrieved successfully.',
            'hin' => 'उपयोगकर्ता प्रोफ़ाइल सफलतापूर्वक प्राप्त कर ली गई।',
            'guj' => 'વપરાશકર્તા પ્રોફાઇલ સફળતાપૂર્વક મેળવી.',
        };
        return Util::getSuccessMessage($message, [
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/profile",
     *     summary="Update profile",
     *     description="Updates the authenticated user profile. Use POST with multipart/form-data for profile image upload.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "email", "language", "mobile"},
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="10-digit mobile number",
     *                     example="9876543210"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="User name",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="User email",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="language",
     *                     type="string",
     *                     description="User language",
     *                     example="guj"
     *                 ),
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile image file (jpeg, png, jpg, gif, webp - max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Profile updated successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The email field is required."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"email": {"The email field is required."}}
     *             )
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/update-profile",
     *     summary="Update profile",
     *     description="Updates the authenticated user profile with the provided information.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "email", "language", "mobile"},
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="10-digit mobile number",
     *                     example="9876543210"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="User name",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="User email",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="language",
     *                     type="string",
     *                     description="User language",
     *                     example="guj"
     *                 ),
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile image file (jpeg, png, jpg, gif, webp - max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Profile updated successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The email field is required."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"email": {"The email field is required."}}
     *             )
     *         )
     *     )
     * )
     */

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return Util::getErrorMessage('Unauthenticated.');
            }

            $request->validate([
                'mobile' => 'required|digits:10|unique:users,mobile,' . $user->id,
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'language' => 'required|string|max:255',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'fcm_token' => 'nullable|string',
            ]);

            $message = match ($request->language) {
                'hin' => 'प्रोफ़ाइल सफलतापूर्वक अपडेट की गई.',
                'guj' => 'પ્રોફાઇલ સફળતાપૂર્વક અપડેટ થઈ.',
                default => 'Profile updated successfully',
            };

            $user->mobile = $request->mobile;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->language = $request->language;

            if ($request->filled('fcm_token')) {
                app(\App\Services\FcmTokenService::class)->syncToken($user, $request->fcm_token);
            }

            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    $oldPath = public_path($user->profile_image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                if (! File::exists(public_path('profile'))) {
                    File::makeDirectory(public_path('profile'), 0755, true);
                }

                $profileImage = $request->file('profile_image');
                $profileImageName = time() . '_' . Str::random(10) . '.' . $profileImage->getClientOriginalExtension();
                $profileImage->move(public_path('profile'), $profileImageName);
                $user->profile_image = 'profile/' . $profileImageName;
            }

            $user->save();

            return Util::getSuccessMessage($message, [
                'user' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/change-language",
     *     tags={"User"},
     *     summary="Change User Language",
     *     description="Update the authenticated user's preferred language.",
     *     operationId="changeLanguage",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"language"},
     *             @OA\Property(
     *                 property="language",
     *                 type="string",
     *                 enum={"eng","hin","guj"},
     *                 example="guj",
     *                 description="User preferred language"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Language changed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Language changed successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="mobile", type="string", example="9876543210"),
     *                     @OA\Property(property="language", type="string", example="guj"),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The selected language is invalid."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */


    public function changeLanguage(Request $request)
    {
        try {
            $request->validate([
                'language' => 'required|in:eng,hin,guj',
            ]);

            $language = Auth::user()->language ?? 'eng';

            $message = match ($language) {
                'hin' => 'भाषा सफलतापूर्वक बदली गई.',
                'guj' => 'ભાષા સફળતાપૂર્વક બદલાઈ.',
                default => 'Language changed successfully.',
            };

            $user = User::find(Auth::user()->id);
            $user->language = $request->language;
            $user->save();

            return Util::getSuccessMessage($message, [
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    // my interest

    /**
     * @OA\Get(
     *     path="/my-interest",
     *     summary="Get My Interest Categories",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="My interests fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="My interests fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="category", type="string", example="Politics")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getMyInterest()
    {
        try {

            $language = Auth::user()?->language ?? 'guj';

            $message = match ($language) {
                'hin' => 'मेरी रुचि सफलतापूर्वक प्राप्त हो गया।',
                'guj' => 'મારી રુચિ સફળતાપૂર્વક મળ્યો.',
                default => 'My interest fetched successfully.',
            };

            $categoryColumn = match ($language) {
                'hin' => 'categoryHindi',
                'guj' => 'categoryGujarati',
                default => 'categoryEnglish',
            };

            $categories = UserFavoriteCategory::with('category')
                ->where('user_id', Auth::id())
                ->get()
                ->map(function ($item) use ($categoryColumn) {
                    return [
                        'id' => $item->category?->id,
                        'category' => $item->category?->$categoryColumn,
                    ];
                });

            return Util::getSuccessMessage($message, [
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/update-my-interest",
     *     tags={"User"},
     *     summary="Update My Interests",
     *     description="Update the authenticated user's favorite categories/interests.",
     *     operationId="updateMyInterest",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"categories"},
     *
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 description="Array of category IDs",
     *                 @OA\Items(
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 example={1,2,3}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="My interest updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="My interest updated successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items()
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The categories field is required."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */
    public function updateMyInterest(Request $request)
    {
        $language = Auth::user()->language ?? 'eng';

        $message = match ($language) {
            'hin' => 'मेरी रुचि सफलतापूर्वक अपडेट हो गई।',
            'guj' => 'મારી રુચિ સફળતાપૂર્વક અપડેટ થઈ',
            default => 'My interest updated successfully',
        };
        try {
            $request->validate([
                'categories' => 'required|array',
            ]);

            $user = User::find(Auth::user()->id);
            $user->favoriteCategories()->sync($request->categories);

            return Util::getSuccessMessage('My interest updated successfully');
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    // bookmark APIs

    /**
     * @OA\Get(
     *     path="/my-bookmarks",
     *     tags={"Bookmarks"},
     *     summary="Get My Bookmarks",
     *     description="Retrieve all bookmarked news for the authenticated user.",
     *     operationId="getMyBookmarks",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bookmarks fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="My bookmarks"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="bookmarks",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="user_id",
     *                             type="integer",
     *                             example=5
     *                         ),
     *                         @OA\Property(
     *                             property="news_id",
     *                             type="integer",
     *                             example=10
     *                         ),
     *                         @OA\Property(
     *                             property="created_at",
     *                             type="string",
     *                             format="date-time"
     *                         ),
     *                         @OA\Property(
     *                             property="updated_at",
     *                             type="string",
     *                             format="date-time"
     *                         ),
     *
     *                         @OA\Property(
     *                             property="news",
     *                             type="object",
     *                             @OA\Property(
     *                                 property="id",
     *                                 type="integer",
     *                                 example=10
     *                             ),
     *                             @OA\Property(
     *                                 property="title",
     *                                 type="string",
     *                                 example="Breaking News Title"
     *                             ),
     *                             @OA\Property(
     *                                 property="slug",
     *                                 type="string",
     *                                 example="breaking-news-title"
     *                             ),
     *                             @OA\Property(
     *                                 property="image",
     *                                 type="string",
     *                                 example="news/news.jpg"
     *                             ),
     *                             @OA\Property(
     *                                 property="news_type",
     *                                 type="string",
     *                                 example="breaking"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */
    public function getMyBookmarks()
    {
        $language = Auth::user()->language ?? 'eng';

        $message = match ($language) {
            'hin' => 'मेरे बुकमार्क्स',
            'guj' => 'મારા બુકમાર્ક્સ',
            default => 'My bookmarks',
        };
        try {
            $bookmarks = UserBookmark::where('user_id', Auth::id())->with('news.media')->orderBy('id', 'desc')->get();

            return Util::getSuccessMessage($message, [
                'bookmarks' => $bookmarks
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/add-bookmark-news",
     *     tags={"Bookmarks"},
     *     summary="Add Bookmark News",
     *     description="Bookmark a news article for the authenticated user.",
     *     operationId="addBookmarkNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"news_id"},
     *             @OA\Property(
     *                 property="news_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the news to bookmark"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="News bookmarked successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="News bookmarked successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items()
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The news id field is required."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */
    public function addBookmarkNews(Request $request)
    {
        $language = Auth::user()->language ?? 'eng';

        $message = match ($language) {
            'hin' => 'समाचार सफलतापूर्वक बुकमार्क किया गया',
            'guj' => 'સમાચાર સફળતાપૂર્વક બુકમાર્ક કર્યા',
            default => 'News bookmarked successfully',
        };

        try {
            $request->validate([
                'news_id' => 'required|integer|exists:news,id',
            ]);

            $user = Auth::user();

            $bookmark = $user->userBookmarks()->firstOrCreate([
                'news_id' => $request->news_id,
            ]);

            if (! $bookmark->wasRecentlyCreated) {
                return Util::getSuccessMessage('News already bookmarked');
            }

            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/remove-bookmark/{id}",
     *     tags={"Bookmarks"},
     *     summary="Remove Bookmark News",
     *     description="Remove a bookmarked news article for the authenticated user.",
     *     operationId="removeBookmarkNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"news_id"},
     *             @OA\Property(
     *                 property="news_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the bookmarked news to remove"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="News bookmark successfully removed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="News bookmark successfully removed"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items()
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The news id field is required."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */
    public function removeBookmarkNews($id)
    {
        try {
            $language = Auth::user()->language ?? 'eng';

            $message = match ($language) {
                'hin' => 'न्यूज़ बुकमार्क सफलतापूर्वक हटा दिया गया।',
                'guj' => 'સમાચાર બુકમાર્ક સફળતાપૂર્વક દૂર કર્યા',
                default => 'News bookmark successfully removed',
            };

            $deleted = Auth::user()->userBookmarks()
                ->where(function ($query) use ($id) {
                    $query->where('news_id', $id)
                        ->orWhere('id', $id);
                })
                ->delete();

            if (! $deleted) {
                return Util::getErrorMessage('Bookmark not found.');
            }

            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }
    /**
     * @OA\Get(
     *     path="/watch-histories",
     *     summary="Get User Watch Histories",
     *     description="Retrieve all watch histories for the authenticated user.",
     *     operationId="getWatchHistories",
     *     tags={"Watch Histories"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         description="Page number",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=true,
     *         description="Number of records per page",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Watch histories retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Watch histories retrieved successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example=5
     *                     ),
     *                     @OA\Property(
     *                         property="video_id",
     *                         type="integer",
     *                         example=12
     *                     ),
     *                     @OA\Property(
     *                         property="watched_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2026-06-20T10:30:00Z"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function getWatchHistories(Request $request)
    {
        $language = Auth::user()->language ?? 'eng';

        $message = match ($language) {
            'hin' => 'वॉच हिस्ट्री सफलतापूर्वक प्राप्त कर ली गई।',
            'guj' => 'જુઓ ઇતિહાસ સફળતાપૂર્વક મેળવે છે',
            default => 'Watch histories retrieved successfully',
        };

        $request->validate([
            'page' => 'required|integer|min:1|max:100',
            'per_page' => 'required|integer|min:1|max:100',
        ]);

        $user = User::find(Auth::user()->id);
        $watchHistories = $user->watchHistories()->active()->get();

        return Util::getSuccessMessage($message, $watchHistories);
    }

    /**
     * @OA\Post(
     *     path="/add-watch-history",
     *     summary="Add Watch History",
     *     description="Add a news item to the authenticated user's watch history.",
     *     operationId="addWatchHistory",
     *     tags={"Watch Histories"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"news_id"},
     *             @OA\Property(
     *                 property="news_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the news item"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Watch history successfully added",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Watch history successfully added"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addWatchHistory(Request $request)
    {
        try {
            $language = Auth::user()->language ?? 'eng';

            $message = match ($language) {
                'hin' => 'वॉच हिस्ट्री जानकर ली गई।',
                'guj' => 'જુઓ ઇતિહાસ સફળતાપૂર્વક મેળવે છે',
                default => 'Watch history successfully added',
            };

            $user = User::find(Auth::user()->id);
            $user->watchHistories()->updateOrCreate(
                ['news_id' => $request->news_id],
                ['status' => WatchHistory::STATUS_ACTIVE]
            );

            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/remove-watch-history/{id}",
     *     summary="Remove Watch History",
     *     description="Remove a watch history record for the authenticated user.",
     *     operationId="removeWatchHistory",
     *     tags={"Watch Histories"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1,
     *                 description="Watch history ID"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Watch history successfully removed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Watch history successfully removed"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function removeWatchHistory($id)
    {
        try {

            $language = Auth::user()->language ?? 'eng';

            $message = match ($language) {
                'hin' => 'वॉच हिस्ट्री हटा दिया गया।',
                'guj' => 'જુઓ ઇતિહાસ સફળતાપૂર્વક દૂર કર્યા',
                default => 'Watch history successfully removed',
            };

            $updated = Auth::user()->watchHistories()
                ->active()
                ->where('id', $id)
                ->update(['status' => WatchHistory::STATUS_DELETED]);

            if (! $updated) {
                return Util::getErrorMessage('Watch history not found.');
            }

            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function removeAllWatchHistories()
    {
        try {
            $language = Auth::user()->language ?? 'eng';

            $message = match ($language) {
                'hin' => 'सभी वॉच हिस्ट्री सफलतापूर्वक हटा दिया गया।',
                'guj' => 'સભેલ જુઓ ઇતિહાસ સફળતાપૂર્વક દૂર કર્યા',
                default => 'All watch histories removed successfully',
            };

            Auth::user()->watchHistories()
                ->active()
                ->update(['status' => WatchHistory::STATUS_DELETED]);
            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function getCmsSlugs()
    {
        try {
            $cms = Cms::select('title', 'slug')->get();
            return Util::getSuccessMessage('CMS slugs fetched successfully', $cms);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function getCmsDetails($slug)
    {
        try {
            $cms = Cms::where('slug', $slug)->first();
            return Util::getSuccessMessage('CMS details fetched successfully', $cms);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function storeFcmToken(Request $request)
    {
        try {
            $fcmToken = $request->header('X-FCM-Token') ?? $request->input('fcm_token');

            $request->merge([
                'fcm_token' => is_string($fcmToken) ? trim($fcmToken) : null,
            ]);

            $request->validate([
                'fcm_token' => 'required|string',
            ]);

            $user = User::find(Auth::user()->id);
            app(\App\Services\FcmTokenService::class)->syncToken($user, $request->fcm_token);

            return Util::getSuccessMessage('FCM token stored successfully', [
                'has_fcm_token' => true,
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }
}
