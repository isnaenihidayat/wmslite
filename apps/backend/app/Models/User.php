<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     * Yii legacy uses el_ prefix — Laravel DB_PREFIX handles this.
     */
    protected $table = 'el_user';

    protected $primaryKey = 'user_id';

    public $timestamps = false; // uses date_created / date_modified

    protected $fillable = [
        'first_name',
        'last_name',
        'email_address',
        'password',
        'mobile_number',
        'status',
        'admin',
        'module',
        'type',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected $casts = [
        'admin'        => 'boolean',
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'last_login'   => 'datetime',
    ];

    /**
     * Override for non-standard email column name.
     */
    public function getAuthIdentifierName(): string
    {
        return 'user_id';
    }

    /**
     * Get the name used for auth.
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * Helper: full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
