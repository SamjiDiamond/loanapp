<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthenticateController extends Controller
{
    public function signup(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'first_name'   => 'required|min:3|max:20',
            'last_name'   => 'required|min:3|max:20',
            'middle_name'   => 'required|min:3|max:20',
            'email'      => 'required|email|unique:users,email',
            'password' => 'required|alpha_num|min:6|max:20',
            'phoneno' => 'required|min:11|max:11',
            'image' => 'required',
            'bank_account' => 'required|min:11|max:11',
            'bank_name' => 'required',
            'role' => 'required|min:4');

        $messages = array(
            'min' => 'Hmm, that looks short.',
            'max' => 'Oops, that too long.',
            'alpha_num'  => 'Use alphabet or alphabet with numbers to secure your password.');


        $validator = Validator::make($input, $rules, $messages);

        if ($validator->passes())
        {
            
            try
            {
                DB::beginTransaction();
                $input['first_name'] = ucfirst($input['first_name']);
                $input['last_name'] = ucfirst($input['last_name']);
                $input['password'] = Hash::make($input['password']);
                $input['status'] = "active";
                $user = User::create($input);

                $file_data= $request->input('image');
                //generating unique file name;
                $file_name = $genId.'.jpg';
                @list($type, $file_data) = explode(';', $file_data);
                @list(, $file_data)= explode(',', $file_data);
                if($file_data!=""){
                    // storing image in storage/app/public Folder
                    \Storage::disk('public')->put($file_name,base64_decode($file_data));
                    // \File::put(storage_path(). '/' . $file_name, base64_decode($file_data));

                    //Storage::put('/' . $file_name, $file_data, 'public');
                }
                DB::commit();
                return response()->json(['status'=> 1, 'message' => "Account created successfully"]);
            }catch(\Exception $e){
                DB::rollback();
                //dd($e);
                return response()->json(['status'=> 0, 'message'=>'Error creating account','error' => $e]);
            }
        }else{
            DB::rollback();
            return response()->json(['status'=> 0, 'message'=>'Error creating account', 'error' => $validator->errors()]);
        }

    }

    public function login(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'username'      => 'required',
            'password' => 'required');

        $validator = Validator::make($input, $rules);

        if ($validator->passes())
        {
            if(Auth::attempt(['phoneno' => request('phoneno'), 'password' => request('password')])){
                $user = Auth::user();
                $token = Str::random(60);

                $request->user()->forceFill([
                    'api_token' => hash('sha256', $token),
                ])->save();

                if(auth()->user()->status != "active"){
                    return response()->json(['status'=> 0, 'message'=>'User '.auth()->user()->status.', kindly contact support']);
                }

                return response()->json(['status'=> 1, 'message' => "User authenticated successfully", 'token' => $token, 'bvn'=>auth()->user()->bvn, 'user_id'=>auth()->user()->user_id]);
            }
            else{
                return response()->json(['status'=> 0, 'message'=>'Incorrect Login Details', 'error'=>'']);
            }
        }else{
            return response()->json(['status'=> 0, 'message'=>'Unable to login with errors', 'error' => $validator->errors()]);;
        }
    }

    public function getUser() {
        if(auth()->user()->status == "active"){
            $user = Auth::user();
            return response()->json(['status' => 1, 'message'=>'User details generated successfully', 'data'=> $user]);
        }else{
            return response()->json(['status'=> 0, 'message'=>'User '.auth()->user()->status.', kindly contact support']);
        }
    }

    public function updateUser(Request $request) {
        {
            $input = $request->all();
            $rules = array(
                'first_name'   => 'required|min:3|max:20',
                'last_name'   => 'required|min:3|max:20',
                'phoneno' => 'required|min:11|max:11',
                'bank_account' => 'required|min:10|max:10',
                'bank_name' => 'required',
                'role' => 'required|min:4');

            $messages = array(
                'min' => 'Hmm, that looks short.',
                'max' => 'Oops, that too long.',
                'alpha_num'  => 'Use alphabet or alphabet with numbers to secure your password.');


            $validator = Validator::make($input, $rules, $messages);

            if ($validator->passes())
            {
                $genId="";
                try
                {
                    DB::beginTransaction();

                    if($input['role']=='artisan'){
                        $artisan = DB::table('artisan_license')->where('number', $input['license'])->get()->first();
                        if(!$artisan){
                            return response()->json(['status' => 0, 'message' => 'Invalid License Number']);
                        }
                    }

                    $input['first_name'] = ucfirst($input['first_name']);
                    $input['last_name'] = ucfirst($input['last_name']);
                    $user=User::where(['user_id'=> auth()->user()->user_id])->update($input);
//
                    DB::commit();
                    return response()->json(['status'=> 1, 'message' => "Profile updated successfully"]);
                }catch(\Exception $e){
                    DB::rollback();
                    //dd($e);
                    return response()->json(['status'=> 0, 'message'=>'Error updating profile','error' => $e]);
                }
            }else{
                DB::rollback();
                return response()->json(['status'=> 0, 'message'=>'Error updating profile', 'error' => $validator->errors()]);
            }

        }
    }
}
