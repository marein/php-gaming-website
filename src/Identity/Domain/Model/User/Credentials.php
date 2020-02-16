<?php
declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Identity\Domain\HashAlgorithm;

final class Credentials
{
    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * Credentials constructor.
     *
     * @param string        $username
     * @param string        $password
     * @param HashAlgorithm $hashAlgorithm
     */
    public function __construct(string $username, string $password, HashAlgorithm $hashAlgorithm)
    {
        $this->username = $username;
        $this->password = $hashAlgorithm->hash($password);
    }

    /**
     * @return string
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * Returns true if the given credentials matches credentials.
     *
     * @param string        $password
     * @param HashAlgorithm $hashAlgorithm
     *
     * @return bool
     */
    public function matches(string $password, HashAlgorithm $hashAlgorithm): bool
    {
        return $hashAlgorithm->verify($password, $this->password);
    }
}
