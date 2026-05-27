<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class SessionUser
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private Security $security,
    ) {}

    public function get(): ?User
    {
        $securityUser = $this->security->getUser();
        if ($securityUser instanceof User) {
            $this->requestStack->getSession()->set('user_id', $securityUser->getId());
            return $securityUser;
        }

        $id = $this->requestStack->getSession()->get('user_id');
        return $id ? $this->em->getRepository(User::class)->find($id) : null;
    }

    public function requireLogin(): ?User
    {
        return $this->get();
    }
}
