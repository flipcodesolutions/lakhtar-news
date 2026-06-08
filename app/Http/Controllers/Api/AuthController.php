<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

            /// here are some predefined numbers to skip sms and defualt them to 1234
            $mobile = $request->mobile;

            if ($mobile == '9999999999') {

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
                'otp' => 'required|digits:4'
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
            ]);

            DB::beginTransaction();

            $user = User::create([
                'mobile' => $request->mobile,
                'name' => $request->name,
                'email' => $request->email,
                'language' => $request->language,
                'role' => 'user',
                'password' => Hash::make('123456'),
            ]);

            foreach ($request->interest as $categoryId) {
                UserFavoriteCategory::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId,
                ]);
            }

            DB::commit();

            return Util::getSuccessMessage('User registered successfully.', [
                'user' => $user
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/update-profile",
     *     summary="Update profile",
     *     description="Updates the user profile with the provided information.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "language", "password","mobile"},
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 description="10-digit mobile number",
     *                 example="9876543210"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="User name",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 description="User email",
     *                 example="john.doe@example.com"
     *             ),
     *             @OA\Property(
     *                 property="language",
     *                 type="string",
     *                 description="User language",
     *                 example="en"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 description="User password",
     *                 example="123456"
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

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|digits:10',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'language' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);


            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                $user = new User();
                $user->mobile = $request->mobile;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->language = $request->language;
                $user->role = 'user';
                $user->password = Hash::make($request->password);
                $user->save();
                return Util::getSuccessMessage('Profile updated successfully', [
                    'user' => $user
                ]);
            } else {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->language = $request->language;
                $user->save();
                return Util::getSuccessMessage('Profile updated successfully', [
                    'user' => $user
                ]);
            }
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

            $user = User::find(Auth::user()->id);
            $user->language = $request->language;
            $user->save();

            return Util::getSuccessMessage('Language changed successfully', [
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
     *     tags={"Home"},
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

            $language = Auth::user()?->language ?? 'eng';

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

            return Util::getSuccessMessage('My interest', [
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function updateMyInterest(Request $request)
    {
        try {
            $request->validate([
                'categories' => 'required|array',
            ]);

            $user = User::find(Auth::user()->id);
            $user->userFavoriteCategories()->sync($request->categories);

            return Util::getSuccessMessage('My interest updated successfully');
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }
}
