<?php

namespace App\Filament\Customer\Resources\Vendors\Pages;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\Page;

class ClassifyVendor extends Page
{
    protected static string $resource = VendorResource::class;

    protected string $view = 'filament.customer.resources.vendors.pages.classify-vendor';
}
