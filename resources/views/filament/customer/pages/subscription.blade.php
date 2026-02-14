<x-filament-panels::page>
    @php
        $packageInfo = $this->getCurrentPackageInfo();
        $packages = $this->getPackages();
        $user = Auth::user();
    @endphp

    {{-- Current Package Summary --}}
    <x-filament::section>
        <x-slot name="heading">
            {{ $packageInfo['name'] }}
        </x-slot>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if($packageInfo['price'] == 0)
                            Free Trial
                        @else
                            ${{ number_format($packageInfo['price'], 2) }} / {{ $packageInfo['billing_cycle'] ?? 'month' }}
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Assessments Used</p>
                    <p class="text-2xl font-bold">
                        {{ $packageInfo['assessments_used'] }} / 
                        @if($packageInfo['assessments_allowed'] == -1)
                            ∞
                        @else
                            {{ $packageInfo['assessments_allowed'] }}
                        @endif
                    </p>
                </div>
            </div>
            
            @if($packageInfo['assessments_allowed'] != -1)
                <div class="pt-4 border-t">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Usage</span>
                        @php
                            $percentage = $packageInfo['assessments_allowed'] > 0 
                                ? ($packageInfo['assessments_used'] / $packageInfo['assessments_allowed']) * 100 
                                : 0;
                            $percentage = min(100, $percentage);
                        @endphp
                        <span>{{ round($percentage) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $percentage >= 100 ? 'bg-red-500' : 'bg-primary-500' }}"
                             style="width: {{ $percentage }}%">
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        {{ $packageInfo['assessments_remaining'] }} assessment{{ $packageInfo['assessments_remaining'] != 1 ? 's' : '' }} remaining
                    </p>
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- Available Packages --}}
    <x-filament::section>
        <x-slot name="heading">
            Choose Your Plan
        </x-slot>
        <x-slot name="description">
            Select the package that best fits your needs
        </x-slot>

        <div class="fi-sc fi-sc-has-gap fi-grid" style="display: grid; gap: 1.5rem; grid-template-columns: repeat(1, minmax(0, 1fr)); --cols-md: repeat(2, minmax(0, 1fr)); --cols-lg: repeat(4, minmax(0, 1fr));">
            <style>
                @media (min-width: 768px) {
                    .fi-grid[style*="--cols-md"] {
                        grid-template-columns: var(--cols-md) !important;
                    }
                }
                @media (min-width: 1024px) {
                    .fi-grid[style*="--cols-lg"] {
                        grid-template-columns: var(--cols-lg) !important;
                    }
                }
            </style>

            @foreach($packages as $package)
                @php
                    $features = is_array($package->features) ? $package->features : [];
                    $aiLimit = $features['ai_cost_limit'] ?? 0;
                    $isCurrentPackage = $package->id == $user->package_id;
                    $isSelectedPackage = $package->id == $this->selectedPackageId;
                    $support = $features['support'] ?? 'email';
                    $priority = $features['priority_support'] ?? false;
                    
                    $iconColor = 'gray';
                    if ($package->package_name === 'Professional') {
                        $iconColor = 'warning';
                    } elseif ($package->package_name === 'Enterprise') {
                        $iconColor = 'danger';
                    } elseif ($package->package_name === 'Basic') {
                        $iconColor = 'primary';
                    } else {
                        $iconColor = 'success';
                    }
                @endphp

                <x-filament::section
                    :icon="$isCurrentPackage ? 'heroicon-o-check-circle' : 'heroicon-o-credit-card'"
                    :icon-color="$iconColor"
                    collapsible
                    :collapsed="false"
                    :class="'cursor-pointer transition-all ' . ($isSelectedPackage ? 'ring-4 ring-primary-500' : '')"
                    wire:click="selectPackage({{ $package->id }})">
                    
                    <x-slot name="heading">
                        {{ $package->package_name }}
                    </x-slot>
                    
                    <x-slot name="description">
                        @if($package->price == 0)
                            FREE
                        @else
                            ${{ number_format($package->price, 0) }}/month
                        @endif
                    </x-slot>

                    @if($isCurrentPackage)
                        <x-slot name="headerEnd">
                            <x-filament::badge color="success">
                                Current
                            </x-filament::badge>
                        </x-slot>
                    @elseif($isSelectedPackage)
                        <x-slot name="headerEnd">
                            <x-filament::badge color="primary">
                                Selected
                            </x-filament::badge>
                        </x-slot>
                    @endif

                    @if($package->package_name === 'Professional')
                        <div class="mb-4">
                            <x-filament::badge color="warning">
                                ⭐ MOST POPULAR
                            </x-filament::badge>
                        </div>
                    @endif

                    {{-- Price Display --}}
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        @if($package->price == 0)
                            <div class="text-3xl font-bold">FREE</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Forever free</div>
                        @else
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-bold">${{ number_format($package->price, 0) }}</span>
                                <span class="text-gray-500 dark:text-gray-400">/month</span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Billed {{ $package->billing_cycle ?? 'monthly' }}
                            </div>
                        @endif
                    </div>

                    {{-- Features List --}}
                    <div class="space-y-3">
                        {{-- Assessments --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <x-filament::icon
                                icon="heroicon-m-check-circle"
                                class="h-5 w-5 text-success-500"
                                style="flex-shrink: 0;"
                            />
                            <span class="text-sm" style="flex: 1;">
                                @if($package->assessments_allowed == -1)
                                    <strong>Unlimited</strong> assessments
                                @else
                                    <strong>{{ $package->assessments_allowed }}</strong> assessments/month
                                @endif
                            </span>
                        </div>

                        {{-- AI Budget --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($aiLimit > 0)
                                <x-filament::icon
                                    icon="heroicon-m-check-circle"
                                    class="h-5 w-5 text-success-500"
                                    style="flex-shrink: 0;"
                                />
                                <span class="text-sm" style="flex: 1;">
                                    AI analysis <strong>${{ $aiLimit }}</strong> budget
                                </span>
                            @else
                                <x-filament::icon
                                    icon="heroicon-m-x-circle"
                                    class="h-5 w-5 text-gray-400"
                                    style="flex-shrink: 0;"
                                />
                                <span class="text-sm text-gray-500" style="flex: 1;">
                                    No AI analysis
                                </span>
                            @endif
                        </div>

                        {{-- Support --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <x-filament::icon
                                icon="heroicon-m-check-circle"
                                class="h-5 w-5 text-success-500"
                                style="flex-shrink: 0;"
                            />
                            <span class="text-sm" style="flex: 1;">
                                @if($support == 'dedicated')
                                    <strong>Dedicated</strong> support
                                @elseif($support == 'email_chat')
                                    <strong>Email & Chat</strong> support
                                @else
                                    <strong>Email</strong> support
                                @endif
                                @if($priority)
                                    ⭐
                                @endif
                            </span>
                        </div>

                        {{-- Premium Features --}}
                        @if(isset($features['custom_branding']) && $features['custom_branding'])
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-filament::icon
                                    icon="heroicon-m-check-circle"
                                    class="h-5 w-5 text-success-500"
                                    style="flex-shrink: 0;"
                                />
                                <span class="text-sm" style="flex: 1;">Custom branding</span>
                            </div>
                        @endif

                        @if(isset($features['api_access']) && $features['api_access'])
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-filament::icon
                                    icon="heroicon-m-check-circle"
                                    class="h-5 w-5 text-success-500"
                                    style="flex-shrink: 0;"
                                />
                                <span class="text-sm" style="flex: 1;">API access</span>
                            </div>
                        @endif

                        @if(isset($features['white_label']) && $features['white_label'])
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-filament::icon
                                    icon="heroicon-m-check-circle"
                                    class="h-5 w-5 text-success-500"
                                    style="flex-shrink: 0;"
                                />
                                <span class="text-sm" style="flex: 1;">White-label</span>
                            </div>
                        @endif

                        @if(isset($features['sso']) && $features['sso'])
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-filament::icon
                                    icon="heroicon-m-check-circle"
                                    class="h-5 w-5 text-success-500"
                                    style="flex-shrink: 0;"
                                />
                                <span class="text-sm" style="flex: 1;">SSO integration</span>
                            </div>
                        @endif
                    </div>

                    {{-- Selection Indicator --}}
                    @if($isSelectedPackage && !$isCurrentPackage)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-center py-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                                <span class="text-sm font-semibold text-primary-700 dark:text-primary-300">
                                    Click "Update Package" above
                                </span>
                            </div>
                        </div>
                    @elseif(!$isCurrentPackage)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-center py-2 text-sm text-gray-500 dark:text-gray-400">
                                Click to select
                            </div>
                        </div>
                    @endif
                </x-filament::section>
            @endforeach
        </div>

        {{-- Trust Info --}}
        <div class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
            <p>Secure billing • All cards accepted • Cancel anytime</p>
        </div>
    </x-filament::section>

    {{-- Package Comparison --}}
    <x-filament::section>
        <x-slot name="heading">
            Package Comparison
        </x-slot>
        <x-slot name="description">
            Compare all available packages and choose the best fit for your needs
        </x-slot>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;" class="border border-gray-300 dark:border-gray-600">
                <thead>
                    <tr style="border-bottom: 2px solid;" class="bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600">
                        <th style="padding: 16px 24px; text-align: left; font-size: 14px; font-weight: 600;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            Feature
                        </th>
                        @foreach($packages as $package)
                            <th style="padding: 16px 24px; text-align: center; font-size: 14px; font-weight: 600;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }} text-gray-900 dark:text-gray-100">
                                <div style="font-weight: bold; font-size: 16px; margin-bottom: 4px;">
                                    {{ $package->package_name }}
                                </div>
                                <div style="font-weight: 600;" class="text-primary-600 dark:text-primary-400">
                                    @if($package->price == 0)
                                        FREE
                                    @else
                                        ${{ number_format($package->price, 0) }}/mo
                                    @endif
                                </div>
                                @if($package->id == $user->package_id)
                                    <div style="margin-top: 8px;">
                                        <x-filament::badge color="success">
                                            Current
                                        </x-filament::badge>
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900">
                    {{-- Assessments --}}
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            Vendor Assessments
                        </td>
                        @foreach($packages as $package)
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }} text-gray-700 dark:text-gray-300">
                                @if($package->assessments_allowed == -1)
                                    <span style="font-weight: 600;" class="text-success-600 dark:text-success-400">Unlimited</span>
                                @else
                                    {{ $package->assessments_allowed }} / month
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    {{-- AI Budget --}}
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            AI Analysis Budget
                        </td>
                        @foreach($packages as $package)
                            @php
                                $features = is_array($package->features) ? $package->features : [];
                                $aiLimit = $features['ai_cost_limit'] ?? 0;
                            @endphp
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }} text-gray-700 dark:text-gray-300">
                                @if($aiLimit > 0)
                                    <span style="font-size: 18px;" class="text-success-600 dark:text-success-400">✓</span>
                                    <span style="margin-left: 4px;">${{ $aiLimit }}/month</span>
                                @else
                                    <span style="font-size: 18px;" class="text-gray-400 dark:text-gray-500">✗</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    {{-- Support --}}
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            Support
                        </td>
                        @foreach($packages as $package)
                            @php
                                $features = is_array($package->features) ? $package->features : [];
                                $support = $features['support'] ?? 'email';
                                $priority = $features['priority_support'] ?? false;
                            @endphp
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }} text-gray-700 dark:text-gray-300">
                                @if($support == 'dedicated')
                                    Dedicated
                                @elseif($support == 'email_chat')
                                    Email & Chat
                                @else
                                    Email
                                @endif
                                @if($priority)
                                    <span style="margin-left: 4px;">⭐</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    {{-- Custom Branding --}}
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            Custom Branding
                        </td>
                        @foreach($packages as $package)
                            @php
                                $features = is_array($package->features) ? $package->features : [];
                                $branding = $features['custom_branding'] ?? false;
                            @endphp
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }}">
                                @if($branding)
                                    <span style="font-size: 18px;" class="text-success-600 dark:text-success-400">✓</span>
                                @else
                                    <span style="font-size: 18px;" class="text-gray-400 dark:text-gray-500">✗</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    {{-- API Access --}}
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            API Access
                        </td>
                        @foreach($packages as $package)
                            @php
                                $features = is_array($package->features) ? $package->features : [];
                                $api = $features['api_access'] ?? false;
                            @endphp
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }}">
                                @if($api)
                                    <span style="font-size: 18px;" class="text-success-600 dark:text-success-400">✓</span>
                                @else
                                    <span style="font-size: 18px;" class="text-gray-400 dark:text-gray-500">✗</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    {{-- White Label --}}
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            White-label
                        </td>
                        @foreach($packages as $package)
                            @php
                                $features = is_array($package->features) ? $package->features : [];
                                $whiteLabel = $features['white_label'] ?? false;
                            @endphp
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }}">
                                @if($whiteLabel)
                                    <span style="font-size: 18px;" class="text-success-600 dark:text-success-400">✓</span>
                                @else
                                    <span style="font-size: 18px;" class="text-gray-400 dark:text-gray-500">✗</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    {{-- SSO --}}
                    <tr>
                        <td style="padding: 16px 24px; font-size: 14px; font-weight: 500;" class="border-r border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                            Single Sign-On (SSO)
                        </td>
                        @foreach($packages as $package)
                            @php
                                $features = is_array($package->features) ? $package->features : [];
                                $sso = $features['sso'] ?? false;
                            @endphp
                            <td style="padding: 16px 24px; font-size: 14px; text-align: center;" class="{{ !$loop->last ? 'border-r border-gray-300 dark:border-gray-600' : '' }}">
                                @if($sso)
                                    <span style="font-size: 18px;" class="text-success-600 dark:text-success-400">✓</span>
                                @else
                                    <span style="font-size: 18px;" class="text-gray-400 dark:text-gray-500">✗</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- FAQ --}}
    <x-filament::section>
        <x-slot name="heading">
            Need help choosing?
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold mb-2">Package Changes</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Select your desired package above and click "Update Package" to change your subscription. Upgrades take effect immediately with prorated billing.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-2">Downgrades</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Downgrades take effect at the start of your next billing cycle. You can change your package at any time.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-2">Assessment Limits</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Your assessment limit resets at the start of each billing cycle. Unused assessments do not roll over.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-2">AI Analysis</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    AI-powered risk analysis helps you make better decisions faster. Budget resets monthly.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
