<?php
/**
 * User: xiangzhiping
 * Date: 2018/7/30
 */

namespace App\Component\Auth;


use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;

class WebGuard implements Guard
{
    use GuardHelpers;

    protected $session;
    protected $userKey;

    public function __construct(UserProvider $provider, $userKey='user')
    {
        $this->provider = $provider;

        $this->userKey = $userKey;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        $user = session($this->userKey);
        if (!$user) return null;

        $this->user = $this->provider->retrieveById($user['id']);

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        throw new \ErrorException('not supported validate');
    }
}