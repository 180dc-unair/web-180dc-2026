<?php

namespace App\Providers;

use App\Repositories\ArticleCategoryRepository;
use App\Repositories\ArticleCommentRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\AuthRepository;
use App\Repositories\CartItemRepository;
use App\Repositories\CartRepository;
use App\Repositories\ClientRepository;
use App\Repositories\Contracts\ArticleCategoryRepositoryInterface;
use App\Repositories\Contracts\ArticleCommentRepositoryInterface;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\EventCategoryRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PaymentMethodRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\PaymentWebhookRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use App\Repositories\EventCategoryRepository;
use App\Repositories\EventRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentWebhookRepository;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ServiceCategoryRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\TeamMemberRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ProductCategoryRepositoryInterface::class, ProductCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ServiceCategoryRepositoryInterface::class, ServiceCategoryRepository::class);
        $this->app->bind(ArticleCategoryRepositoryInterface::class, ArticleCategoryRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(ArticleCommentRepositoryInterface::class, ArticleCommentRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, TeamMemberRepository::class);
        $this->app->bind(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(CartItemRepositoryInterface::class, CartItemRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentWebhookRepositoryInterface::class, PaymentWebhookRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        RateLimiter::for('auth-credential', function (Request $request) {
            $identifier = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($identifier);
        });
    }
}
