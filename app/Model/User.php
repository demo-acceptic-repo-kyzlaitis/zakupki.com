<?php

namespace App\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\App;


class User extends Model implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activation_code',
        'active',
    ];

    protected $guarded = [
        'created_at',
        'updated_at',
        'remember_token',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function organization() {
        return $this->hasOne('App\Model\Organization');
    }

    public function tenders() {
        return $this->hasManyThrough('App\Model\Tender', 'App\Model\Organization');
    }

    public function hasOrganization() {
        $organization = $this->organization;

        return !empty($organization);
    }

    public function balance()
    {
        return $this->hasOne('App\Model\UserBalance', 'user_id', 'id');
    }

    public function getBalance(){
        if (UserBalance::find($this->id) == null){
            $ub = new UserBalance;
            $ub->user_id = $this->id;
            $ub->balance = 0.00;
            $ub->currency = 'UAH';
            $ub->save();
            return $ub->user_id;
        }else{
            $ub =  UserBalance::find($this->id);
            return $ub->balance;
        }
    }
    public function orders()
    {
        return $this->hasMany('App\Model\Order');
    }
    public function paymentHistory()
    {
        return $this->hasMany('App\Model\PaymentHistory', 'user_id', 'id');
    }

    public function transactions() {
        return $this->hasMany('App\Model\Transaction', 'user_id', 'id');
    }

    /**
     * Get all roles for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany('App\Model\Role', 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Check does user has role
     *
     * @param $role
     * @return bool
     */
    public function has_role($role) {
        return ($this->roles()->where('roles.code', $role)->first()) ? true : false;
    }

    /**
     * Check does user has access (this role (role value) or higher)
     *
     * @param $role
     * @return bool
     */
    public function has_access($role) {
        if ((int) $role > 0) {
            return ($this->roles()->where('roles.value', '<=', (int) $role)->first()) ? true : false;
        } else {
            $roleModel = App::make('App\Model\Role')->where('code', $role)->first();
            return ($this->roles()->where('roles.value', '<=', $roleModel->value)->first()) ? true : false;
        }
    }
}
