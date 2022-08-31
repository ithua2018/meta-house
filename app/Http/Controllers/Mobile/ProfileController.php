<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Requests\API\ProfileUpdateRequest;
use App\Models\User;
use App\Services\TokenManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends AbstractApiController
{
    private Hash $hash;
    private TokenManager $tokenManager;

    /** @var User */
    private ?Authenticatable $currentUser;

    public function __construct(Hash $hash)
    {
        $this->hash = $hash;
    }

    public function show()
    {
        return response()->json($this->currentUser);
    }

    public function update(ProfileUpdateRequest $request)
    {

        $data = $request->only('name', 'email');

        if ($request->new_password) {
            $data['password'] = $this->hash->make($request->new_password);
        }

        $this->currentUser->update($data);

        $responseData = $request->new_password
            ? ['token' => $this->tokenManager->refreshToken($this->currentUser)->plainTextToken]
            : [];

        return response()->json($responseData);
    }
}
