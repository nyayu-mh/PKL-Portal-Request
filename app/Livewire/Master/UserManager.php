<?php

namespace App\Livewire\Master;

use App\Livewire\Concerns\WithCsvImport;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class UserManager extends Component
{
    use WithCsvImport, WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $divisi = '';

    public string $jabatan = '';

    public string $jabatan_level = 'staff';

    public string $role = 'karyawan';

    public ?int $atasan_id = null;

    public bool $is_active = true;

    public bool $showForm = false;

    protected function rules(): array
    {
        $emailRule = 'required|email|max:150|unique:users,email';
        if ($this->editingId) {
            $emailRule .= ','.$this->editingId;
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => explode('|', $emailRule),
            'password' => [$this->editingId ? 'nullable' : 'required', 'min:8'],
            'divisi' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'jabatan_level' => ['required', 'in:staff,junior_leader,leader,manager'],
            'role' => ['required', 'in:karyawan,hr,ga,admin'],
            'atasan_id' => ['nullable', 'exists:users,id', 'different:editingId'],
            'is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->password = '';
        $this->divisi = (string) $u->divisi;
        $this->jabatan = (string) $u->jabatan;
        $this->jabatan_level = $u->jabatan_level;
        $this->role = $u->role;
        $this->atasan_id = $u->atasan_id;
        $this->is_active = $u->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        User::updateOrCreate(['id' => $this->editingId], $data);

        session()->flash('success', 'Data user berhasil disimpan.');
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $u = User::findOrFail($id);

        if ($u->id === auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menonaktifkan akun sendiri.');

            return;
        }

        $u->update(['is_active' => ! $u->is_active]);
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'divisi', 'jabatan', 'atasan_id', 'showForm']);
        $this->jabatan_level = 'staff';
        $this->role = 'karyawan';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    // ── Import CSV ────────────────────────────────────────────────

    public function csvTemplateName(): string
    {
        return 'template-user.csv';
    }

    public function csvTemplateHeaders(): array
    {
        return ['name', 'email', 'password', 'divisi', 'jabatan', 'jabatan_level', 'role', 'atasan_email', 'is_active'];
    }

    public function csvTemplateExample(): array
    {
        return [
            ['Contoh Leader', 'leader2@brilliantthinkcenter.com', '', 'IT', 'IT Team Leader', 'leader', 'karyawan', 'manager@brilliantthinkcenter.com', 'aktif'],
            ['Contoh Manager', 'manager2@brilliantthinkcenter.com', 'rahasia123', 'Operasional', 'Ops Manager', 'manager', 'karyawan', '', 'aktif'],
        ];
    }

    public function csvImportHint(): string
    {
        return 'Wajib: name, email. "password" boleh kosong untuk user baru -> otomatis "password". '
            .'"jabatan_level": staff / junior_leader / leader / manager. "role": karyawan / hr / ga / admin. '
            .'"atasan_email": email atasan langsung (harus sudah terdaftar; kalau atasannya ikut di file yang sama, jalankan import 2x). '
            .'Email yang sudah ada akan diperbarui; password hanya berubah kalau kolomnya diisi.';
    }

    public function importCsvRow(array $row, int $line): void
    {
        $name = (string) ($row['name'] ?? '');
        $email = (string) ($row['email'] ?? '');
        if ($name === '' || $email === '') {
            throw new \RuntimeException('kolom "name" dan "email" wajib diisi.');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("email \"{$email}\" tidak valid.");
        }

        $existing = User::where('email', $email)->first();

        $level = $this->csvChoice($row['jabatan_level'] ?? '', [
            'staff' => 'staff',
            'junior leader' => 'junior_leader', 'junior_leader' => 'junior_leader',
            'leader' => 'leader',
            'manager' => 'manager',
        ], $existing->jabatan_level ?? 'staff');

        $role = $this->csvChoice($row['role'] ?? '', [
            'karyawan' => 'karyawan',
            'hr' => 'hr', 'tim hr' => 'hr',
            'ga' => 'ga', 'tim ga' => 'ga', 'tim general affair' => 'ga',
            'admin' => 'admin', 'administrator' => 'admin',
        ], $existing->role ?? 'karyawan');

        $atasanId = $existing->atasan_id ?? null;
        $atasanEmail = (string) ($row['atasan_email'] ?? '');
        if ($atasanEmail !== '') {
            if (Str::lower($atasanEmail) === Str::lower($email)) {
                throw new \RuntimeException('atasan_email tidak boleh sama dengan email user sendiri.');
            }
            $atasan = User::where('email', $atasanEmail)->first();
            if (! $atasan) {
                $this->importNote("Baris {$line}: atasan \"{$atasanEmail}\" belum terdaftar — user tetap disimpan tanpa atasan. Jalankan import lagi setelah semua user masuk.");
            } else {
                $atasanId = $atasan->id;
            }
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'divisi' => ($row['divisi'] ?? '') !== '' ? $row['divisi'] : null,
            'jabatan' => ($row['jabatan'] ?? '') !== '' ? $row['jabatan'] : null,
            'jabatan_level' => $level,
            'role' => $role,
            'atasan_id' => $atasanId,
            'is_active' => $this->csvBool($row['is_active'] ?? '', true),
        ];

        $password = (string) ($row['password'] ?? '');

        if ($existing) {
            if ($password !== '') {
                if (strlen($password) < 8) {
                    throw new \RuntimeException('password minimal 8 karakter.');
                }
                $data['password'] = Hash::make($password);
            }
            $existing->update($data);

            return;
        }

        if ($password === '') {
            $password = 'password';
            $this->importNote("Baris {$line}: password kosong untuk \"{$email}\" — dipakai default \"password\".");
        } elseif (strlen($password) < 8) {
            throw new \RuntimeException('password minimal 8 karakter.');
        }
        $data['password'] = Hash::make($password);

        User::create($data);
    }

    private function csvChoice(string $value, array $map, string $default): string
    {
        $v = Str::lower(trim($value));
        if ($v === '') {
            return $default;
        }

        return $map[$v] ?? $default;
    }

    public function render()
    {
        return view('livewire.master.user-manager', [
            'items' => User::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
                ->orderBy('name')->paginate(10),
            'jabatanLevelOptions' => User::jabatanLevelLabels(),
            'roleOptions' => User::roleLabels(),
            'atasanOptions' => User::where('is_active', true)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->orderBy('name')->get(),
        ]);
    }
}
