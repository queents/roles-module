<?php

namespace Modules\Roles\Vilt\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Modules\Base\Services\Components\Base\Filter;
use Modules\Base\Services\Components\Base\Table;
use Modules\Base\Services\Resource\Resource;
use Modules\Base\Services\Rows\Email;
use Modules\Base\Services\Rows\Relation;
use Modules\Base\Services\Rows\Text;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    public ?string $model = User::class;
    public string $icon = "bx bxs-user";
    public string $group = "ALC";

    public function rows(): array
    {
        return [
            Text::make('id')->label(__('ID'))->edit(false)->create(false),
            Text::make('name')->label(__('Name'))
                ->searchable(true)
                ->validation([
                    "create" => 'required|string|max:199',
                    "update" => 'required|string|max:199'
                ]),
            Email::make('email')
                ->label(__('Email'))
                ->searchable()
                ->validation([
                    "create" => 'required|string|email|max:199',
                    "update" => 'required|string|email|max:199'
                ])
                ->unique(),
            Text::make('password')
                ->label(__('Password'))
                ->validation([
                    "create" => 'required|string|confirmed|min:6',
                    "update" => 'sometimes|string|confirmed|min:6'
                ])
                ->type('password')
                ->list(false)
                ->view(false),
            Text::make('password_confirmation')
                ->validation([
                    "create" => 'required|string|min:6',
                    "update" => 'sometimes|string|min:6'
                ])
                ->label(__('Password Confirmation'))
                ->type('password')
                ->list(false)
                ->view(false),
            Relation::make('roles')
                ->label(__('Roles'))
                ->validation(['array'])
                ->type('relation')
                ->model(Role::class)
                ->relation('roles')
                ->multi(true)
                ->sortable(false),
        ];
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

    public function setFilters($query, Request $request): void
    {
        if($request->has('roles') && !empty($request->get('roles'))) {
            $query->whereHas('roles', function($query) use ($request) {
                $query->whereIn('id', $request->get('roles'));
            });
        }
    }

    public function table(): Table
    {
        return Table::make('table')->filters([
            Filter::make('roles')->label(__('Filter By Roles'))->rows([
                Relation::make('roles')->model(Role::class)->relation('roles')
            ])
        ]);
    }

    public function beforeStore(Request $request): Request
    {
        $request['password'] = bcrypt($request['password']);

        return $request;
    }

    public function afterStore(Request $request, $record): void
    {
        if ($request->has('roles')) {
            foreach ($request->get('roles') as $role) {
                $record->assignRole($role['name']);
            }
        }
    }

    public function beforeUpdate(Request $request, $record): Request
    {
        $request['password'] = bcrypt($request['password']);

        return $request;
    }

    public function afterUpdate(Request $request, $record): void
    {
        if ($request->has('roles')) {
            foreach ($request->get('roles') as $role) {
                $record->syncRoles($role['name']);
            }
        }
    }
}
