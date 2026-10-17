@extends('layouts.main')

@section('content')
    <div class="order-wrap">
        <a href="{{ route('orders.my') }}" class="back-link">← Все заказы</a>

        <div class="order-header">
            <div>
                <h1 class="page-title">Заказ #{{ $order->id }}</h1>
                <p class="order-date">{{ $order->created_at?->format('d.m.Y H:i') }}</p>
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

        <div class="order-grid">
            <div class="order-panel">
                <h2 class="panel-title">Товары</h2>
                <div class="order-items">
                    @foreach($order->items as $item)
                        <div class="order-item">
                            <img class="order-thumb"
                                 src="{{ $item->product?->images?->first()?->path ?? asset('/storage/images/no-image.jpg') }}"
                                 alt="">
                            <div class="order-item-info">
                                <div class="order-item-name">{{ $item->product?->name ?? 'Товар удалён' }}</div>
                                <div class="order-item-meta">Количество: {{ $item->quantity }}</div>
                            </div>
                            <div class="order-item-price">{{ number_format($item->total_price, 0, '.', ' ') }} ₽</div>
                        </div>
                    @endforeach
                </div>

                <div class="order-total">
                    <span>Итого</span>
                    <span>{{ number_format($order->total_price, 0, '.', ' ') }} ₽</span>
                </div>
            </div>

            <aside class="order-panel">
                <h2 class="panel-title">Доставка</h2>
                <div class="order-info-row">
                    <span>Адрес</span>
                    <span>{{ $order->delivery_address }}</span>
                </div>
                <div class="order-info-row">
                    <span>Комментарий</span>
                    <span>{{ $order->comment ?: '—' }}</span>
                </div>
                <div class="order-info-row">
                    <span>Получатель</span>
                    <span>{{ $order->user?->name ?? '—' }}</span>
                </div>
                <div class="order-info-row">
                    <span>Телефон</span>
                    <span>{{ $order->user?->phone ?? '—' }}</span>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .order-wrap { max-width: 1000px; margin: 0 auto; padding: 32px 24px 64px; }
        .back-link {
            display: inline-block;
            font-size: 13px;
            color: #636e72;
            text-decoration: none;
            margin-bottom: 16px;
        }
        .back-link:hover { color: #0984e3; }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }
        .page-title { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .order-date { font-size: 13px; color: #636e72; }

        .order-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            background: #f1f2f6;
            color: #636e72;
        }
        .order-status-new        { background: #e6f1fc; color: #0984e3; }
        .order-status-processing { background: #fff4e0; color: #b06d00; }
        .order-status-done       { background: #e8f8f3; color: #009e7f; }
        .order-status-canceled   { background: #fdecec; color: #d63031; }

        .order-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 20px;
            align-items: start;
        }
        .order-panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .panel-title {
            font-size: 16px;
            font-weight: 700;
            color: #2f3640;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f2f6;
            margin-bottom: 14px;
        }

        .order-items { display: flex; flex-direction: column; }
        .order-item {
            display: grid;
            grid-template-columns: 56px 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f2f6;
        }
        .order-item:last-child { border-bottom: none; }
        .order-thumb {
            width: 56px; height: 56px;
            object-fit: contain;
            border-radius: 6px;
            background: #f5f6fa;
        }
        .order-item-name { font-size: 14px; font-weight: 600; color: #2f3640; }
        .order-item-meta { font-size: 12px; color: #636e72; margin-top: 2px; }
        .order-item-price { font-size: 15px; font-weight: 700; color: #2f3640; }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            margin-top: 4px;
            border-top: 1px solid #f1f2f6;
            font-size: 16px;
            font-weight: 600;
        }
        .order-total span:last-child { font-size: 22px; font-weight: 700; }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f2f6;
        }
        .order-info-row:last-child { border-bottom: none; }
        .order-info-row span:first-child { color: #636e72; }
        .order-info-row span:last-child { color: #2f3640; font-weight: 600; text-align: right; }

        @media (max-width: 900px) {
            .order-grid { grid-template-columns: 1fr; }
        }
    </style>
@endsection
