@extends('layouts.main')

@section('content')
    <div class="admin-orders-wrap">
        <h1 class="page-title">Все заказы</h1>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        <div class="panel">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th>Пользователь</th>
                        <th>Адрес</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th style="width:100px"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>
                                <div class="cell-title">{{ $order->user?->name ?? '—' }}</div>
                                <div class="cell-sub">{{ $order->user?->email ?? '' }}</div>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($order->delivery_address, 40) }}</td>
                            <td><b>{{ number_format($order->total_price, 0, '.', ' ') }} ₽</b></td>
                            <td>
                                <span class="order-status order-status-{{ $order->status ?? 'new' }}">
                                    {{ match($order->status ?? 'new') {
                                        'new'        => 'Новый',
                                        'processing' => 'В обработке',
                                        'done'       => 'Выполнен',
                                        'canceled'   => 'Отменён',
                                        default      => ucfirst($order->status ?? 'new'),
                                    } }}
                                </span>
                            </td>
                            <td>{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="btn btn-sm btn-outline">Открыть</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-row">Заказов пока нет</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">{{ $orders->links() }}</div>
        </div>
    </div>

    <style>
        .admin-orders-wrap { max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; }
        .panel {
            background: #fff; border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .table-wrap { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .data-table th {
            text-align: left; font-size: 12px; text-transform: uppercase;
            letter-spacing: .4px; color: #636e72;
            padding: 10px 12px; border-bottom: 1px solid #f1f2f6;
            white-space: nowrap;
        }
        .data-table td {
            padding: 12px; border-bottom: 1px solid #f1f2f6;
            vertical-align: middle;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #fafbfc; }
        .cell-title { font-weight: 600; color: #2f3640; }
        .cell-sub { font-size: 12px; color: #b2bec3; margin-top: 2px; }
        .empty-row { text-align: center; padding: 40px 0; color: #b2bec3; }

        .order-status {
            display: inline-block; padding: 4px 10px;
            border-radius: 6px; font-size: 12px; font-weight: 600;
            background: #f1f2f6; color: #636e72;
        }
        .order-status-new        { background: #e6f1fc; color: #0984e3; }
        .order-status-processing { background: #fff4e0; color: #b06d00; }
        .order-status-done       { background: #e8f8f3; color: #009e7f; }
        .order-status-canceled   { background: #fdecec; color: #d63031; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 6px; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            padding: 10px 16px; cursor: pointer;
            text-decoration: none; font-family: inherit;
            transition: background-color .2s, color .2s, border-color .2s;
        }
        .btn-sm { padding: 6px 10px; font-size: 13px; }
        .btn-outline {
            background: #fff; color: #0984e3;
            border: 1px solid #0984e3;
        }
        .btn-outline:hover { background: #e6f1fc; }

        .flash-success {
            background: #e8f8f3; color: #007a5e;
            border: 1px solid #b8ecdb;
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-size: 14px;
        }
        .pagination-wrapper { margin-top: 20px; display: flex; justify-content: center; }
    </style>
@endsection
