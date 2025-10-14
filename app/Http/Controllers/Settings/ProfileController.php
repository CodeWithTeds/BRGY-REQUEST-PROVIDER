<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'applicantProfile' => $user->applicantProfile ? $user->applicantProfile->only([
                'first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth', 'place_of_birth',
                'civil_status', 'gender', 'citizenship', 'contact_number',
            ]) : null,
        ]);
    }

    public function editPersonal(Request $request): Response
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        return Inertia::render('settings/PersonalInformation', [
            'status' => $request->session()->get('status'),
            'applicantProfile' => $user->applicantProfile ? $user->applicantProfile->only([
                'first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth', 'place_of_birth',
                'civil_status', 'gender', 'citizenship', 'contact_number',
            ]) : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit');
    }

    /**
     * Create or update the user's applicant profile personal information.
     */
    public function updateApplicant(\App\Http\Requests\Settings\ApplicantProfileUpdateRequest $request): RedirectResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $data = $request->validated();

        $profile = $user->applicantProfile()->firstOrNew();
        $profile->fill($data);
        $profile->user_id = $user->id;
        $profile->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
