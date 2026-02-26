<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class FinancialReports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Rapoarte';
    protected static ?string $title = 'Rapoarte Financiare';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.financial-reports';

    protected function getViewData(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // DAILY
        $dailyRevenue = Payment::whereDate('created_at', $today)->where('status', 'paid')->sum('amount');
        $dailyTransactions = Payment::whereDate('created_at', $today)->where('status', 'paid')->count();
        $dailyNewMembers = User::whereRole('member')->whereDate('created_at', $today)->count();

        // MONTHLY AGGREGATES
        $monthlyRevenue = Payment::where('created_at', '>=', $thisMonth)->where('status', 'paid')->sum('amount');
        $monthlyNewMembers = User::whereRole('member')->where('created_at', '>=', $thisMonth)->count();
        
        // MONTHLY (Daily Breakdown for Grid)
        $daysInMonth = Carbon::now()->daysInMonth;
        $monthlyDays = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::now()->setDay($i)->format('Y-m-d');
            $revenue = Payment::whereDate('created_at', $date)->where('status', 'paid')->sum('amount');
            $monthlyDays[] = [
                'day' => $i,
                'date' => Carbon::parse($date)->translatedFormat('d F'),
                'revenue' => (float) $revenue
            ];
        }

        // YEARLY (Monthly Breakdown for Table & Chart)
        $yearlyMonths = [];
        $totalYearlyRevenue = 0;
        
        // Find top plans to show as columns
        $planNames = \App\Models\Plan::pluck('name')->toArray();
        if (empty($planNames)) $planNames = ['Abonament Standard']; // Fallback
        
        $yearlyPlanTotals = [];
        foreach ($planNames as $p) $yearlyPlanTotals[$p] = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::now()->setMonth($m)->startOfMonth();
            $monthEnd = Carbon::now()->setMonth($m)->endOfMonth();
            
            // If month is in the future relative to this year, we might still show 0
            $monthPayments = Payment::with('membership.plan')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', 'paid')
                ->get();
                
            $monthTotal = $monthPayments->sum('amount');
            $totalYearlyRevenue += $monthTotal;
            
            $planBreakdown = [];
            foreach ($planNames as $planName) {
                // Sum amount for payments where membership -> plan -> name == $planName
                $planSum = $monthPayments->filter(function($payment) use ($planName) {
                    return $payment->membership && $payment->membership->plan && $payment->membership->plan->name === $planName;
                })->sum('amount');
                $planBreakdown[$planName] = (float) $planSum;
                $yearlyPlanTotals[$planName] += $planSum;
            }
            
            // Add any payments without a specific plan to a "Altele" category
            $knownPlanSum = array_sum($planBreakdown);
            $othersSum = $monthTotal - $knownPlanSum;
            if ($othersSum > 0) {
                $planBreakdown['Altele'] = (float) $othersSum;
                if (!isset($yearlyPlanTotals['Altele'])) $yearlyPlanTotals['Altele'] = 0;
                $yearlyPlanTotals['Altele'] += $othersSum;
            }

            $yearlyMonths[] = [
                'month' => $monthStart->translatedFormat('F'),
                'month_num' => $m,
                'total' => (float) $monthTotal,
                'breakdown' => $planBreakdown
            ];
        }

        return [
            'metrics' => [
                'daily' => [
                    'revenue' => $dailyRevenue,
                    'transactions' => $dailyTransactions,
                    'new_members' => $dailyNewMembers,
                ],
                'monthly' => [
                    'revenue' => $monthlyRevenue,
                    'new_members' => $monthlyNewMembers,
                    'days' => $monthlyDays,
                ],
                'yearly' => [
                    'revenue' => $totalYearlyRevenue,
                    'months' => $yearlyMonths,
                    'planNames' => array_keys($yearlyMonths[0]['breakdown'] ?? []),
                    'planTotals' => $yearlyPlanTotals,
                ]
            ]
        ];
    }
}
