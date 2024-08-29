<?php

namespace App\Http\Controllers\user;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Http\Traits\ApiResponseTrait;
class AuthController extends Controller
{
    use ApiResponseTrait;
    //
    public function register(Request $request){
    $request->validate([
        'name' => 'required|string',
        'phone_number' => 'required|number',
        'email' => 'required|string|email|max:255|unique:users',
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'certificate' => 'required|string',
        'password' => 'required|string|confirmed',
    ]);
    $file     = ($request->file('photo'));
   // return response()->json($file );
        $fileName = time() . '-' . $file-> getClientOriginalName();
        
        $file->move(images , $fileName);
   
    $file = $request->file('file');
$fileName = $file->getClientOriginalName();
$path = $file->store('uploads','public');
$request->user()->generateTwoFactorCode();
    $request->user()->notify(new SendTwoFactorCode());
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'password' => bcrypt($request->password),
        "profile_photo" => $fileName,
        "certificate" =>$path,
    ]);

    $token =  $user->createToken('MyApp')->plainTextToken;
    return $this->apiResponse($user, $token, 'User Register successfully', 201);
    
}
public function login(Request $request)
{
    if($request->email){
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);
    $credentials = $request->only('email', 'password');

    $token = Auth::attempt($credentials);
    if (!$token) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
        ], 401);
    }

    $user = Auth::user();
    return $this->apiResponse($user, $token, 'User login successfully', 201);
   }
   else if ($request->phone_number){
    $request->validate([
        'phone_number' => 'required',
        'password' => 'required|string',
    ]);
    $credentials = $request->only('phone_number', 'password');

    $token = Auth::attempt($credentials);
    if (!$token) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
        ], 401);
    }

    $user = Auth::user();
    return $this->apiResponse($user, $token, 'User login successfully', 201);
   }

}


public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(Carbon::now()->addMinutes(config('sanctum.ac_expiration'))),
                'type' => 'bearer',
            ]
        ]);
    }






}
