<?php

namespace Artesaos\Defender\Commands;

use Illuminate\Console\Command;
use Artesaos\Defender\Contracts\Repositories\PermissionRepository;
use Artesaos\Defender\Contracts\Repositories\RoleRepository;
use Artesaos\Defender\Contracts\User as UserContract;

class MakePermission extends Command
{
    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var UserContract
     */
    protected $user;

    /**
     * Create a new command instance.
     *
     * @param PermissionRepository $permissionRepository
     * @param RoleRepository       $roleRepository
     * @param User                 $user
     */
    public function __construct(PermissionRepository $permissionRepository, RoleRepository $roleRepository, UserContract $user)
    {
        parent::__construct();

        $this->permissionRepository = $permissionRepository;
        $this->roleRepository       = $roleRepository;
        $this->user                 = $user;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'defender:make:permission
                            {name : Name of the permission}
                            {readableName : A readable name of the permission}
                            {--user= : User id. Attach permission to user with the provided id}
                            {--role= : Role name. Attach permission to role with the provided name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a permission';

    /**
     * Execute the command.
     */
    public function handle()
    {
        $name         = $this->argument('name');
        $readableName = $this->argument('readableName');
        $userId       = $this->option('user');
        $roleName     = $this->option('role');
        $permission   = $this->createPermission($name, $readableName);

        if ($userId)
            $this->attachPermissionToUser($permission, $userId);
        
        if ($roleName)
            $this->attachPermissionToRole($permission, $roleName);
    }

    /**
     * Create permission
     *
     * @param string $name
     * @param string $readableName
     * 
     * @return \Artesaos\Defender\Permission
     */
    protected function createPermission($name, $readableName)
    {
        // No need to check is_null($permission) as create() throwsException
        $permission = $this->permissionRepository->create($name, $readableName);
        $this->info('Permission created successfully');
        return $permission;
    }

    /**
     * Attach Permission to user
     *
     * @param \Artesaos\Defender\Permission $permission
     * @param int                           $userId
     */
    protected function attachPermissionToUser($permission, $userId) {
        // Check if user exists
        if($user = $this->user->findById($userId)) {
            $user->attachPermission($permission);
            $this->info('Permission attached successfully to user');
        }
        else {
            $this->error('Not possible to attach permission. User not found');
        }
    }

    /**
     * @param \Artesaos\Defender\Permission $permission
     * @param string                           $roleName
     */
    protected function attachPermissionToRole($permission, $roleName) {
        // Check if role exists
        if($role = $this->roleRepository->findByName($roleName)) {
            $role->attachPermission($permission);
            $this->info('Permission attached successfully to role');
        }
        else {
            $this->error('Not possible to attach permission. Role not found');
        }
    }
}
