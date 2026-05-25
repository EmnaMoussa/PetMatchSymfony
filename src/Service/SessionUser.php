<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionUser
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
    ) {}

    public function get(): ?User
    {
        $id = $this->requestStack->getSession()->get('user_id');
        return $id ? $this->em->getRepository(User::class)->find($id) : null;
    }

    public function requireLogin(): ?User
    {
        return $this->get();
    }
}
