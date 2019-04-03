<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-02-07
 * Time: 14:11
 */

namespace App\Security\Voter;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AdminVoter extends Voter
{

    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy = null)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports($attribute, $subject)
    {
        $attributes_allowed = [
            'CAN_EDIT_USER',
            'CAN_CHANGE_STATUS',
            'CAN_EDIT_PERMISSIONS',
            'CAN_VIEW_HISTORY',
            'CAN_VIEW_REPORTS',
            'CAN_MODIFY_SELF'
        ];
        return in_array($attribute, $attributes_allowed);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $allowed = ['ROLE_ADMIN'];
        $allowed[] = $attribute;
        foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
            if (in_array($role->getRole(), $allowed)) {
                return true;
            }
        }

        return false;
    }
}