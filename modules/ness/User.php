<?php

namespace modules\ness;

/**
 * Ness User interface
 *
 * @author Aleksej Sokolov <aleksej000@gmail.com>,<chosenone111@protonmail.com>
 */
class User
{
    private string $username;
    private string $type;
    private string $nonce;
    private array $tags;
    private string $public;
    private string $verify;

    public function __construct(string $username, string $type, string $nonce, array $tags, string $public, string $verify)
    {
        $this->username = $username;
        $this->type = $type;
        $this->nonce = $nonce;
        $this->tags = $tags;
        $this->public = $public;
        $this->verify = $verify;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getPublic(): string
    {
        return $this->public;
    }

    public function getVerify(): string
    {
        return $this->verify;
    }
}
