<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits;
use App\Models\MediaPost;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Offer;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\File;

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
                'price' => ['numeric'],
                'deliveryDate' => ['date', 'date_multi_format:"Y-n-j","Y-m-d"', 'after:now'],
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

            return $this->success('post ' . $id, $offers);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * accept offer
     * Customer acceptance of the Freelancer offer
     * @return message by JsonResponse
     * */
    public function acceptOffer($id)
    {
        try {

            $offer = Offer::find($id);

            $post = $offer->post;

            $order = $post->order;

            $user = User::find(auth()->user()->id);


            if ($order != null) {
                return $this->failed('يوحد عرض مقبول مسبقاً');
            }

            if ($post->user_id != $user->id) {
                return $this->failed('ليس لديك الصلاحية بقبول هذا العرض');
            }

            if ($offer->user_id == $user->id) {
                return $this->failed('ليس بالامكان قبول عرضك');
            }

            Order::create([
                'discription' => $offer->discription,
                'price' => $offer->price,
                'deliveryDate' => $offer->deliveryDate,
                'freelancer_id' => $offer->user_id,
                'user_id' => $post->user_id,
                'post_id' => $post->id,
            ]);

            return $this->success('تم قبول العرض بنجاح');
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * cancel accept offers 
     * Customer cancels acceptance of the Freelancer offer
     * The freelancer refused the customer's approval to my offer
     * @return message by JsonResponse
     * */
    public function cancelOrder($id)
    {
        try {

            $order = Order::find($id);

            $user = User::find(auth()->user()->id);

            if (($order->user_id != $user->id) && ($order->freelancer_id != $user->id)) {
                return $this->failed('ليس لديك الصلاحية للقيام بذلك');
            }

            if ($order->post_id == null) {
                return $this->failed('ليس لديك الصلاحية للقيام بذلك');
            }

            $order->delete();

            return $this->success('تم إلغاء قبول العرض');
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * accept accept offers 
     * The freelancer accepted the customer's approval to my offer
     * @return message by JsonResponse
     * */
    public function acceptAcceptOffer($id)
    {
        try {

            $order = Order::find($id);

            $user = User::find(auth()->user()->id);

            if ($order->freelancer_id != $user->id) {
                return $this->failed('ليس لديك الصلاحية بالموافقة على قبول العرض');
            }

            $post = $order->post;

            $mediaposts = $post->mediaposts;

            foreach ($mediaposts as $mediapost) {
                if (File::exists(public_path($mediapost->path)))
                    File::delete(public_path($mediapost->path));
                $mediapost->delete();
            }

            $offers = $post->offers;

            foreach ($offers as $offer) {
                $offer->delete();
            }

            $postcategories = $post->postcategories;

            foreach ($postcategories as $postcategory) {
                $postcategory->delete();
            }

            $post->delete();

            $order->post_id = null;
            $order->save();

            return $this->success('تمت الموافقة على قبول العرض');
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }
}
