<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'jetstream' => function () use ($request) {
                $user = $request->user();
                return [
                    'canCreateTeams' => $user &&
                        Jetstream::userHasTeamFeatures($user) &&
                        Gate::forUser($user)->check('create', Jetstream::newTeamModel()),
                    'hasAccountDeletionFeatures' => Jetstream::hasAccountDeletionFeatures(),
                    'hasApiFeatures' => Jetstream::hasApiFeatures(),
                    'hasTeamFeatures' => Jetstream::hasTeamFeatures(),
                    'hasTermsAndPrivacyPolicyFeature' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
                    'managesProfilePhotos' => Jetstream::managesProfilePhotos(),
                    'flash' => $request->session()->get('flash', []),
                ];
            },
            'auth' => [
                'user' => function () use ($request) {
                    if (! $user = $request->user()) {
                        return null;
                    }

                    $userHasTeamFeatures = Jetstream::userHasTeamFeatures($user);
                    if ($userHasTeamFeatures) {
                        $user->currentTeam;
                    }

                    return array_merge($user->toArray(), array_filter([
                        'all_teams' => $userHasTeamFeatures ? $user->allTeams()->values() : null,
                    ]), [
                        'two_factor_enabled' => ! is_null($user->two_factor_secret),
                        'permissions' => $user->teamPermissions($user->currentTeam),
                        'role' => $user->teamRole($user->currentTeam),
                    ]);
                },
            ],
            'errorBags' => function () {
                return collect(optional(Session::get('errors'))->getBags() ?: [])->mapWithKeys(function ($bag, $key) {
                    return [$key => $bag->messages()];
                })->all();
            },
        ]);
    }
}
