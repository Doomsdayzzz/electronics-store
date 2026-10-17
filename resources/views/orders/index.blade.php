@extends('layouts.main')

@section('content')
    <div class="orders-wrap">
        <h1 class="page-title">Мои заказы</h1>

        @if($orders->isEmpty())
            <div class="orders-empty">
                <div class="orders-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="orders-empty-title">У вас ещё нет заказов</h2>
                <p class="orders-empty-text">Оформите первый заказ из корзины</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">В каталог</a>
            </div>
        @else
            <div class="orders-list">
                @foreach($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="order-card">
                        <div class="order-card-header">
                            <div>
                                <div class="order-card-id">Заказ #{{ $order->id }}</div>
                                <div class="order-card-date">{{ $order->created_at?->format('d.m.Y H:i') }}</div>
                            </div>
                            <div class="order-status order-status-{{ $order->status ?? 'new' }}">
                                {{ match($order->status ?? 'new') {
                                    'new'        => 'Новый',
                                    'processing' => 'В обработке',
                                    'done'       => 'Выполнен',
                                    'canceled'   => 'Отменён',
                                    default      => ucfirst($order->status ?? 'new'),
                                } }}
                            </div>
                        </div>

                        <div class="order-card-items">
                            @foreach($order->items->take(3) as $item)
                                <div class="order-card-item">
                                    <img class="order-card-thumb"
                                         src="{{ $item->product?->images?->first()?->path ?? asset('/storage/images/no-image.jpg') }}"
                                         alt="">
                                    <div class="order-card-item-name">{{ $item->product?->name ?? 'Товар удалён' }}</div>
                                    <div class="order-card-item-qty">× {{ $item->quantity }}</div>
                                </div>
                            @endforeach
                            @if($order->items->count() > 3)
                                <div class="order-card-more">+ ещё {{ $order->items->count() - 3 }}</div>
                            @endif
                        </div>

                        <div class="order-card-footer">
                            <span>Сумма</span>
                            <span class="order-card-total">{{ number_format($order->total_price, 0, '.', ' ') }} ₽</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pagination-wrapper">{{ $orders->links() }}</div>
        @endif
    </div>

    <style>
        .orders-wrap {
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 24px 64px;
        }
        .orders-empty {
            background: #fff;
            border-radius: 12px;
            padding: 60px 24px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .orders-empty-icon {
            width: 72px; height: 72px; margin: 0 auto 20px;
            border-radius: 50%;
            background: #f1f2f6; color: #b2bec3;
            display: flex; align-items: center; justify-content: center;
        }
        .orders-empty-icon svg { width: 34px; height: 34px; }
        .orders-empty-title { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .orders-empty-text  { font-size: 14px; color: #636e72; margin-bottom: 20px; }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            background: #00b894;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-primary:hover { background: #009e7f; }

        .orders-list {
            display: grid;
            gap: 16px;
        }

        .order-card {
            display: block;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            text-decoration: none;
            color: inherit;
            transition: transform .2s, box-shadow .2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,.08);
        }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f2f6;
            margin-bottom: 14px;
        }
        .order-card-id { font-size: 16px; font-weight: 700; }
        .order-card-date { font-size: 12px; color: #636e72; margin-top: 2px; }

        .order-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f1f2f6;
            color: #636e72;
            white-space: nowrap;
        }
        .order-status-new        { background: #e6f1fc; color: #0984e3; }
        .order-status-processing { background: #fff4e0; color: #b06d00; }
        .order-status-done       { background: #e8f8f3; color: #009e7f; }
        .order-status-canceled   { background: #fdecec; color: #d63031; }

        .order-card-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 14px;
        }
        .order-card-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .order-card-thumb {
            width: 40px; height: 40px;
            object-fit: contain;
            border-radius: 6px;
            background: #f5f6fa;
        }
        .order-card-item-name {
            flex: 1;
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .order-card-item-qty { font-size: 13px; color: #636e72; }
        .order-card-more {
            font-size: 13px;
            color: #0984e3;
            padding-left: 50px;
        }

        .order-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 14px;
            border-top: 1px solid #f1f2f6;
            font-size: 14px;
            color: #636e72;
        }
        .order-card-total { font-size: 18px; font-weight: 700; color: #2f3640; }

        .pagination-wrapper { margin-top: 24px; display: flex; justify-content: center; }
    </style>
@endsection
