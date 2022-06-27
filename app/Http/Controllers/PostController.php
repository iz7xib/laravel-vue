<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostsRequest;
use App\Http\Requests\UpdatePostsRequest;
use App\Http\Resources\PostResource;
use App\Models\Posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
//        return PostResource::collection(Posts::where('user_id', $user->id)->paginate());
        return PostResource::collection(Posts::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePostsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostsRequest $request)
    {
        $data = $request->validated();
        // Check if image was given and save on local file system
        if (isset($data['image'])) {
            $relativePath  = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }
        $post = Posts::create($data);
        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Posts $post
     * @return \Illuminate\Http\Response
     */
    public function show(Posts $post, Request $request)
    {
        $user = $request->user();
        if ($user->id !== $post->user_id) {
            return abort(403, 'Unauthorized action.');
        }

        return new PostResource($post);
    }
    /**
     * Display the specified resource.
     *
     * @param \App\Models\Posts $posts
     * @return \Illuminate\Http\Response
     */
    public function showForGuest(Posts $posts)
    {
        if (!$posts->status) {
            return response("", 404);
        }

        $currentDate = new \DateTime();
        $expireDate = new \DateTime($posts->expire_date);
        if ($currentDate > $expireDate && $expireDate) {
            return response("", 404);
        }

        return new PostResource($posts);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdatePostsRequest $request
     * @param \App\Models\Posts                     $post
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostsRequest $request, Posts $post)
    {
        $data = $request->validated();

        // Check if image was given and save on local file system
        if (isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;

            // If there is an old image, delete it
            if ($post->image) {
                $absolutePath = public_path($post->image);
                File::delete($absolutePath);
            }
        }

        // Update survey in the database
        $post->update($data);

        // Get ids as plain array of existing questions
        //$existingIds = $post->questions()->pluck('id')->toArray();
        // Get ids as plain array of new questions
        //$newIds = Arr::pluck($data['questions'], 'id');
        // Find questions to delete
        //$toDelete = array_diff($existingIds, $newIds);
        //Find questions to add
        //$toAdd = array_diff($newIds, $existingIds);

        // Delete questions by $toDelete array
        //SurveyQuestion::destroy($toDelete);

        // Create new questions
        /*foreach ($data['questions'] as $question) {
            if (in_array($question['id'], $toAdd)) {
                $question['survey_id'] = $post->id;
                $this->createQuestion($question);
            }
        }*/

        // Update existing questions
        //$questionMap = collect($data['questions'])->keyBy('id');
        /*foreach ($post->questions as $question) {
            if (isset($questionMap[$question->id])) {
                $this->updateQuestion($question, $questionMap[$question->id]);
            }
        }*/

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Posts $posts)
    {
        $user = $request->user();
        if ($user->id !== $post->user_id) {
            return abort(403, 'Unauthorized action.');
        }
        $post->delete();
        return response('', 204);
    }
    /**
     * Save image in local file system and return saved image path
     *
     * @param $image
     * @throws \Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    private function saveImage($image)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($image, strpos($image, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $image);

        return $relativePath;
    }
}
