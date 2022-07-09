<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','body', 'price', 'type', 'deliveryDate', 'user_id'
    ];

    protected $attributes=[
        'type' => 'Non small services',
    ];

    // Relationship one (user) to many (post)
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }


    // Relationship one (post) to many (postcategory)
    public function postcategories(){
        return $this->hasMany(PostCategory::class,'post_id','id');
    }

    // Relationship one (post) to many (offer)
    public function offers(){
        return $this->hasMany(Offer::class,'post_id','id');
    }

    // Relationship one (post) to many (media)
    public function MediasProject(){
        return $this->hasMany(MediaProject::class,'project_id','id');
    }
}
