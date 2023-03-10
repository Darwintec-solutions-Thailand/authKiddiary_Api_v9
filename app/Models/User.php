<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
        /*------------------------------------------------- Data dictionary ------------------------------------------------
        id                                                                  => type: integer
        name                                                                => type: varchar
        username                                                            => type: varchar
        email                                                               => type: varchar
        password                                                            => type: varchar
        telephone                                                           => type: varchar
        remember_token                                                      => type: varchar
        citizen_id                                                          => type: varchar
        school_id    (foreign: school table)                                => type: integer
        hospital_department_id    (foreign: hospital_department table)      => type: integer
        school_group_id    (foreign: school_group table)                    => type: integer
        role                                                                => type: integer  | data: 1, 2, 3, 99
        status                                                              => type: integer  | data: 100, 101, 102, 200, 202, 300
        user_type                                                           => type: integer  | data: 300, 301, 302
    ------------------------------------------------------------------------------------------------------------------*/

    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name','username','email','password','role',

        // 'name',
        // 'username',
        // 'email',
        // 'password',
        // 'telephone',
        // 'citizen_id',
        // 'school_id',
        // 'hospital_department_id',
        // 'school_group_id',
        // 'role',
        // 'status',
        // 'user_type'
      
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
