<?php
namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * customized hasher dedicated for securing user's password
 *
 */
class PasswordHasher implements HasherContract
{
    /**
     * (non-PHPdoc)
     *
     * @see \Illuminate\Contracts\Hashing\Hasher::make()
     *
     * @param string $value
     * @param array  $options avialable options are:
     *                        - salt     salt that adds randomness to the hashing result
     * @return string
     */
    public function make($value, array $options = [])
    {
        // possibly we get salt from options
        $salt = array_get($options, 'salt', '');

        return strtoupper(md5($salt . $value));
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Illuminate\Contracts\Hashing\Hasher::check()
     *
     * @param string $value       密码明文值
     * @param string $hashedValue 密码散列值
     * @param array  $options     avialable options are:
     *                            - salt     salt that goes along with the hashed value
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        return $hashedValue == $this->make($value, ['salt' => array_get($options, 'salt', '')]);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Illuminate\Contracts\Hashing\Hasher::needsRehash()
     *
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        // currently we don't take rehash into consideration
        return false;
    }
}
