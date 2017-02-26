<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'code',
        'name',
        'value'
    ];

    /**
     * Get all users for this role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users() {
        return $this->belongsToMany('App\Model\User', 'user_roles', 'role_id', 'user_id');
    }
}
