<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'middle_name', 'phoneno', 'image', 'bank_account', 'bank_name', 'role','auth','bvn','gender','date','address', 'user_state', 
		'marital', 'no_children', 'remember_token', 'device_details','deviceid','version', 'next_name','next_relationship', 'next_phone', 'next_address','employer_name', 'monthly_salary', 'employment_date','salary_payday', 'employer_address', 'bank_name', 'account_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
