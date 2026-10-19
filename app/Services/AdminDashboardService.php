<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class AdminDashboardService
{
    public function __construct(
        private readonly int $cacheTtlSeconds = 60,
    ) {
    }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return Cache::remember(
            'admin:dashboard:summary',
            now()->addSeconds($this->cacheTtlSeconds),
            fn () => $this->buildSummary(),
        );
    }

    public function flush(): void
    {
        Cache::forget('admin:dashboard:summary');
    }

    /** @return array<string, mixed> */
    private function buildSummary(): array
    {
        $startDate = now()->startOfDay()->subDays(6);
        $articleActivity = Article::query()
            ->where('created_at', '>=', $startDate)
            ->get(['created_at'])
            ->countBy(fn (Article $article) => $article->created_at->format('Y-m-d'));
        $commentActivity = ArticleComment::query()
            ->where('created_at', '>=', $startDate)
            ->get(['created_at'])
            ->countBy(fn (ArticleComment $comment) => $comment->created_at->format('Y-m-d'));

        return [
            'metrics' => [
                'products' => [
                    'total' => Product::query()->count(),
                    'active' => Product::query()->where('status', 'active')->count(),
                    'inactive' => Product::query()->where('status', 'inactive')->count(),
                ],
                'articles' => [
                    'total' => Article::query()->count(),
                    'published' => Article::query()->where('status', 'published')->count(),
                    'draft' => Article::query()->where('status', 'draft')->count(),
                ],
                'services' => [
                    'total' => Service::query()->count(),
                    'active' => Service::query()->where('is_active', true)->count(),
                    'featured' => Service::query()->where('is_featured', true)->count(),
                ],
                'clients' => [
                    'total' => Client::query()->count(),
                    'featured' => Client::query()->where('is_featured', true)->count(),
                ],
                'comments' => [
                    'total' => ArticleComment::query()->count(),
                    'pending' => ArticleComment::query()->where('is_approved', false)->count(),
                ],
                'orders' => [
                    'total' => Order::query()->count(),
                    'pending' => Order::query()->where('status', 'pending')->count(),
                    'paid' => Order::query()->where('status', 'paid')->count(),
                    'completed' => Order::query()->where('status', 'completed')->count(),
                    'cancelled' => Order::query()->where('status', 'cancelled')->count(),
                    'revenue_idr' => (string) Order::query()
                        ->where('status', 'completed')
                        ->sum('total_amount'),
                ],
                'payments' => [
                    'total' => Payment::query()->count(),
                    'pending' => Payment::query()->where('status', 'pending')->count(),
                    'paid' => Payment::query()->where('status', 'paid')->count(),
                    'failed' => Payment::query()->where('status', 'failed')->count(),
                ],
            ],
            'recent' => [
                'articles' => Article::query()
                    ->select(['id', 'title', 'status', 'created_at'])
                    ->latest()
                    ->limit(5)
                    ->get(),
                'products' => Product::query()
                    ->select(['id', 'title', 'status', 'created_at'])
                    ->latest()
                    ->limit(5)
                    ->get(),
                'comments' => ArticleComment::query()
                    ->with(['user:id,name', 'article:id,title,slug'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (ArticleComment $comment) => [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'is_approved' => $comment->is_approved,
                        'user' => $comment->user?->name,
                        'article' => $comment->article?->title,
                        'created_at' => $comment->created_at?->toISOString(),
                    ]),
                'orders' => Order::query()
                    ->select(['id', 'order_number', 'status', 'total_amount', 'created_at'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (Order $order) => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total_amount' => (string) $order->total_amount,
                        'created_at' => $order->created_at?->toISOString(),
                    ]),
            ],
            'activity' => collect(range(0, 6))->map(function (int $offset) use ($startDate, $articleActivity, $commentActivity) {
                $date = $startDate->copy()->addDays($offset);
                $key = $date->format('Y-m-d');

                return [
                    'date' => $key,
                    'label' => $date->translatedFormat('D'),
                    'articles' => (int) ($articleActivity[$key] ?? 0),
                    'comments' => (int) ($commentActivity[$key] ?? 0),
                ];
            })->values(),
        ];
    }
}
