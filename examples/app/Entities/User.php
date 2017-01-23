<?php

namespace App\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @SWG\Definition(definition="User", type="object")
 * @SWG\Property(property="id", type="integer", example="5")
 * @SWG\Property(property="name", type="string", example="Testi Tester")
 * @SWG\Property(property="email", type="string", example="testi.tester@gmail.com")
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone_number', 'country', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'name', 'email', 'phone_number', 'country'
    ];

    public function getCountryAttribute($value)
    {
        if ($value == null) {
            return config('app.fallback_locale');
        }

        return $value;
    }


    public function contacts()
    {
        return $this->hasMany(Contact::class, 'owner_id')
            ->whereNotNull('contacts.user_id')
            ->with('user');
    }

    public function pendingContacts()
    {
        return $this->hasMany(Contact::class, 'owner_id')
            ->whereNull('contacts.user_id');
    }

    public function allContacts()
    {
        return $this->hasMany(Contact::class, 'owner_id');
    }
}
