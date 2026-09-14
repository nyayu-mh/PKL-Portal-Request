<?php

namespace App\Livewire\Setting;

use App\Models\MenuPermission;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class GroupAccessManager extends Component
{
    public string $selectedRole;

    /**
     * @var array<string, array{view: bool, create: bool, update: bool, delete: bool}>
     */
    public array $perms = [];

    public function mount(): void
    {
        $this->selectedRole = array_key_first(MenuPermission::groupLabels());
        $this->loadPerms();
    }

    public function updatedSelectedRole(): void
    {
        $this->loadPerms();
    }

    protected function loadPerms(): void
    {
        $saved = MenuPermission::where('role', $this->selectedRole)->get()->keyBy('menu_key');

        $perms = [];
        foreach (array_keys(MenuPermission::menuList()) as $key) {
            $row = $saved->get($key);
            // Default: menu terlihat (sesuai perilaku aplikasi sebelum fitur ini ada), tambah/ubah/hapus belum diatur.
            $perms[$key] = [
                'view' => $row ? (bool) $row->can_view : true,
                'create' => $row ? (bool) $row->can_create : false,
                'update' => $row ? (bool) $row->can_update : false,
                'delete' => $row ? (bool) $row->can_delete : false,
            ];
        }

        $this->perms = $perms;
    }

    public function save(): void
    {
        foreach ($this->perms as $menuKey => $p) {
            MenuPermission::updateOrCreate(
                ['role' => $this->selectedRole, 'menu_key' => $menuKey],
                [
                    'can_view' => (bool) ($p['view'] ?? false),
                    'can_create' => (bool) ($p['create'] ?? false),
                    'can_update' => (bool) ($p['update'] ?? false),
                    'can_delete' => (bool) ($p['delete'] ?? false),
                ],
            );
        }

        session()->flash('success', 'Hak akses grup "'.MenuPermission::groupLabels()[$this->selectedRole].'" berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.setting.group-access-manager', [
            'groupOptions' => MenuPermission::groupLabels(),
            'menuOptions' => MenuPermission::menuList(),
        ]);
    }
}
