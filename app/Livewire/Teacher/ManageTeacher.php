<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Teacher\Teacher;
use App\Models\User;
use Illuminate\Validation\Rule;

class ManageTeacher extends Component
{
    use WithPagination;

    /**
     * Search term for filtering teachers by name, email, employee no, or department.
     *
     * @var string
     */
    public $search = '';

    // Modal state
    public $showCreateModal = false;
    public $showDeleteTeacherModal = false;

    // Form fields
    public $teacherId = null;
    public $userId = null;
    public $employeeNo = '';
    public $department = '';
    public $position = '';
    public $hireDate = '';
    public $deleteTeacherId = null;
    public $deleteTeacherName = null;

    /**
     * Reset pagination when the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the create teacher modal.
     */
    public function openCreateModal(): void
    {
        $this->teacherId = null;
        $this->resetForm();
        $this->showCreateModal = true;
    }

    /**
     * Open the edit modal for an existing teacher.
     */
    public function editTeacher(int $teacherId): void
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        $this->teacherId = $teacher->id;
        $this->userId = $teacher->user_id;
        $this->employeeNo = $teacher->employee_no ?? '';
        $this->department = $teacher->department ?? '';
        $this->position = $teacher->position ?? '';
        $this->hireDate = $teacher->hire_date ? $teacher->hire_date->format('Y-m-d') : '';

        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    /**
     * Close the create/edit modal and reset the form.
     */
    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    /**
     * Open delete confirmation modal for a teacher.
     */
    public function deleteTeacher(int $teacherId): void
    {
        $teacher = Teacher::with('user')->find($teacherId);

        if (!$teacher) {
            $this->dispatch('show-toast', [
                'message' => 'Teacher not found.',
                'type' => 'error',
                'title' => 'Error',
            ]);
            return;
        }

        $this->deleteTeacherId = $teacher->id;
        $this->deleteTeacherName = $teacher->user?->name ?? 'this teacher';
        $this->showDeleteTeacherModal = true;
    }

    /**
     * Confirm and perform teacher deletion.
     */
    public function confirmDeleteTeacher(): void
    {
        if (!$this->deleteTeacherId) {
            return;
        }

        $teacher = Teacher::with('user')->findOrFail($this->deleteTeacherId);
        $teacherName = $teacher->user?->name ?? 'Teacher';

        $teacher->delete();

        $this->closeDeleteTeacherModal();
        $this->resetPage();

        $this->dispatch('show-toast', [
            'message' => 'Teacher "' . $teacherName . '" deleted successfully!',
            'type' => 'success',
            'title' => 'Teacher Deleted',
        ]);
    }

    /**
     * Close delete confirmation modal and reset state.
     */
    public function closeDeleteTeacherModal(): void
    {
        $this->showDeleteTeacherModal = false;
        $this->deleteTeacherId = null;
        $this->deleteTeacherName = null;
    }

    /**
     * Reset form fields.
     */
    public function resetForm(): void
    {
        $this->userId = null;
        $this->employeeNo = '';
        $this->department = '';
        $this->position = '';
        $this->hireDate = '';
        $this->resetErrorBag();
    }

    /**
     * Create or update a teacher record.
     */
    public function saveTeacher(): void
    {
        $isEditing = !is_null($this->teacherId);

        $rules = [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'employeeNo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('teachers', 'employee_no')->ignore($this->teacherId),
            ],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'hireDate' => ['nullable', 'date'],
        ];

        $this->validate($rules);

        $user = User::findOrFail($this->userId);

        // Ensure a user can only have one teacher profile
        $existingForUser = Teacher::where('user_id', $user->id)
            ->when($isEditing, function ($q) {
                $q->where('id', '!=', $this->teacherId);
            })
            ->exists();

        if ($existingForUser) {
            $this->addError('userId', 'This user already has a teacher profile.');
            return;
        }

        $data = [
            'user_id' => $user->id,
            'employee_no' => $this->employeeNo ?: null,
            'department' => $this->department ?: null,
            'position' => $this->position ?: null,
            'hire_date' => $this->hireDate ?: null,
        ];

        if ($isEditing) {
            $teacher = Teacher::findOrFail($this->teacherId);
            $teacher->update($data);
            $message = 'Teacher updated successfully!';
        } else {
            $teacher = Teacher::create($data);
            $message = 'Teacher created successfully!';
            $this->resetPage();
        }

        $this->closeCreateModal();

        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success',
        ]);
    }

    public function render()
    {
        $query = Teacher::query()
            ->with('user');

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';

            $query->where(function ($q) use ($search) {
                $q->where('employee_no', 'like', $search)
                    ->orWhere('department', 'like', $search)
                    ->orWhere('position', 'like', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        $teachers = $query->latest()->paginate(10);

        $userOptions = User::role('admin')
            ->orderBy('name')
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->name . ' (' . $user->email . ')',
                ];
            })
            ->toArray();

        return view('livewire.teacher.manage-teacher', [
            'teachers' => $teachers,
            'userOptions' => $userOptions,
        ]);
    }
}
