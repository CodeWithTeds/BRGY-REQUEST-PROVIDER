<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClerkRequest;
use App\Http\Requests\Admin\UpdateClerkRequest;
use App\Models\Clerk;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ClerkController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $perPage = (int) ($request->input('per_page', 10));

        $query = Clerk::query()->latest();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage)->appends(['search' => $search]);

        return Inertia::render('Admin/Clerks', [
            'clerks' => $paginator->items(),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreClerkRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $payload = $data;
        unset($payload['password'], $payload['password_confirmation']);

        // Create Clerk first to get ID
        $clerk = Clerk::create($payload);

        // Create linked user if credentials provided
        if (!empty($data['email']) && !empty($data['password'])) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'staff',
                'clerk_id' => $clerk->id,
            ]);
            // Back-link user_id on clerk
            $clerk->user_id = $user->id;
            $clerk->save();
        }

        return redirect()->route('admin.clerks')
            ->with('success', 'Clerk added successfully.');
    }

    public function update(UpdateClerkRequest $request, int $id): RedirectResponse
    {
        $clerk = Clerk::findOrFail($id);
        $data = $request->validated();
        $payload = $data;
        unset($payload['password'], $payload['password_confirmation']);

        $clerk->update($payload);

        // Sync linked user
        if ($clerk->user_id) {
            $user = User::find($clerk->user_id);
            if ($user) {
                $updates = [];
                if (!empty($data['name']) && $data['name'] !== $user->name) {
                    $updates['name'] = $data['name'];
                }
                if (!empty($data['email']) && $data['email'] !== $user->email) {
                    $updates['email'] = $data['email'];
                }
                if (!empty($data['password'])) {
                    $updates['password'] = Hash::make($data['password']);
                }
                // Always ensure clerk linkage
                if ($user->clerk_id !== $clerk->id) {
                    $updates['clerk_id'] = $clerk->id;
                }
                if ($updates) {
                    $user->update($updates);
                }
            }
        } else {
            // Create user if credentials provided
            if (!empty($data['email']) && !empty($data['password'])) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => 'staff',
                    'clerk_id' => $clerk->id,
                ]);
                $clerk->user_id = $user->id;
                $clerk->save();
            }
        }

        return redirect()->route('admin.clerks')
            ->with('success', 'Clerk updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $clerk = Clerk::findOrFail($id);
        $userId = $clerk->user_id;
        $clerk->delete();

        if ($userId) {
            if ($user = User::find($userId)) {
                $user->delete();
            }
        }

        return redirect()->route('admin.clerks')
            ->with('success', 'Clerk deleted successfully.');
    }
}