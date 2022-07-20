<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\MediaProject;

class IdentityDocumentionController extends Controller
{

    use Traits\ResponseTrait;
    use Traits\ImageTrait;

    //get my status of identity (0 is false, 1 is true)
    public function getStatus()
    {
        $me = User::find(auth()->user()->id);
        return $this->success('My identity\'s status', $me->is_documented);
    }

    /*
     *
     * send identity document
     * Storing the user's identity document on the database for acceptance or rejection by the admin
     * @return message by JsonResponse
     * */
    public function sendIdentityDocument(Request $request)
    {
        try {

            $rules = [
                'media' => ['required', 'array'],
                'media.*' => 'required|max:20000|mimes:bmp,jpg,png,jpeg',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->failed($validator->errors()->first());
            }

            $user = User::find(auth()->user()->id);

            $media = $request->file('media');
            if ($media != null) {
                $i = 0;
                foreach ($media as $file) {

                    $i++;
                    $media = $this->saveImage($file, 'freelancers identity documents', $i);

                    $medias[] = MediaProject::create([
                        'path' => $media['path'],
                        'user_id' => $user->id,
                    ]);
                }
            }

            $message = 'تم ارسال الوثائق بنجاح';
            return $this->success($message);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     *
     * respone identity documentation
     * Acceptance or rejection of user documents by the admin
     * true for Acceptance | false for rejection
     * @return message by JsonResponse
     * */
    public function ResponeIdentityDocumentation(Request $request)
    {
        try {

            $rules = [
                'user_id' => ['required', 'numeric', 'exists:users,id'],
                'is_documented' => ['required', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->failed($validator->errors()->first());
            }

            $user = User::find($request->user_id);
            $media = $user->mediaprojects;

            if (count($media) != 0) {
                if ($request->is_documented == true) {
                    $user->is_documented = $request->is_documented;
                    $user->save();
                } else {
                    foreach ($media as $oneMedia) {
                        $oneMedia->delete;
                    }
                }
                $message = 'تم الاستجابة للوثائق بنجاح';
                return $this->success($message);
            }

            $message = 'حصل خطأ، لا يوجد وثائق خاصة بالمستخدم';
            return $this->failed($message);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /*
     *
     * get identity documentation
     * get user identification documents
     * @return message by JsonResponse
     * */
    public function GetIdentityDocumentation()
    {
        try {

            $media = MediaProject::whereNotNull('user_id')
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy('user_id');

            $arr = [];
            $react = false;
            foreach ($media as $one_media) {
                for ($i = 0; $i < count($one_media); $i++) {
                    $user = $one_media[$i]->user;
                    if ($user->is_documented == 0) {
                        $arr[] = $one_media;
                        $react = true;
                    }
                }
            }

            if ($react)
                return $this->success('طلبات توثيق المستخدمين', $arr);
            else
                return $this->success('لا يوجد وثائق');
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }
}
