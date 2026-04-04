<?php

namespace App\Services;

use App\Models\Clerk;
use App\Models\User;
use App\Repositories\ClerkRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class ClerkService
{
    public function __construct(protected ClerkRepository $repo) {}

    /** Paginated clerk list with search. */
    public function list(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->repo->listWithSearch($search, $perPage);
    }

    /** Create a clerk and an optional linked user account. */
    public function store(array $data): Clerk
    {
        $payload = collect($data)->except(['password', 'password_confirmation'])->all();

        $clerk = Clerk::create($payload);

        if (!empty($data['email']) && !empty($data['password'])) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'staff',
                'clerk_id' => $clerk->id,
            ]);
            $clerk->user_id = $user->id;
            $clerk->save();
        }

        return $clerk;
    }

    /** Update clerk and sync the linked user account. */
    public function update(int $id, array $data): Clerk
    {
        $clerk   = Clerk::findOrFail($id);
        $payload = collect($data)->except(['password', 'password_confirmation'])->all();

        $clerk->update($payload);

        if ($clerk->user_id) {
            $user = User::find($clerk->user_id);
            if ($user) {
                $updates = [];
                if (!empty($data['name'])  && $data['name']  !== $user->name)  $updates['name']  = $data['name'];
                if (!empty($data['email']) && $data['email'] !== $user->email) $updates['email'] = $data['email'];
                if (!empty($data['password']))                                  $updates['password'] = Hash::make($data['password']);
                if ($user->clerk_id !== $clerk->id)                            $updates['clerk_id'] = $clerk->id;
                if ($updates) $user->update($updates);
            }
        } elseif (!empty($data['email']) && !empty($data['password'])) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'staff',
                'clerk_id' => $clerk->id,
            ]);
            $clerk->user_id = $user->id;
            $clerk->save();
        }

        return $clerk;
    }

    /** Delete clerk and its linked user if present. */
    public function destroy(int $id): void
    {
        $clerk = Clerk::findOrFail($id);
        $userId = $clerk->user_id;
        $clerk->delete();

        if ($userId && $user = User::find($userId)) {
            $user->delete();
        }
    }
}
