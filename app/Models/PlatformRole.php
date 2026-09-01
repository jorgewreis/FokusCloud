<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRole extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'code', 'name'];

    public function permissions()
    {
        return $this->belongsToMany(PlatformPermission::class, 'platform_role_permissions', 'platform_role_id', 'platform_permission_id');
    }
}
