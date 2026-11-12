<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Classroom;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

#[Layout('components.admin.layout')]
class UserManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $activeTab = 'siswa';
    
    public $search = '';
    public $statusFilter = '';
    public $classroomFilter = '';

    public $selectedUser = null;
    public $selectedUsers = [];
    
    // Edit Modal
    public $showEditModal = false;
    public $editName = '';
    public $editEmail = '';
    public $editNisn = '';
    public $editNip = '';
    public $editMapel = '';

    // Password Reset Modal
    public $showPasswordModal = false;
    public $generatedPassword = '';

    // Move Class Modal
    public $showChangeClassModal = false;
    public $newClassroomId = null;
    public $availableClassrooms = [];
    public $currentAcademicYear = null;

    // Import Modal
    public $showImportModal = false;
    public $importFile;

    protected $queryString = [
        'activeTab' => ['except' => 'siswa'],
        'search' => ['except' => ''], 
        'statusFilter' => ['except' => ''],
        'classroomFilter' => ['except' => '']
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingClassroomFilter() { $this->resetPage(); }
    public function updatingActiveTab() { 
        $this->resetPage(); 
        $this->search = '';
        $this->statusFilter = '';
        $this->classroomFilter = '';
        $this->selectedUsers = [];
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->selectedUsers = [];
        $this->resetPage();
    }

    public function editUser($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        
        $this->editName = $this->selectedUser->name;
        $this->editEmail = $this->selectedUser->email;
        $this->editNisn = $this->selectedUser->nisn;
        $this->editNip = $this->selectedUser->nip_nuptk;
        $this->editMapel = $this->selectedUser->subject;
        
        $this->showEditModal = true;
    }

    public function saveUser()
    {
        $rules = [
            'editName' => 'required|string|max:255',
            'editEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->selectedUser->id)],
        ];

        if ($this->selectedUser->role === 'siswa') {
            $rules['editNisn'] = ['nullable', 'string', 'max:50', Rule::unique('users', 'nisn')->ignore($this->selectedUser->id)];
        } else {
            $rules['editNip'] = ['nullable', 'string', 'max:50'];
            $rules['editMapel'] = ['nullable', 'string', 'max:100'];
        }

        $this->validate($rules);

        $this->selectedUser->name = $this->editName;
        $this->selectedUser->email = $this->editEmail;
        
        if ($this->selectedUser->role === 'siswa') {
            $this->selectedUser->nisn = $this->editNisn;
        } else {
            $this->selectedUser->nip_nuptk = $this->editNip;
            $this->selectedUser->subject = $this->editMapel;
        }

        $this->selectedUser->save();

        session()->flash('success', 'Data pengguna berhasil diperbarui.');
        $this->showEditModal = false;
        $this->selectedUser = null;
    }

    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        
        // Generate random 6 character password without similar characters
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $this->generatedPassword = substr(str_shuffle(str_repeat($chars, 3)), 0, 6);
        
        $user->password = Hash::make($this->generatedPassword);
        $user->needs_password_reset = true;
        $user->save();

        $this->selectedUser = $user;
        $this->showPasswordModal = true;
    }

    public function resetToDefaultPassword($userId)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($userId);
        
        $user->password = Hash::make('password');
        $user->needs_password_reset = true;
        $user->save();

        session()->flash('success', 'Sandi pengguna berhasil direset ke sandi default: password.');
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->generatedPassword = '';
        $this->selectedUser = null;
        session()->flash('success', 'Sandi sementara berhasil diatur.');
    }

    public function openChangeClassModal($userId)
    {
        $this->selectedUser = User::with('classrooms')->findOrFail($userId);
        
        if ($this->selectedUser->role !== 'siswa') {
            return;
        }

        $currentClassroom = $this->selectedUser->activeClassroom();
        
        if (!$currentClassroom) {
            session()->flash('error', 'Siswa tidak memiliki kelas aktif untuk tahun ajaran ini.');
            return;
        }

        $this->currentAcademicYear = $currentClassroom->academic_year_id;
        
        // Get other classrooms in the same academic year
        $this->availableClassrooms = Classroom::where('academic_year_id', $this->currentAcademicYear)
            ->where('id', '!=', $currentClassroom->id)
            ->get();
            
        $this->showChangeClassModal = true;
    }

    public function changeClass()
    {
        $this->validate([
            'newClassroomId' => 'required|exists:classrooms,id',
        ]);

        $classroom = Classroom::findOrFail($this->newClassroomId);
        
        // Validate it's in the same academic year
        if ($classroom->academic_year_id !== $this->currentAcademicYear) {
            $this->addError('newClassroomId', 'Kelas tujuan harus berada di tahun ajaran yang sama.');
            return;
        }

        // Remove old classrooms for the same academic year
        $oldClassroomIds = $this->selectedUser->classrooms()
            ->wherePivot('academic_year_id', $classroom->academic_year_id)
            ->pluck('classrooms.id')
            ->toArray();

        if (count($oldClassroomIds) > 0) {
            $this->selectedUser->classrooms()->detach($oldClassroomIds);
        }

        // Insert new classroom
        $this->selectedUser->classrooms()->attach(
            $classroom->id, ['academic_year_id' => $classroom->academic_year_id]
        );

        session()->flash('success', 'Kelas siswa berhasil dipindahkan.');
        $this->showChangeClassModal = false;
        $this->newClassroomId = null;
        $this->selectedUser = null;
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);

        if (auth()->id() == $user->id) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            return;
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        session()->flash('success', 'Status pengguna berhasil diperbarui.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        if (auth()->id() == $user->id) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        $user->delete();

        session()->flash('success', 'Akun pengguna berhasil dihapus.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedUsers)) {
            return;
        }

        $count = 0;
        foreach ($this->selectedUsers as $userId) {
            if (auth()->id() != $userId) {
                User::where('id', $userId)->delete();
                $count++;
            }
        }

        $this->selectedUsers = [];
        session()->flash('success', "$count akun pengguna berhasil dihapus.");
    }

    public function importSiswa()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        require_once app_path('Helpers/SimpleXLSX.php');
        
        if ( $xlsx = \Shuchkin\SimpleXLSX::parse( $this->importFile->getRealPath() ) ) {
            $rows = $xlsx->rows();
            $header = array_shift($rows); // Remove header
            
            $activeClassrooms = Classroom::where('academic_year_id', function($q) {
                $q->select('id')->from('academic_years')->where('is_active', true)->limit(1);
            })->get();
            
            $count = 0;
            foreach ($rows as $row) {
                // Ensure row has at least 5 elements to avoid undefined array key errors
                $row = array_pad($row, 5, '');
                
                $name = trim($row[1]);
                $nisn = trim($row[2]);
                $kelasName = trim($row[3]);
                $email = trim($row[4]);
                
                // Assuming format: No, Nama, NISN, Kelas, Email
                if (!empty($name) && !empty($email)) {
                    
                    if (!User::where('email', $email)->orWhere('nisn', $nisn)->exists()) {
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make($nisn ?: '12345678'), // Default password if no NISN
                            'role' => 'siswa',
                            'nisn' => $nisn,
                            'status' => 'active',
                        ]);
                        
                        // Assign classroom if matched
                        if (!empty($kelasName)) {
                            // Find classroom by combining grade and major
                            $matchedClassroom = $activeClassrooms->first(function ($c) use ($kelasName) {
                                $dbName = preg_replace('/\s+/', '', strtolower($c->grade . $c->major));
                                $dbFullName = preg_replace('/\s+/', '', strtolower($c->name));
                                $excelName = preg_replace('/\s+/', '', strtolower($kelasName));
                                
                                if ($dbName === $excelName || $dbFullName === $excelName) return true;
                                
                                // Handle common aliases
                                $excelNameAliases = str_replace(
                                    ['akuntansi', 'pemasaran', 'perkantoran', 'rekayasaperangkatlunak', 'multimedia', 'teknikkomputer', 'pplg', 'tjkt'],
                                    ['akl', 'pm', 'otkp', 'rpl', 'mm', 'tkj', 'rpl', 'tkj'],
                                    $excelName
                                );
                                
                                return $dbName === $excelNameAliases || $dbFullName === $excelNameAliases || str_contains($excelName, strtolower($c->major)) || str_contains(strtolower($c->major), $excelName) || str_contains(strtolower($c->name), strtolower($kelasName));
                            });
                            
                            if ($matchedClassroom) {
                                $user->classrooms()->attach(
                                    $matchedClassroom->id, 
                                    ['academic_year_id' => $matchedClassroom->academic_year_id]
                                );
                            }
                        }
                        
                        $count++;
                    }
                }
            }
            
            session()->flash('success', "Berhasil mengimpor $count data siswa baru.");
        } else {
            session()->flash('error', 'Gagal membaca file Excel. ' . \Shuchkin\SimpleXLSX::parseError());
        }

        $this->showImportModal = false;
        $this->importFile = null;
    }

    public function render()
    {
        $siswaCount = User::where('role', 'siswa')->count();
        $guruCount = User::where('role', 'guru')->count();
        
        $classroomsQuery = Classroom::where('academic_year_id', function($q) {
            $q->select('id')->from('academic_years')->where('is_active', true)->limit(1);
        })->get();

        $users = User::query()
            ->where('role', $this->activeTab)
            ->when($this->search, function($q) {
                $q->where(function($q2) {
                    $q2->where('name', 'like', '%'.$this->search.'%')
                       ->orWhere('email', 'like', '%'.$this->search.'%')
                       ->orWhere('nisn', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->activeTab === 'siswa' && $this->classroomFilter, function($q) {
                $q->whereHas('classrooms', function($q2) {
                    $q2->where('classrooms.id', $this->classroomFilter);
                });
            })
            ->paginate(15);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'siswaCount' => $siswaCount,
            'guruCount' => $guruCount,
            'classroomsList' => $classroomsQuery
        ]);
    }
}
