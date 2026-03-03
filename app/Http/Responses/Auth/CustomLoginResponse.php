<?php

namespace App\Http\Responses\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        $url = ($user instanceof User && $user->isSuper())
            ? Filament::getPanel('admin')->getUrl()
            : Filament::getPanel('customer')->getUrl();

        return redirect()->intended($url);
    }
}
