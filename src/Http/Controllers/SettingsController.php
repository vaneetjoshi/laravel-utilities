<?php

namespace Vaneetjoshi\LaravelUtilities\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Vaneetjoshi\LaravelUtilities\Settings\SettingsManager;

class SettingsController extends Controller
{
    /**
     * Resolve the authenticated user, automatically adapting to the Tenancy Engine if active.
     */
    protected function getAuthenticatedUser()
    {
        $guard = function_exists('tenant_auth') && function_exists('is_tenant_initialized') && is_tenant_initialized() 
            ? tenant_auth() 
            : auth();

        return $guard->user();
    }

    /**
     * Validate and save the submitted settings from the Headless UI Component.
     */
    public function update(Request $request, string $groupKey)
    {
        $user = $this->getAuthenticatedUser();
        
        // 🚀 Fetch from the new in-memory registry instead of config
        $group = SettingsManager::getGroup($groupKey);
        
        abort_if(! $group, 404, 'Settings group not found.');
        abort_if(! $group->isAuthorized($user), 403, 'Unauthorized action.');

        $rules = $group->getValidationRules($user);
        $attributes = $group->getValidationAttributes($request->all(), $user);
        
        $validatedData = $request->validate($rules, [], $attributes);
        
        SettingsManager::save($group, $validatedData, $user);

        // Redirect back to the exact URL the component was rendered on
        $redirectUrl = $request->input('_redirect_url');
        
        if ($redirectUrl) {
            return redirect($redirectUrl)->with('success', "{$group->label} have been updated successfully!");
        }

        return back()->with('success', "{$group->label} have been updated successfully!");
    }
}