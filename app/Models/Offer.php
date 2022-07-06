<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'discription','price', 'deliveryDate'
    ];


    // Relationship one (user) to many (offer)
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }


    // Relationship one (post) to many (offer)
    public function post(){
        return $this->belongsTo(Post::class,'post_id','id');
    }
}
