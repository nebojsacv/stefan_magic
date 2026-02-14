<?php

namespace App\Filament\Customer\Pages\Auth;

use App\Models\Package;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),

                TextInput::make('company_name')
                    ->label('Company Name')
                    ->maxLength(255)
                    ->helperText('Optional - Your company or organization name'),

                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                Section::make('Choose Your Plan')
                    ->description('Select the package that best fits your needs. You can upgrade or downgrade anytime.')
                    ->schema([
                        Radio::make('package_id')
                            ->label('Subscription Package')
                            ->options(function () {
                                return Package::where('is_active', true)
                                    ->orderBy('price')
                                    ->get()
                                    ->mapWithKeys(function ($package) {
                                        $features = is_array($package->features) ? $package->features : [];
                                        $aiLimit = $features['ai_cost_limit'] ?? 0;
                                        $support = $features['support'] ?? 'email';

                                        $description = sprintf(
                                            '%s/month | %s assessments | $%s AI budget | %s support',
                                            $package->price == 0 ? 'FREE' : '$'.number_format($package->price, 0),
                                            $package->assessments_allowed == -1 ? 'Unlimited' : $package->assessments_allowed,
                                            $aiLimit,
                                            ucfirst($support)
                                        );

                                        return [$package->id => $package->package_name.' - '.$description];
                                    });
                            })
                            ->descriptions(function () {
                                return Package::where('is_active', true)
                                    ->orderBy('price')
                                    ->get()
                                    ->mapWithKeys(function ($package) {
                                        $features = is_array($package->features) ? $package->features : [];
                                        $featureList = [];

                                        if ($package->assessments_allowed == -1) {
                                            $featureList[] = '✓ Unlimited vendor assessments';
                                        } else {
                                            $featureList[] = '✓ '.$package->assessments_allowed.' vendor assessments per month';
                                        }

                                        if (isset($features['ai_cost_limit']) && $features['ai_cost_limit'] > 0) {
                                            $featureList[] = '✓ AI-powered risk analysis';
                                        }

                                        if (isset($features['priority_support']) && $features['priority_support']) {
                                            $featureList[] = '✓ Priority support';
                                        }

                                        if (isset($features['custom_branding']) && $features['custom_branding']) {
                                            $featureList[] = '✓ Custom branding';
                                        }

                                        if (isset($features['api_access']) && $features['api_access']) {
                                            $featureList[] = '✓ API access';
                                        }

                                        return [$package->id => implode("\n", $featureList)];
                                    });
                            })
                            ->required()
                            ->default(function () {
                                return Package::where('package_name', 'Free Trial')->first()?->id;
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        // Assign the selected package to the user
        if (isset($data['package_id'])) {
            $package = Package::find($data['package_id']);
            if ($package) {
                $user->package_id = $package->id;
                $user->assessments_allowed = $package->assessments_allowed;
                $user->status = $package->price == 0 ? 'trial' : 'active';
                $user->save();
            }
        }

        return $user;
    }
}
