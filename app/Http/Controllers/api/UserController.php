<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use DB;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }
        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function profile(){
        return response()->json(auth()->user());
    }

    public function logout() {
        auth()->logout();
        return response()->json([
            'status' => true,
            'message' => 'User successfully signed out'
        ]);
    }

    public function userStore(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(), [

            'name'  => 'required|max:100',
            'email' => 'required|email|unique:users',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }else{
            $user = new User();
            $user->name =$request->name;
            $user->email =$request->email;
            $user->contact =$request->contact;
            $user->password = Hash::make($request->password);
            $user->photo = 'avatar/default.png';

            $data= $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User successfully registered',
                'user' => $data
            ], 201);
        }
    }

    public function show(Request $request, $id){
        
        try{
            $user = User::findOrFail($id);
            return response()->json([
                'status' => true,
                'user' => $user
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                "message" =>'User not found.'
            ]);
        }
    }

    public function update(Request $request, $id){
        $user = User::find($id);
        $validator = Validator::make($request->all(), [

            // 'name'  => 'required|max:100',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }else{
            try{

                $profile_image = $request->photo;
               
                if ($profile_image) {
                    if($user->photo!='avatar/default.png'){
                        File::delete('/' . $user->photo);
                    }
                    $profile_image_name = hexdec(uniqid());
                    $profile_image_ext  = strtolower($profile_image->getClientOriginalExtension());

                    $profile_image_image_full_name = $profile_image_name . '.' . $profile_image_ext;
                    $profile_image_upload_path     = 'avatar/';
                    $profile_image_upload_path1    = 'avatar';
                    $profile_image_image_url       = $profile_image_upload_path . $profile_image_image_full_name;
                    $success                       = $profile_image->move($profile_image_upload_path1, $profile_image_image_full_name);
                    
                } else {
                    $profile_image_image_url = $user->photo;
                }
                // $user = User::find($id);
                $user->name =$request->name;
                $user->email =$request->email;
                $user->contact =$request->contact;
                $user->password = Hash::make($request->password);
                $user->photo  = $profile_image_image_url;
                $data= $user->save();
                $user = User::findOrFail($id);
    
                if(is_null($data)){
                    return response()->json(["message" => "User is not found"]);
                }else{
                    return response()->json([
                        'status' => true,
                        'message' => 'User Updated successfully',
                        'data' => $user
                    ]);
                }
            }catch(\Exception $e){
                return response()->json(["message" =>$e->getMessage()]);
            }
        }
    }

    public function destroy($id){
        
        $user = User::find($id);
        if ($user->id == auth()->user()->id) {
            return response()->json([
                'message' => 'You can not delete your own data',
            ]);
        }else {
            $user->delete();
            if($user->photo!='avatar/default.png'){
                File::delete('' . $user->photo);
            }
            $data['message'] = 'User deleted successfully';
            $data['id']      = $id;

            return response()->json([
                'status' => true,
                'data'    => $data,
            ]);
        }
    }
}
