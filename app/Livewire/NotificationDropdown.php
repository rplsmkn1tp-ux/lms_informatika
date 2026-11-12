<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $isOpen = false;
    public string $filter = 'all'; // 'all' or 'unread'

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function markAsRead(string $key, ?string $redirectUrl = null)
    {
        $user = Auth::user();
        if ($user) {
            app(NotificationService::class)->markAsRead($user, $key);
        }

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if ($user) {
            app(NotificationService::class)->markAllAsRead($user);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $notifications = collect();
        $unreadCount = 0;

        if ($user) {
            $service = app(NotificationService::class);
            $all = $service->getNotifications($user);
            $unreadCount = $all->where('is_read', false)->count();

            if ($this->filter === 'unread') {
                $notifications = $all->where('is_read', false)->values();
            } else {
                $notifications = $all;
            }
        }

        return view('livewire.notification-dropdown', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}

