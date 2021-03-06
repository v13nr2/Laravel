<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        //$user = DB::table('users')->get();
        $user = DB::table('users')->select('uuidx as uuid', 'name', 'email')->where('email', request()->email)->first();
        $grup = DB::select('SELECT B.name AS namagroup FROM nng_users_to_group A 
                            LEFT JOIN nng_group B ON B.id = A.group_id 
                            LEFT JOIN users C ON C.id = A.user_id
                            WHERE C.email = "'.request()->email.'" ORDER BY C.id ASC LIMIT 1');
        $res = array(
            'data'  => $user,
            'grup'  => $grup,
            'token' => compact('token')
        );
        return response()->json($res, 200);
    }

    public function userList(Request $request)
    {
        $credentials = $request->only('email', 'password');


        //$user = DB::table('users')->get();
        $user = DB::table('users')->select('uuidx as uuid', 'name', 'email', 'username', 'refferal_link')->where('id', '<>', 1)->get();
        $res = array(
            'data'  => $user,
        );
        return response()->json($res, 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:55|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $unik = Str::uuid()->toString();
        $user = User::create([
            'name' => $request->get('name'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'uuidx'  => $unik,
            'refferal_link' => $request->get('referal_link'),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }


    public function validator(array $data){ 

        return Validator::make($data, [
            'uuid' => 'required|max:255',
            'name' => 'required|max:255', //  Name
             //'email' => 'required|email|max:255|unique:users', // Unique Email id
             //'password' => 'required|min:6', //password min 6 charater
    
         ]);
      }


public function update(Request $request)
{

 /* Called Validator Method For Validation */  
   $validation = $this->validator($request->all());
    $User = User::where('uuidx',request()->uuid)->first(); /* Check id exist in table */
    //$id2 = Str::slug('6f43adf8 7d65 4f8c 8d09 5cb714327139');

    if(!is_null($User)){
        User::where('uuidx',$request->get("uuid"))->update(
         array(
                 'name' => $request->get("name"),
              )
         );
          $data = array('msg' => 'Updated successfully !! ', 'success' => true);
          echo json_encode($data);

    }else{

        $data = array('msg' => 'User Not Found !! ', 'error' => true);
        echo json_encode($data);
    }
}

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    public function guidv4($data)
    {
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}