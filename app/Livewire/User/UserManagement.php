<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class UserManagement extends Component
{
    use WithPagination;

    public $selected = [];
    public $selectAll = false;
    public $selectPage = false;
    
    // Modal state
    public $showInviteModal = false;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $active_status = true;

    #[Computed]
    public function users()
    {
        return User::query()
            ->select('id', 'name', 'email', 'active_status', 'created_at')
            ->latest()
            ->paginate(10);
    }

    public function updatedSelectPage($value)
    {
        $pageIds = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();

        if ($value) {
            $this->selected = array_unique(array_merge($this->selected, $pageIds));
        } else {
            $this->selected = array_diff($this->selected, $pageIds);
            $this->selectAll = false;
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = false;
        
        $pageIds = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();
        
        if (empty($pageIds)) {
            $this->selectPage = false;
            return;
        }

        $this->selectPage = count(array_intersect($pageIds, $this->selected)) === count($pageIds);
    }

    public function selectAllMatching()
    {
        $this->selectAll = true;
        $this->selectPage = true;
        // In a real app with filters, you'd apply filters here
        $this->selected = User::query()
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function openInviteModal()
    {
        $this->resetForm();
        $this->showInviteModal = true;
    }

    public function closeInviteModal()
    {
        $this->showInviteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->active_status = true;
        $this->resetErrorBag();
    }

    public function saveUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'active_status' => 'boolean',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'active_status' => $this->active_status,
        ]);

        session()->flash('message', 'User invited successfully!');
        
        $this->closeInviteModal();
        
        // Reset pagination to show the new user
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.user.user-management', [
            'users' => $this->users,
        ]);
    }
}
