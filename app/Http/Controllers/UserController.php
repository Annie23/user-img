<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client; // For TinyPNG API
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class UserController extends Controller
{
    // Return list of users with pagination (6 per page)
    public function index(Request $request)
    {
        $users = User::paginate(6);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return response()->json([
                'message' => 'Authorization token is required to access this resource.'
            ], 401);
        }

        // Extract token from the header
        $token = str_replace('Bearer ', '', $authHeader);

        // Validate the token
        if ($token !== Cache::get('auth_token')) {
            return response()->json([
                'message' => 'Invalid or expired authorization token. Please provide a valid token.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'image' => 'required|image|mimes:jpg,png,jpeg',
        ]);

        $manager = new ImageManager(new Driver());

        $image = $manager->read($request->file('image'));

        $image->resize(70, 70);

        $imageName = time().'-'.$request->file('image')->getClientOriginalName();

        $destinationPath = public_path('images/');

        // Create the images directory if it doesn't exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $image->save($destinationPath.$imageName);

        // Optimize image using TinyPNG API
        $optimizedImageUrl = $this->optimizeImageWithTinyPng($destinationPath . $imageName);

        // Create user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->image_url = $optimizedImageUrl;
        $user->save();

        return response()->json($user, 201);
    }

    private function optimizeImageWithTinyPng($imagePath)
    {
        $client = new Client();
        $apiKey = '4XmcFpygsFmgZxDD3wxlLljTm1HPL6TR';//env('TINYPNG_API_KEY');
        $response = $client->request('POST', 'https://api.tinify.com/shrink', [
            'auth' => ['api', $apiKey],
            'body' => fopen($imagePath, 'r')
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $optimizedUrl = $result['output']['url'];

        // Download the optimized image and save it
        $client->request('GET', $optimizedUrl, ['sink' => $imagePath]);

        return asset('storage/' . basename($imagePath));
    }

    // Generate and return a token (for demonstration)
    public function getToken(Request $request)
    {
        $token = Str::random(60);
        Cache::put('auth_token', $token, now()->addMinutes(60));

        return response()->json(['token' => $token]);
    }
}
