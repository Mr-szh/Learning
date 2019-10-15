<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     * 可分配的属性
     * 
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     * 应该为数组隐藏的属性
     * 
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     * 应该转换为本机类型的属性
     * 
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function addresses()
    {
        // 一对多关系
        return $this->hasMany(UserAddress::class);
    }

    public function favoriteProducts()
    {
        /**
         * 用于定义一个多对多的关联
         * withTimestamps() 代表中间表带有时间戳字段
         * 默认的排序方式是根据中间表的创建时间倒序排序
         */
        return $this->belongsToMany(Product::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at', 'desc');
    }

    public function cartItems()
    {
        // 一对多关系
        return $this->hasMany(CartItem::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
