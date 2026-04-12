<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ResetUserPasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $users = $this->userService->getPaginatedUsers(
            $request->only('search'),
            $perPage
        );

        return Inertia::render('User/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $this->userService->storeUser($request->validated());

        return redirect()->back()->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()->back()->with('success', 'User berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $this->userService->deleteUser($user);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'User gagal dihapus');
        }

        return redirect()->back()->with('success', 'User berhasil dihapus');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(ResetUserPasswordRequest $request, User $user)
    {
        $this->userService->resetPassword($user, $request->password);

        return redirect()->back()->with('success', 'Password berhasil diubah');
    }
}
