<?php
namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

use App\Models\User;

use App\Exceptions\User\UserNotExistsException;

class UserProvider extends EloquentUserProvider
{
    /**
     * (non-PHPdoc)
     *
     * @see \Illuminate\Auth\EloquentUserProvider::validateCredentials()
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if ($user instanceof User) {
            $plain = $credentials['password'];
            if ($user->user_status != User::NORMAL_STATUS) {
                throw new UserNotExistsException('不可登录账号，请联系管理员');
            }

            return $this->hasher->check($plain, $user->getAuthPassword(), ['salt' => $user->salt]);
        }

        return parent::validateCredentials($user, $credentials);
    }
}
