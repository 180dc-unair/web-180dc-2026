<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminPageController extends Controller
{
    private const RESOURCES = [
        'products' => 'Produk',
        'product-categories' => 'Kategori Produk',
        'services' => 'Layanan',
        'service-categories' => 'Kategori Layanan',
        'clients' => 'Klien',
        'articles' => 'Artikel',
        'article-categories' => 'Kategori Artikel',
        'team-members' => 'Tim',
        'event-categories' => 'Kategori Event',
        'events' => 'Event',
        'users' => 'Pengguna',
        'payment-methods' => 'Metode Pembayaran',
    ];

    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard/Index');
    }

    public function resource(string $resourceKey): Response
    {
        abort_unless(array_key_exists($resourceKey, self::RESOURCES), 404);

        return Inertia::render('Admin/Resources/Index', [
            'resourceKey' => $resourceKey,
            'pageTitle' => self::RESOURCES[$resourceKey],
        ]);
    }

    public function comments(): Response
    {
        return Inertia::render('Admin/Comments/Index');
    }

    public function orders(): Response
    {
        return Inertia::render('Admin/Orders/Index');
    }

    public function payments(): Response
    {
        return Inertia::render('Admin/Payments/Index');
    }
}
