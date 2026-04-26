<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\UserResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'surname1',
        'surname2',
        'alias',
        'rol',
        'elo',
        'partidas_jugadas',
        'titulo',
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
        'partidas_jugadas' => 'integer',
        'elo_total' => 'integer',
        'imagenes_acertadas' => 'integer',
        'promedio_puntos' => 'integer',
        'mejor_puntuacion' => 'integer',
        'ultima_puntuacion' => 'integer',
        'consistencia_pct' => 'integer',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    public function partidas()
    {
        return $this->belongsToMany(Partida::class, 'usuario_partida', 'id_usuario', 'id_partida')->withPivot('puntuacion');
    }

    public function salas()
    {
        return $this->belongsToMany(Sala::class, 'usuario_sala', 'id_usuario', 'id_sala')->withPivot('fecha_entrada');
    }

    public function salasCreadas()
    {
        return $this->hasMany(Sala::class, 'id_creador');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images/users')
            ->useFallbackUrl('/images/placeholder.jpg')
            ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }

    public function registerMediaConversions(Media $media = null): void
    {
        if (env('RESIZE_IMAGE') === true) {

            $this->addMediaConversion('resized-image')
                ->width(env('IMAGE_WIDTH', 300))
                ->height(env('IMAGE_HEIGHT', 300));
        }
    }
}
