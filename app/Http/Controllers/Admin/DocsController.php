<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DocsController extends Controller
{
    /**
     * Display the API documentation page.
     */
    public function api()
    {
        $user = auth()->user();
        $token = $user->tokens()->where('name', 'admin-token')->first();

        return view('admin.docs.api', [
            'token' => session('api_token'),
            'token_hits' => $token?->hits ?? 0,
            'last_used_at' => $token?->last_used_at,
        ]);
    }

    public function generateToken()
    {
        $user = auth()->user();
        
        // Delete existing admin token
        $user->tokens()->where('name', 'admin-token')->delete();
        
        // Create new token
        $token = $user->createToken('admin-token');

        return back()->with('api_token', $token->plainTextToken)
                    ->with('success', 'API Token generated successfully! Please copy it now, as it will not be shown again.');
    }
}
