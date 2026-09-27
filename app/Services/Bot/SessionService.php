<?php

namespace App\Services\Bot;

use App\Models\WhatsappUser;
use Illuminate\Support\Facades\Cache;

class SessionService
{
    protected int $ttl = 172800; // 48 hours

    public function get(WhatsappUser $user): array
    {
        return Cache::get("session_{$user->id}", []);
    }

    public function set(WhatsappUser $user, array $data): void
    {
        Cache::put("session_{$user->id}", $data, $this->ttl);
    }

    public function clear(WhatsappUser $user): void
    {
        Cache::forget("session_{$user->id}");
    }

    public function setValue(WhatsappUser $user, string $key, mixed $value): void
    {
        $data = $this->get($user);
        $data[$key] = $value;
        $this->set($user, $data);
    }

    public function getValue(WhatsappUser $user, string $key, mixed $default = null): mixed
    {
        $data = $this->get($user);
        return $data[$key] ?? $default;
    }
}