<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\MediaProject;
use App\Models\User;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    use Traits\ResponseTrait;
    use Traits\ImageTrait;

    /*
     * 
     * create post
     * Create a user post on the database
     * @return message by JsonResponse
     * */    
    public function createPost (Request $request, $id){
        try{
            Validator::extend('date_multi_format', function($attribute, $value, $formats) {
                foreach($formats as $format) {
                    $parsed = date_parse_from_format($format, $value);
                    if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                        return true;
                    }
                }
                return false;
            });

            $rules=[
                'title'=> ['required','string'],
                'body'=> ['required','string'],
                'price'=> ['required', 'numeric'],
                'deliveryDate'=> ['required','date','date_multi_format:"Y-n-j","Y-m-d"','after:now'],
                'media' => 'array',
                'media.*' => 'required|max:20000|mimes:bmp,jpg,png,jpeg,svg,gif,flv,mp4,mkv,m4v,gifv,m3u8,ts,3gp,mov,avi,wmv',
            ];
            
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->failed($validator->errors()->first());
            }

            $user = User::find(auth()->user()->id);

            if($request->price <= 30){
                $post = Post::create([
                    'title'=> $request->title,
                    'body'=> $request->body,
                    'user_id'=> $user['id'],
                    'price'=> $request->price,
                    'deliveryDate'=> $request->deliveryDate,
                    'type'=>'small services'
                ]);
            }else{
                $post = Post::create([
                    'title'=> $request->title,
                    'body'=> $request->body,
                    'user_id'=> $user['id'],
                    'price'=> $request->price,
                    'deliveryDate'=> $request->deliveryDate,
                ]);    
            }

            if ($request->has('media')) {
                $media = $request->file('media');
                if ($media != null) {
                    $i = 0;
                    foreach ($media as $file) {

                        $i++;
                        $media = $this->saveImage($file, 'freelancers projects', $i);

                        $medias[] = MediaProject::create([
                            'path' => $media['path'],
                            'project_id' => $post->id,
                        ]);
                    }
                }
            }

            PostCategory::create([
                'post_id'=> $post['id'],
                'category_id'=> $id,
            ]);

            $message = 'تم إنشاء منشور بنجاح';
            return $this->success($message);

        }catch(\Exception $e){
            return $this->failed($e->getMessage());
        }
    }
    
    /*
     * 
     * edit post
     * Edit a user post on the database
     * @return message by JsonResponse
     * */    
    public function editPost (Request $request, $id){
        try{
            $post = Post::find($id);
            $user = User::find(auth()->user()->id);

            if($post->user_id != $user->id){
                return $this->returnError('ليس لديك الصلاحية بتعديل هذا المنشور');
            }

            Validator::extend('date_multi_format', function($attribute, $value, $formats) {
                foreach($formats as $format) {
                    $parsed = date_parse_from_format($format, $value);
                    if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                        return true;
                    }
                }
                return false;
            });

            $request['created_at']=$post['created_at'];

            $rules=[
                'title'=> ['string'],
                'body'=> ['string'],
                'price'=> ['numeric'],
                'created_at'=> ['date'],
                'deliveryDate'=> ['date','date_multi_format:"Y-n-j","Y-m-d"','after:created_at'],
                'media' => 'array',
                'media.*' => 'required|max:20000|mimes:bmp,jpg,png,jpeg,svg,gif,flv,mp4,mkv,m4v,gifv,m3u8,ts,3gp,mov,avi,wmv',
                'delete_media' => 'array',
                'delete_media.*' => 'required|integer|min:1|exists:media_projects,id',
            ];

            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails()){
                return $this->failed($validator->errors()->first());
            }

            if ($request->title)
                $post->title = $request->title;

            if ($request->body)
                $post->body = $request->body;

            if ($request->price){
                $post->price = $request->price;

                if($request->price <= 30)
                    $post->type ='small services';
                else{
                    $post->type ='non small services';
                }
            }

            if ($request->deliveryDate)
                $post->deliveryDate = $request->deliveryDate;

            $post->save();
        
            if ($request->has('media')) {
                $media = $request->file('media');
                if ($media != null) {
                    $i = 0;
                    foreach ($media as $file) {

                        $i++;
                        $media = $this->saveImage($file, 'freelancers projects', $i);

                        $medias[] = MediaProject::create([
                            'path' => $media['path'],
                            'project_id' => $post->id,
                        ]);
                    }
                }
            }

            if ($request->has('delete_media')) {
                $delete_media = $request->get('delete_media');

                foreach ($delete_media as $media) {
                    $media_record = MediaProject::find($media);

                    if (File::exists(public_path($media_record->path)))
                        File::delete(public_path($media_record->path));
                    MediaProject::destroy($media);
                }
            }

            $message = 'تم تعديل المنشور بنجاح';
            return $this->success($message);

        }catch(\Exception $e){
            return $this->failed($e->getMessage());
        }
    }

    /*
     * 
     * delete post
     * Delete a user post on the database
     * @return message by JsonResponse
     * */    
    public function deletePost ($id){
        try{  
            $post = Post::find($id);
            $user = User::find(auth()->user()->id);

            if($post->user_id != $user->id){
                return $this->returnError('ليس لديك الصلاحية بتعديل هذا المنشور');
            }

            $MediasProject=$post->MediasProject;

            foreach( $MediasProject as $MediaProject){
                $MediaProject->delete();
            }
            
            $offers=$post->offers;

            foreach( $offers as $offer){
                $offer->delete();
            }
            
            $postcategories=$post->postcategories;

            foreach( $postcategories as $postcategory){
                $postcategory->delete();
            }
            
            $post->delete();
            $message = 'تم حذف المنشور بنجاح';
            return $this->success($message);

        }catch(\Exception $e){
            return $this->failed($e->getMessage());
        }
    }
    
}
