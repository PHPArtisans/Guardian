<?php

namespace Artesaos\Defender\Repositories\Eloquent;

use Artesaos\Defender\Contracts\Permission;
use Artesaos\Defender\Contracts\Repositories\PermissionRepository;
use Artesaos\Defender\Exceptions\PermissionExistsException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class EloquentPermissionRepository.
 */
class EloquentPermissionRepository extends AbstractEloquentRepository implements PermissionRepository
{
    /**
     * @param Application $app
     * @param Permission  $model
     */
    public function __construct(Application $app, Permission $model)
    {
        parent::__construct($app, $model);
    }

    /**
     * Create a new permission using the given name.
     *
     * @param string $permissionName
     * @param string $readableName
     *
     * @throws PermissionExistsException
     *
     * @return Permission
     */
    public function create($permissionName, $readableName = null)
    {
        if (! is_null($this->findByName($permissionName))) {
            throw new PermissionExistsException('The permission '.$permissionName.' already exists'); // TODO: add translation support
        }

        // Do we have a display_name set?
        $readableName = is_null($readableName) ? $permissionName : $readableName;

        return $permission = $this->model->create([
            'name'          => $permissionName,
            'readable_name' => $readableName,
        ]);
    }

    /**
     * @param array $rolesIds
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByRoles(array $rolesIds)
    {
        return $this->model->whereHas('roles', function ($query) use ($rolesIds) {
            $query->whereIn('id', $rolesIds);
        })->get();
    }

    /**
     * @param $user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivesByUser($user)
    {
        $table = $user->permissions()->getTable();

        return $user->permissions()
            ->where($table.'.value', true)
            ->where(function ($q) use ($table) {
                $q->where($table.'.expires', '>=', Carbon::now());
                $q->orWhereNull($table.'.expires');
            })
            ->get();
    }

	/**
	 * @param $user
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getInactivesByUser($user)
	{
		$table = $user->permissions()->getTable();

		return $user->permissions()
			->where($table.'.value', false)
			->where(function ($q) use ($table) {
				$q->where($table.'.expires', '>=', Carbon::now());
				$q->orWhereNull($table.'.expires');
			})
			->get();
	}
}
