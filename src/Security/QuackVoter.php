<?php

namespace App\Security;

use App\Entity\Duck;
use App\Entity\Quack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class QuackVoter extends Voter
{
    const EDIT = 'edit';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        // only vote on `Quack` objects
        if (!$subject instanceof Quack) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // unleash all the admin mighty powers
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof Duck) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Quack object, thanks to `supports()`
        /** @var Quack $quack */
        $quack = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($quack, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Quack $quack, Duck $duck): bool
    {
        // this assumes that the Quack object has a `getDuck()` method
        return $duck === $quack->getDuck() || $duck === $quack->getParent()->getDuck();
    }
}
