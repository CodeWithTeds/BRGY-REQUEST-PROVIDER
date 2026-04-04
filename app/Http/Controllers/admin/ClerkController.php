<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClerkIndexRequest;
use App\Http\Requests\Admin\StoreClerkRequest;
use App\Http\Requests\Admin\UpdateClerkRequest;
use App\Services\ClerkService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ClerkController extends Controller
{
    public function __construct(protected ClerkService $service) {}

    public function index(ClerkIndexRequest $request)
    {
        $paginator = $this->service->list($request->searchTerm(), $request->perPage());
        return Inertia::render('Admin/Clerks', [
            'clerks'     => $paginator->items(),
            'filters'    => ['search' => $request->searchTerm(), 'per_page' => $request->perPage()],
            'pagination' => ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'last_page' => $paginator->lastPage(), 'total' => $paginator->total()],
        ]);
    }

    public function store(StoreClerkRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.clerks')->with('success', 'Clerk added successfully.');
    }

    public function update(UpdateClerkRequest $request, int $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());
        return redirect()->route('admin.clerks')->with('success', 'Clerk updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->destroy($id);
        return redirect()->route('admin.clerks')->with('success', 'Clerk deleted successfully.');
    }
}