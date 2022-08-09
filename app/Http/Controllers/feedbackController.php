<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class feedbackController extends Controller
{
    use Traits\ResponseTrait;

    public function getAll()
    {
        return $this->success(
            'التقييمات والشكاوى',
            Feedback::all()
        );
    }

    public function getForGuest()
    {
        return $this->success(
            'التقييمات والشكاوى',
            Feedback::where('status', 1)->get()
        );
    }

    public function feedback(Request $request){

    }
}
