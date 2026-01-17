<?php

namespace App\Livewire\Management;

use App\Models\SystemSetting;
use App\Enums\SystemSettingGroup;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class SystemSettings extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;

    // Modal state
    public $showSettingModal = false;
    public $showDeleteModal = false;
    public $deleteSettingId = null;
    public $deleteSettingName = '';
    
    // Form fields
    public $settingId = null;
    public $key = '';
    public $name = '';
    public $value = '';
    public $type = 'text';
    public $group = '';
    public $isLocked = false;
    public $file = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showSettingModal = true;
    }

    public function closeSettingModal()
    {
        $this->showSettingModal = false;
        $this->resetForm();
    }

    public function editSetting($id)
    {
        $setting = SystemSetting::findOrFail($id);
        
        $this->settingId = $setting->id;
        $this->key = $setting->key;
        $this->name = $setting->name;
        $this->value = $setting->value;
        $this->type = $setting->type;
        $this->group = (string) $setting->group;
        $this->isLocked = $setting->is_locked;
        $this->file = null;
        
        $this->resetErrorBag();
        $this->showSettingModal = true;
    }

    public function resetForm()
    {
        $this->settingId = null;
        $this->key = '';
        $this->name = '';
        $this->value = '';
        $this->type = 'text';
        $this->group = '';
        $this->isLocked = false;
        $this->file = null;
        $this->resetErrorBag();
    }

    public function saveSetting()
    {
        $isEditing = !is_null($this->settingId);

        // Ensure group is a string
        $this->group = (string) $this->group;

        // Validation rules
        $rules = [
            'key' => 'required|string|max:255|unique:system_settings,key' . ($isEditing ? ',' . $this->settingId : ''),
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,file,number,boolean',
            'group' => 'required|string|in:' . implode(',', SystemSettingGroup::values()),
            'isLocked' => 'boolean',
        ];

        // Value validation based on type
        if ($this->type === 'file') {
            if ($isEditing) {
                $rules['file'] = 'nullable|image|max:2048';
            } else {
                $rules['file'] = 'required|image|max:2048';
            }
        } else {
            $rules['value'] = 'nullable|string';
        }

        $this->validate($rules);

        // Handle file upload
        $filePath = null;
        if ($this->type === 'file') {
            if ($this->file) {
                // New file uploaded - store it
                try {
                    $filePath = $this->file->store('system-settings', 'public');
                    $filePath = 'storage/' . $filePath;
                } catch (\Exception $e) {
                    $this->addError('file', 'Failed to upload file: ' . $e->getMessage());
                    return;
                }
            } elseif ($isEditing) {
                // Keep existing file if editing and no new file uploaded
                $existing = SystemSetting::find($this->settingId);
                $filePath = $existing->value;
            }
        }

        $settingData = [
            'key' => $this->key,
            'name' => $this->name,
            'value' => $this->type === 'file' ? $filePath : $this->value,
            'type' => $this->type,
            'group' => $this->group,
            'is_locked' => $this->isLocked,
        ];

        if ($isEditing) {
            $setting = SystemSetting::findOrFail($this->settingId);
            $setting->update($settingData);
            $message = 'Setting updated successfully!';
        } else {
            SystemSetting::create($settingData);
            $message = 'Setting created successfully!';
            $this->resetPage();
        }

        // Clear file after successful save
        $this->file = null;
        
        $this->closeSettingModal();

        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function confirmDelete($id)
    {
        $setting = SystemSetting::findOrFail($id);
        
        if ($setting->is_locked) {
            $this->dispatch('show-toast', [
                'message' => 'Cannot delete a locked setting.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }

        $this->deleteSettingId = $setting->id;
        $this->deleteSettingName = $setting->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteSettingId = null;
        $this->deleteSettingName = '';
    }

    public function deleteSetting()
    {
        if (!$this->deleteSettingId) {
            return;
        }

        $setting = SystemSetting::findOrFail($this->deleteSettingId);
        
        if ($setting->is_locked) {
            $this->dispatch('show-toast', [
                'message' => 'Cannot delete a locked setting.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            $this->closeDeleteModal();
            return;
        }

        $settingName = $setting->name;
        $setting->delete();

        $this->closeDeleteModal();

        $this->dispatch('show-toast', [
            'message' => 'Setting "' . $settingName . '" deleted successfully!',
            'type' => 'success'
        ]);
    }

    public function render()
    {
        // Query for paginated results (for "All" tab)
        $query = SystemSetting::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('key', 'like', '%' . $this->search . '%')
                    ->orWhere('value', 'like', '%' . $this->search . '%')
                    ->orWhere('group', 'like', '%' . $this->search . '%');
            });
        }

        $paginatedSettings = $query->orderBy('group', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        // Query for grouped results (for group tabs)
        $groupedQuery = SystemSetting::query();
        
        if (!empty($this->search)) {
            $groupedQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('key', 'like', '%' . $this->search . '%')
                    ->orWhere('value', 'like', '%' . $this->search . '%')
                    ->orWhere('group', 'like', '%' . $this->search . '%');
            });
        }

        $groupedSettings = $groupedQuery->orderBy('group', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->groupBy('group');

        return view('livewire.management.system-settings', [
            'paginatedSettings' => $paginatedSettings,
            'groupedSettings' => $groupedSettings,
        ]);
    }
}
