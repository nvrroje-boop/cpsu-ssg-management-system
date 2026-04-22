<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Announcement;

class AnnouncementListComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortBy = 'newest';

    #[\Livewire\Attributes\On('announcement-updated')]
    public function refreshList()
    {
        $this->resetPage();
    }

    public function updateding()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Announcement::query();

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('body', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('published', $this->statusFilter === 'published');
        }

        $this->sortBy == 'newest' ? $query->orderBy('created_at', 'desc') : $query->orderBy('created_at');

        $announcements = $query->paginate(15);

        return view('livewire.admin.announcement-list-component', [
            'announcements' => $announcements,
        ]);
    }
}
