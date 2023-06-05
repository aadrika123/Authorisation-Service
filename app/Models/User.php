<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'password' => 'hashed',
    ];

    /**
     * | Get User by Email
     */
    public function getUserByEmail($email)
    {
        return User::where('email', $email)
            ->first();
    }

    public function getUserById($userId)
    {
        return User::select('users.*', 'ulb_masters.ulb_name')
            ->where('users.id', $userId)
            ->join('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
            ->first();
    }

    /**
     * | getUserRoleDtls
     */
    public function getUserRoleDtls()
    {
        return User::select()
            ->join('wf_roleusermaps', 'wf_roleusermaps.user_id', 'users.id')
            ->join('wf_roles', 'wf_roles.id', 'wf_roleusermaps.wf_role_id')
            ->where('suspended', false)
            ->where('wf_roleusermaps.is_suspended', false);
    }
}
