<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROL_ADMIN = 'admin';
    const ROL_VENDEDOR = 'vendedor';
    const ROL_CLIENTE = 'cliente';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'telefono',
        'direccion',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function ventasComoCliente(): HasMany
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    public function ventasComoVendedor(): HasMany
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esVendedor(): bool
    {
        return $this->rol === self::ROL_VENDEDOR;
    }

    public function esCliente(): bool
    {
        return $this->rol === self::ROL_CLIENTE;
    }
}
