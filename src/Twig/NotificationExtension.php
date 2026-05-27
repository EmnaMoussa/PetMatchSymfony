<?php

namespace App\Twig;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_notifications_count', [$this, 'getUnreadCount']),
        ];
    }

    public function getUnreadCount(?string $type = null): int
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return 0;
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(n.id)')
            ->from(Notification::class, 'n')
            ->where('n.user = :user')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('user', $user);

        if ($type) {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $type);
        }

        return (int)$qb->getQuery()
            ->getSingleScalarResult();
    }
}
