<?php

namespace App\Filament\Customer\Resources\Vendors;

use App\Filament\Customer\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Customer\Resources\Vendors\Pages\EditVendor;
use App\Filament\Customer\Resources\Vendors\Pages\ListVendors;
use App\Filament\Customer\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Customer\Resources\Vendors\Tables\VendorsTable;
use App\Models\Vendor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'My Vendors';

    protected static ?string $modelLabel = 'Vendor';

    protected static ?string $pluralModelLabel = 'Vendors';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user) return null;
        
        $used = $user->vendors()->count();
        $limit = $user->assessments_allowed === -1 ? '∞' : $user->assessments_allowed;
        
        return "{$used}/{$limit}";
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $user = Auth::user();
        if (!$user || $user->assessments_allowed === -1) return 'success';
        
        $used = $user->vendors()->count();
        $percentage = ($used / $user->assessments_allowed) * 100;
        
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 70) return 'warning';
        return 'success';
    }

    public static function canAccess(): bool
    {
        // Hide vendors from superadmin users
        return auth()->user()->role !== 'superadmin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from navigation for superadmin
        return auth()->user()->role !== 'superadmin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
