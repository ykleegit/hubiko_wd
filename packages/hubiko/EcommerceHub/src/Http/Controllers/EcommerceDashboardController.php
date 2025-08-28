<?php

namespace Hubiko\EcommerceHub\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hubiko\EcommerceHub\Entities\EcommerceStore;
use Hubiko\EcommerceHub\Entities\EcommerceOrder;
use Hubiko\EcommerceHub\Entities\EcommerceProduct;
use Hubiko\EcommerceHub\Entities\EcommerceCustomer;

class EcommerceDashboardController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->isAbleTo('ecommerce dashboard manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspace_id = getActiveWorkSpace();
        
        // E-commerce metrics
        $totalSales = EcommerceOrder::workspace($workspace_id)
                                   ->where('payment_status', 'paid')
                                   ->sum('total_amount');
        
        $ordersToday = EcommerceOrder::workspace($workspace_id)
                                    ->today()
                                    ->count();
        
        $ordersThisMonth = EcommerceOrder::workspace($workspace_id)
                                        ->thisMonth()
                                        ->count();
        
        $totalCustomers = EcommerceCustomer::workspace($workspace_id)
                                          ->where('status', 'active')
                                          ->count();
        
        $activeProducts = EcommerceProduct::workspace($workspace_id)
                                         ->where('status', 'active')
                                         ->count();
        
        $lowStockProducts = EcommerceProduct::workspace($workspace_id)
                                           ->whereRaw('stock_quantity <= low_stock_threshold')
                                           ->count();
        
        // Recent orders
        $recentOrders = EcommerceOrder::workspace($workspace_id)
                                     ->with(['customer', 'store'])
                                     ->orderBy('created_at', 'desc')
                                     ->limit(10)
                                     ->get();
        
        // Top selling products
        $topProducts = EcommerceProduct::workspace($workspace_id)
                                      ->withCount(['orderItems as total_sold' => function($query) {
                                          $query->selectRaw('sum(quantity)');
                                      }])
                                      ->orderBy('total_sold', 'desc')
                                      ->limit(5)
                                      ->get();
        
        // Monthly sales chart data
        $monthlySales = $this->getMonthlySalesData($workspace_id);
        
        // Order status distribution
        $orderStatusData = $this->getOrderStatusData($workspace_id);
        
        // Revenue by payment method
        $paymentMethodData = $this->getPaymentMethodData($workspace_id);

        return view('EcommerceHub::dashboard.index', compact(
            'totalSales',
            'ordersToday', 
            'ordersThisMonth',
            'totalCustomers',
            'activeProducts',
            'lowStockProducts',
            'recentOrders',
            'topProducts',
            'monthlySales',
            'orderStatusData',
            'paymentMethodData'
        ));
    }

    public function storeOverview($storeId)
    {
        if (!\Auth::user()->isAbleTo('ecommerce store manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $store = EcommerceStore::workspace()->findOrFail($storeId);
        
        $metrics = [
            'total_sales' => $store->getTotalSales(),
            'total_orders' => $store->getTotalOrders(),
            'active_products' => $store->getActiveProductsCount(),
            'customers_count' => $store->getCustomersCount(),
            'conversion_rate' => $this->calculateConversionRate($store),
            'average_order_value' => $this->calculateAverageOrderValue($store)
        ];
        
        $recentOrders = $store->orders()
                             ->with('customer')
                             ->orderBy('created_at', 'desc')
                             ->limit(10)
                             ->get();
        
        return view('EcommerceHub::dashboard.store', compact('store', 'metrics', 'recentOrders'));
    }

    protected function getMonthlySalesData($workspaceId)
    {
        $months = [];
        $sales = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $monthlySale = EcommerceOrder::workspace($workspaceId)
                                        ->where('payment_status', 'paid')
                                        ->whereYear('created_at', $date->year)
                                        ->whereMonth('created_at', $date->month)
                                        ->sum('total_amount');
            
            $sales[] = (float) $monthlySale;
        }
        
        return [
            'labels' => $months,
            'data' => $sales
        ];
    }

    protected function getOrderStatusData($workspaceId)
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $data = [];
        
        foreach ($statuses as $status) {
            $count = EcommerceOrder::workspace($workspaceId)
                                  ->where('status', $status)
                                  ->count();
            $data[] = [
                'label' => ucfirst($status),
                'value' => $count
            ];
        }
        
        return $data;
    }

    protected function getPaymentMethodData($workspaceId)
    {
        return EcommerceOrder::workspace($workspaceId)
                            ->where('payment_status', 'paid')
                            ->selectRaw('payment_method, sum(total_amount) as total')
                            ->groupBy('payment_method')
                            ->get()
                            ->map(function($item) {
                                return [
                                    'label' => ucfirst($item->payment_method ?? 'Unknown'),
                                    'value' => (float) $item->total
                                ];
                            });
    }

    protected function calculateConversionRate($store)
    {
        $totalVisitors = 1000; // This would come from analytics
        $totalOrders = $store->getTotalOrders();
        
        return $totalVisitors > 0 ? round(($totalOrders / $totalVisitors) * 100, 2) : 0;
    }

    protected function calculateAverageOrderValue($store)
    {
        $totalSales = $store->getTotalSales();
        $totalOrders = $store->getTotalOrders();
        
        return $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;
    }
}
