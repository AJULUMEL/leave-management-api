<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function redirectToGithub(): JsonResponse
    {
        $url = Socialite::driver('github')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'message' => 'GitHub OAuth redirect URL generated successfully.',
            'data' => [
                'url' => $url,
            ],
        ]);
    }

    public function handleGithubCallback(): JsonResponse
    {
        $githubUser = Socialite::driver('github')
            ->stateless()
            ->user();

        $email = $githubUser->getEmail();

        if (!$email) {
            $email = $githubUser->getNickname() . '@github.local';
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'provider' => 'github',
                'provider_id' => $githubUser->getId(),
            ]);
        } else {
            $user = User::create([
                'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                'email' => $email,
                'password' => null,
                'provider' => 'github',
                'provider_id' => $githubUser->getId(),
                'role' => 'employee',
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'GitHub login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}