<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Offer;
use App\Models\User;

class OfferController extends Controller
{
    use Traits\ResponseTrait;

    /*
     * 
     * create Offer
     * Create a user Offer of the post on the database
     * @return message by JsonResponse
     * */
    public function createOffer(Request $request, $id)
    {
        try {
            Validator::extend('date_multi_format', function ($attribute, $value, $formats) {
                foreach ($formats as $format) {
                    $parsed = date_parse_from_format($format, $value);
                    if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                        return true;
                    }
                }
                return false;
            });

            $rules = [
                'discription' => ['required', 'string'],
                'price' => ['required', 'numeric'],
                'deliveryDate' => ['required', 'date', 'date_multi_format:"Y-n-j","Y-m-d"', 'after:now'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->failed($validator->errors()->first());
            }

            $user = User::find(auth()->user()->id);
            $post = Post::find($id);

            $offer = Offer::create([
                'discription' => $request->discription,
                'price' => $request->price,
                'deliveryDate' => $request->deliveryDate,
                'post_id' => $post['id'],
                'user_id' => $user['id']
            ]);

            $message = 'تم إنشاء عرض بنجاح';
            return $this->success($message);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * edit Offer
     * edit a user Offer of the post on the database
     * @return message by JsonResponse
     * */
    public function editOffer(Request $request, $id)
    {
        try {
            $offer = Offer::find($id);

            Validator::extend('date_multi_format', function ($attribute, $value, $formats) {
                foreach ($formats as $format) {
                    $parsed = date_parse_from_format($format, $value);
                    if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                        return true;
                    }
                }
                return false;
            });

            $rules = [
                'discription' => ['string'],
                'price' => [ 'numeric'],
                'deliveryDate' => [ 'date', 'date_multi_format:"Y-n-j","Y-m-d"', 'after:now'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->failed($validator->errors()->first());
            }

            if ($request->discription)
                $offer->discription = $request->discription;

            if ($request->price)
                $offer->price = $request->price;

            if ($request->deliveryDate)
                $offer->deliveryDate = $request->deliveryDate;

            $offer->save();

            $message = 'تم تعديل العرض بنجاح';
            return $this->success($message);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * delete Offer
     * delete a user Offer of the post on the database
     * @return message by JsonResponse
     * */
    public function deleteOffer($id)
    {
        try {
            $offer = Offer::find($id);
            $offer->delete();
            $message = 'تم حذف العرض بنجاح';
            return $this->success($message);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * get Post Offers 
     * Get all id post offers
     * @return Data by JsonResponse : array of offers
     * */
    public function getPostOffers($id)
    {
        try {

            $post = Post::find($id);

            $offers = $post->offers;

            foreach ($offers as $offer) {
                $offer->user;
            }

            return $this->success('post ' .$id, $offers);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }
}
