<?php

namespace Modules\Roles\Vilt\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Base\Helpers\Resources\Alert;
use Modules\Base\Services\Components\Base\Action;
use Modules\Base\Services\Components\Base\AddRoute;
use Modules\Base\Services\Components\Base\Form;
use Modules\Base\Services\Components\Base\Modal;
use Modules\Base\Services\Components\Base\Render;
use Modules\Base\Services\Resource\Resource;
use Modules\Base\Services\Rows\Relation;
use Modules\Base\Services\Rows\Select;
use Modules\Base\Services\Rows\Text;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesResource extends Resource
{
    public ?string $model = Role::class;
    public ?string $module = "Roles";
    public string $icon = "bx bxs-lock-alt";
    public string $group = "ALC";

    public function rows(): array
    {
        return [
            Text::make('id')->label(__('ID'))->edit(false)->create(false),
            Text::make('name')->label(__('Name'))->searchable()->validation([
                "create" => 'required|string|max:199',
                "update" => 'required|string|max:199'
            ]),
            Text::make('guard_name')->searchable()->label(__('Guard Name'))->validation([
                "create" => 'required|string|max:199',
                "update" => 'required|string|max:199'
            ]),
            Relation::make('permissions')
                ->validation([
                    "create" => 'required|array|min:1',
                    "update" => 'required|array|min:1'
                ])
                ->label(__('Permissions'))
                ->type('relation')
                ->list(false)
                ->model(Permission::class),
       ];
    }

    public function create(Request $request): \Inertia\Response
    {
        /*
         * Check if user has role to create a new record
         */
        if ($this->checkRoles('canCreate') && !$this->isAPI($request)) {
            return $this->checkRoles('canCreate');
        }

        $rows = $this->rows();

        $prem = Permission::all()->makeHidden(['pivot', 'created_at', 'updated_at']);

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();


        $permGroup = [];
        foreach($tables as $item){
            $permGroupGet = [];
            foreach($prem as $key=>$p){
                if(Str::endsWith($p->name, '_' . $item) && $p->guard_name == 'web'){
                    $p->table= $item;
                    $permGroupGet[] = $p;
                }
            }

            if(count($permGroupGet) > 0){
                $permGroup[$item] = $permGroupGet;
            }
        }

        return Render::make(ucfirst(Str::camel($this->table)).'/Create')->module($this->module)->data([
            "rows" => $rows,
            "url" => $this->table,
            "perm" => $permGroup
        ])->render();
    }

    public function edit(Request $request, $id): \Inertia\Response
    {
        /*
         * Check if user has role to create a new record
         */
        if ($this->checkRoles('canEdit') && !$this->isAPI($request)) {
            return $this->checkRoles('canEdit');
        }

        $record = Role::with('permissions')->find($id);

        $prem = Permission::all()->makeHidden(['pivot', 'created_at', 'updated_at']);

        $rows = $this->rows();

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        $permGroup = [];
        foreach($tables as $item){
            $permGroupGet = [];
            foreach($prem as $key=>$p){
                if(Str::endsWith($p->name , '_' . $item) && $p->guard_name == 'web'){
                    $p->table= $item;
                    $permGroupGet[] = $p;
                }
            }

            if(count($permGroupGet) > 0){
                $permGroup[$item] = $permGroupGet;
            }
        }

        foreach($tables as $getTable) {
            $record->permissions->map(function ($item) use ($getTable) {
                if (Str::endsWith($item->name, '_' . $getTable) && $item->guard_name == 'web') {
                    $item->table = $getTable;
                }
                return $item;
            })->makeHidden(['pivot', 'created_at', 'updated_at']);
        }

        return Render::make(ucfirst(Str::camel($this->table)).'/Edit')->module($this->module)->data([
            "perm" => $permGroup,
            "rows" => $rows,
            "url" => $this->table,
            "record" => $record
        ])->render();
    }

    public function form(): Form
    {
        return Form::make('page');
    }

    public function loadTranslations(): array
    {
        return [
            "index" => __($this->generateName()),
            "create" => __('Create ' . $this->generateName(true, true)),
            "bulk" => __('Delete Selected ' . $this->generateName(true, true)),
            "edit_title" => __('Edit ' . $this->generateName(true, true)),
            "create_title" => __('Create New ' . $this->generateName(true, true)),
            "view_title" => __('View ' . $this->generateName(true, true)),
            "delete_title" => __('Delete ' . $this->generateName(true, true)),
            "bulk_title" => __('Run Bulk Action To ' . $this->generateName(true, true)),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make('generate')->label(__('Generate Permissions'))->type('success')->modal('generate'),
        ];
    }

    public function modals(): array
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        return [
            Modal::make('generate')->label(__('Generate Permissions'))->type('success')
                ->rows([
                    Text::make('guard_name')->label(__('Guard Name')),
                    Select::make('tables')
                        ->label(__('Tables'))
                        ->multi(true)
                        ->type('select')
                        ->trackByName(null)
                        ->trackById(null)
                        ->options($tables),
                ])
                ->buttons([
                    Action::make('generate')->label(__('Generate'))->action('roles.generate')->type('success'),
                ]),
        ];
    }

    public function route(): array
    {
        return [
            AddRoute::make('generate')->type('post')->method('generate')->path('generate')->controller(static::class)
        ];
    }

    public function generate(Request $request)
    {
        /*
         * Check if user has role to create a new record
         */
        if ($this->checkRoles('canCreate') && !$this->isAPI($request)) {
            return $this->checkRoles('canCreate');
        }

        $rules = [
            "tables" => "required|array",
            "guard_name" => "required|string",
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        if (!$validator->fails()) {
            foreach ($request->get('tables') as $table) {
                $checkView = Permission::where('name', 'view_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkView) {
                    Permission::create(['name' => 'view_' . $table, 'guard_name' => $request->guard_name]);
                }
                $checkViewAny = Permission::where('name', 'view_any_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkViewAny) {
                    Permission::create(['name' => 'view_any_' . $table, 'guard_name' => $request->guard_name]);
                }
                $checkCreate = Permission::where('name', 'create_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkCreate) {
                    Permission::create(['name' => 'create_' . $table, 'guard_name' => $request->guard_name]);
                }
                $checkUpdate = Permission::where('name', 'update_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkUpdate) {
                    Permission::create(['name' => 'update_' . $table, 'guard_name' => $request->guard_name]);
                }
                $checkDelete = Permission::where('name', 'delete_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkDelete) {
                    Permission::create(['name' => 'delete_' . $table, 'guard_name' => $request->guard_name]);
                }
                $checkExport = Permission::where('name', 'export_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkExport) {
                    Permission::create(['name' => 'export_' . $table, 'guard_name' => $request->guard_name]);
                }
                $checkDeleteAny = Permission::where('name', 'delete_any_' . $table)->where('guard_name', $request->guard_name)->first();
                if (!$checkDeleteAny) {
                    Permission::create(['name' => 'delete_any_' . $table, 'guard_name' => $request->guard_name]);
                }
            }

            return Alert::make(__("Permission Generated Success"))->fire();
        }
    }

    public function afterStore(Request $request, $record): void
    {
        if ($request->has('permissions')) {
            foreach ($request->get('permissions') as $permission) {
                $record->givePermissionTo($permission['name']);
            }
        }
    }

    public function afterUpdate(Request $request, $record): void
    {
        if ($request->has('permissions')) {
            $getPermArray = [];
            foreach ($request->get('permissions') as $permission) {
                array_push($getPermArray, $permission['name']);
            }

            $record->syncPermissions($getPermArray);
        }
    }
}
