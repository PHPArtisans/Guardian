<?php

namespace Artesaos\Defender\Traits;

use Artesaos\Defender\Traits\Users\HasPermissions;
use Artesaos\Defender\Traits\Users\HasRoles;

/**
 * Trait HasDefender.
 */
trait HasDefender
{
    use HasRoles, HasPermissions;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $cachedPermissions;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $cachedRolePermissions;

    /**
     * Returns if the current user has the given permission.
     * User permissions override role permissions.
     *
     * @param string $permission
     * @param bool   $force
     *
     * @return bool
     */
    public function hasPermission($permission, $force = false)
    {
        $permissions = $this->getAllPermissions($force)->pluck('name')->toArray();

        if (strpos($permission, '*')) {
            $permission = substr($permission, 0, -2);

            return (bool) preg_grep('~'.$permission.'~', $permissions);
        }

        return in_array($permission, $permissions);
    }

    /**
     * Returns if the current user has all or one permission of the given array.
     * User permissions override role permissions.
     *
     * @param array $permissions Array of permissions
     * @param bool $strict       Check if has all permissions from array or one of them
     * @param bool $force
     * @return bool
     */
    public function hasPermissions(array $permissions, $strict = true, $force = false)
    {
        $allPermissions = $this->getAllPermissions($force)->pluck('name')->toArray();
        $equalPermissions = array_intersect($permissions, $allPermissions);
        $countEqual = count($equalPermissions);
        if ($countEqual > 0 && ($strict === false || $countEqual === count($permissions))) {
            return true;
        }

        return false;
    }

    /**
     * Checks for permission
     * If has superuser group automatically passes.
     *
     * @param string $permission
     * @param bool   $force
     *
     * @return bool
     */
    public function canDo($permission, $force = false)
    {
        // If has superuser role
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->hasPermission($permission, $force);
    }

    /**
     * check has superuser role.
     *
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->hasRole(config('defender.superuser_role', 'superuser'));
    }

    /**
     * Check if the user has the given permission using
     * only his roles.
     *
     * @param string $permission
     * @param bool   $force
     *
     * @return bool
     */
    public function roleHasPermission($permission, $force = false)
    {
        $permissions = $this->getRolesPermissions($force)->pluck('name')->toArray();

        return in_array($permission, $permissions);
    }

    /**
     * Retrieve all user permissions.
     *
     * @param bool $force
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions($force = false)
    {
        if (empty($this->cachedPermissions) or $force) {
            $this->cachedPermissions = $this->getFreshAllPermissions();
        }

        return $this->cachedPermissions;
    }

    /**
     * Get permissions from database based on roles.
     *
     * @param bool $force
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRolesPermissions($force = false)
    {
        if (empty($this->cachedRolePermissions) or $force) {
            $this->cachedRolePermissions = $this->getFreshRolesPermissions();
        }

        return $this->cachedRolePermissions;
    }

    /**
     * Get fresh permissions from database based on roles.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFreshRolesPermissions()
    {
        $roles = $this->roles()->get(['id'])->pluck('id')->toArray();

        return app('defender.permission')->getByRoles($roles);
    }

    /**
     * Get fresh permissions from database.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFreshAllPermissions()
    {
        $permissionsRoles = $this->getRolesPermissions(true);

        $permissions = app('defender.permission')->getActivesByUser($this);
	    $permissionsInactives = app('defender.permission')->getInactivesByUser($this);

        $permissions = $permissions->merge($permissionsRoles)
            ->map(function ($permission) use($permissionsInactives) {
	            unset($permission->pivot, $permission->created_at, $permission->updated_at);

	            if(!$permissionsInactives->whereIn('id', $permission->id)->first()){
		            return $permission;
	            }
            })->filter();

        return $permissions->toBase();
    }

    /**
     * Find a user by its id.
     *
     * @param int $id
     *
     * @return \Artesaos\Defender\Contracts\User
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
