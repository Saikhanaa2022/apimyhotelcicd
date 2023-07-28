<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Filesystem\FileNotFoundException;

class ImageController extends Controller
{
	/**
	 * Upload image to File system.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function upload(Request $request)
	{
        try {
            $request->validate([
                'image' => 'required|mimes:jpeg,png,jpg|max:1000',
                'path' => 'nullable|string',
            ]);
    
            $id = $request->header('X-Slug');
            $image = $request->file('image');
            $path = $request->input('path');
    
            // Check id /hotel/
            if ($id && $id != '') {
                $path = $path . '/' . $id;
            }
    
            $storagePath = Storage::put($path, $image, 'public');
    
            return response()->json([
                'path' => $storagePath,
            ]);
        } catch(\Exception $e) {
			$exceptionType = get_class($e);
			if($exceptionType === 'Illuminate\Validation\ValidationException'){
				return response()->json([
					'success' => false,
					'error' => $e->getMessage(),
					'message' => 'Үйлдэл амжилтгүй . Таны зургийн хэмжээ 1MB-ас дээш байна.',
				], 400);
			}else{
				return response()->json([
					'success' => false,
					'error' => $e->getMessage(),
					'message' => 'Үйлдэл амжилтгүй . Алдаа гарлаа.',
				], 400);
			}
        }
	}

	/**
	 * Return image.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function render(Request $request)
	{
		try {
			$server = ServerFactory::create([
				// Response
				'response' => new LaravelResponseFactory(),
				// Source filesystem
				'source' => Storage::disk(env("FILESYSTEM_DRIVER", "local"))->getDriver(),
				// Source filesystem path prefix
				'source_path_prefix' => '',
				// Cache filesystem
				'cache' => Storage::disk(env("FILESYSTEM_DRIVER", "local"))->getDriver(),
				// Cache filesystem path prefix
				'cache_path_prefix' => 'cache',
			]);

			return $server->getImageResponse($request->path, $request->all());
		} catch (SignatureException $e) {
			// Forbidden access
			abort(403);
		} catch (FileNotFoundException $e) {
			// Not Found
			abort(404);
		}
	}

    // /**
	//  * Destroy image from File system.
	//  *
	//  * @param  \Illuminate\Http\Request  $request
	//  * @return \Illuminate\Http\Response
	//  */
	// public function destroy(Request $request)
	// {
    //     // $request->validate([
    //     //     'path' => 'required|string',
    //     // ]);
    //     $path = $request->input('path');

	// 	if(Storage::disk('s3')->exists($path)) {
    //         Storage::disk('s3')->delete($path);
    //     }

	// 	return response()->json([
	// 		'path' => $path,
	// 	]);
	// }
}
