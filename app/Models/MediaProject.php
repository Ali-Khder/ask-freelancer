<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'project_id',
        'post_id',
        'user_id',
    ];

    // Relationship one (project) to many (mediaproject)
    public function project(){
        return $this->belongsTo(PreviousProject::class,'project_id','id');
    }
    
    // Relationship one (post) to many (mediaproject)
    public function post(){
        return $this->belongsTo(Post::class,'post_id','id');
    }

    // Relationship one (post) to many (mediaproject)
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone('Asia/Damascus')
            ->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone('Asia/Damascus')
            ->toDateTimeString();
    }
}
