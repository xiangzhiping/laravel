<?php
/**
 * User: xiangzhiping
 * Date: 2018/7/30
 */

namespace App\Component\Auth;


use App\Component\JwtToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ApiProvider implements UserProvider
{

    protected $storageKey;

    public function __construct($storageKey)
    {
        $this->storageKey = $storageKey;
    }


    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return new AuthUser(['id'=>$identifier]);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws
     */
    public function retrieveByToken($identifier, $token)
    {
        throw new \ErrorException('no supported remember me token');
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     *
     * @return void
     * @throws
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new \ErrorException('no supported remember me token');
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials[$this->storageKey])) return null;

        $payload    = JwtToken::instance()->parseToken($credentials[$this->storageKey]);
        $identifier = $payload['sub'];

        return $this->retrieveById($identifier);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $userObj = $this->retrieveByCredentials($credentials);

        return $userObj && $userObj->getAuthIdentifier() == $user->getAuthIdentifier();
    }
}