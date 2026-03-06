<?php

namespace App\Policies;

use App\Models\Router;
use App\Models\User;

class RouterPolicy
{
    /**
     * Determine if the user can view any routers.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageRouters();
    }

    /**
     * Determine if the user can view the router.
     */
    public function view(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can create routers.
     */
    public function create(User $user): bool
    {
        return $user->canManageRouters();
    }

    /**
     * Determine if the user can update the router.
     */
    public function update(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can delete the router.
     */
    public function delete(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can manage the router (general management actions).
     */
    public function manage(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can test connection to the router.
     */
    public function testConnection(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can sync users from the router.
     */
    public function syncUsers(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can disconnect users from the router.
     */
    public function disconnect(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can manage VPN for the router.
     */
    public function manageVpn(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can withdraw from the router wallet.
     */
    public function withdraw(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }

    /**
     * Determine if the user can download the router portal.
     */
    public function downloadPortal(User $user, Router $router): bool
    {
        return $user->ownsRouter($router);
    }
}
