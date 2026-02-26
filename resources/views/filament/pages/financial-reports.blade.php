<x-filament-panels::page>
    <div x-data="{ activeTab: 'daily' }">
        
        <x-filament::tabs label="Raport Tabs">
            <x-filament::tabs.item 
                alpine-active="activeTab === 'daily'" 
                x-on:click="activeTab = 'daily'"
                icon="heroicon-o-calendar-days"
            >
                Zilnic (Astăzi)
            </x-filament::tabs.item>

            <x-filament::tabs.item 
                alpine-active="activeTab === 'monthly'" 
                x-on:click="activeTab = 'monthly'"
                icon="heroicon-o-calendar"
            >
                Lunar (Luna Curentă)
            </x-filament::tabs.item>

            <x-filament::tabs.item 
                alpine-active="activeTab === 'yearly'" 
                x-on:click="activeTab = 'yearly'"
                icon="heroicon-o-chart-pie"
            >
                Anual (Anul Curent)
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div class="mt-8">
            {{-- DAILY TAB --}}
            <div x-show="activeTab === 'daily'">
                <x-filament::section>
                    <x-slot name="heading">Performanța de Astăzi</x-slot>
                    <x-slot name="description">Un rezumat al activității financiare și a clienților noi din cursul zilei de azi.</x-slot>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-filament::fieldset>
                            <x-slot name="label">Încasări Totale</x-slot>
                            <div class="text-4xl font-bold text-success-600 dark:text-success-400">
                                {{ number_format($metrics['daily']['revenue'], 2) }} RON
                            </div>
                        </x-filament::fieldset>
                        
                        <x-filament::fieldset>
                            <x-slot name="label">Tranzacții Finalizate</x-slot>
                            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400">
                                {{ $metrics['daily']['transactions'] }}
                            </div>
                        </x-filament::fieldset>

                        <x-filament::fieldset>
                            <x-slot name="label">Membri Noi</x-slot>
                            <div class="text-4xl font-bold text-info-600 dark:text-info-400">
                                {{ $metrics['daily']['new_members'] }}
                            </div>
                        </x-filament::fieldset>
                    </div>
                </x-filament::section>
            </div>

            {{-- MONTHLY TAB --}}
            <div x-show="activeTab === 'monthly'" x-cloak>
                <x-filament::section>
                    <x-slot name="heading">Raportul pe Zile (Luna Curentă)</x-slot>
                    <x-slot name="description">Veniturile pentru fiecare zi a lunii curente, defalcate în 3 coloane.</x-slot>
                    
                    <div class="mb-8">
                        <x-filament::fieldset>
                            <x-slot name="label">Total Luna Curentă</x-slot>
                            <div class="text-4xl font-bold text-success-600 dark:text-success-400">
                                {{ number_format($metrics['monthly']['revenue'], 2) }} RON
                            </div>
                        </x-filament::fieldset>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        @foreach($metrics['monthly']['days'] as $day)
                            <div style="padding: 1rem 1.5rem; border-radius: 0.75rem; border: 1px solid var(--gray-200); background-color: var(--white); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);" class="dark:bg-gray-900 dark:border-gray-800">
                                <span style="font-weight: 500; font-size: 1.125rem; color: var(--gray-600);" class="dark:text-gray-300">{{ $day['date'] }}</span>
                                <span style="font-weight: 700; font-size: 1.125rem; color: {{ $day['revenue'] > 0 ? 'var(--success-600)' : 'var(--gray-400)' }};" class="{{ $day['revenue'] > 0 ? 'dark:text-success-400' : 'dark:text-gray-600' }}">
                                    {{ $day['revenue'] > 0 ? number_format($day['revenue'], 2) : '-' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            </div>

            {{-- YEARLY TAB --}}
            <div x-show="activeTab === 'yearly'" x-cloak>
                <x-filament::section>
                    <x-slot name="heading">Raportul Anual - GENERAL</x-slot>
                    <x-slot name="description">Anul: {{ date('Y') }}</x-slot>
                    
                    <div class="mb-10 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm" style="height: 350px;">
                        <canvas id="yearlyChart"></canvas>
                    </div>

                    @php
                        $firstHalf = array_slice($metrics['yearly']['months'], 0, 6);
                        $secondHalf = array_slice($metrics['yearly']['months'], 6, 6);
                    @endphp

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        {{-- First Half (Jan-Jun) --}}
                        <div style="border-radius: 1rem; border: 1px solid var(--gray-200); overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);" class="dark:border-gray-800">
                            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                                <thead style="background-color: rgba(0,0,0,0.03);" class="dark:bg-gray-900/50">
                                    <tr>
                                        <th style="padding: 1rem; font-weight: 600; border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 dark:text-white">Luna</th>
                                        @foreach($metrics['yearly']['planNames'] as $planName)
                                            <th style="padding: 1rem; font-weight: 500; color: var(--gray-500); border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 dark:text-gray-400">{{ $planName }}</th>
                                        @endforeach
                                        <th style="padding: 1rem; font-weight: 700; text-align: right; border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 dark:text-white">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($firstHalf as $month)
                                        <tr style="border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td style="padding: 1rem; font-weight: 500;" class="dark:text-white capitalize">{{ $month['month'] }}</td>
                                            @foreach($metrics['yearly']['planNames'] as $planName)
                                                <td style="padding: 1rem; color: var(--gray-600);" class="dark:text-gray-400">
                                                    {{ isset($month['breakdown'][$planName]) && $month['breakdown'][$planName] > 0 ? (floor($month['breakdown'][$planName]) == $month['breakdown'][$planName] ? number_format($month['breakdown'][$planName], 0) : number_format($month['breakdown'][$planName], 2)) : '-' }}
                                                </td>
                                            @endforeach
                                            <td style="padding: 1rem; font-weight: 700; text-align: right;" class="dark:text-white">
                                                {{ $month['total'] > 0 ? number_format($month['total'], 2) : '0.00' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Second Half (Jul-Dec) --}}
                        <div style="border-radius: 1rem; border: 1px solid var(--gray-200); overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);" class="dark:border-gray-800">
                            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                                <thead style="background-color: rgba(0,0,0,0.03);" class="dark:bg-gray-900/50">
                                    <tr>
                                        <th style="padding: 1rem; font-weight: 600; border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 dark:text-white">Luna</th>
                                        @foreach($metrics['yearly']['planNames'] as $planName)
                                            <th style="padding: 1rem; font-weight: 500; color: var(--gray-500); border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 dark:text-gray-400">{{ $planName }}</th>
                                        @endforeach
                                        <th style="padding: 1rem; font-weight: 700; text-align: right; border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 dark:text-white">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($secondHalf as $month)
                                        <tr style="border-bottom: 1px solid var(--gray-200);" class="dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td style="padding: 1rem; font-weight: 500;" class="dark:text-white capitalize">{{ $month['month'] }}</td>
                                            @foreach($metrics['yearly']['planNames'] as $planName)
                                                <td style="padding: 1rem; color: var(--gray-600);" class="dark:text-gray-400">
                                                    {{ isset($month['breakdown'][$planName]) && $month['breakdown'][$planName] > 0 ? (floor($month['breakdown'][$planName]) == $month['breakdown'][$planName] ? number_format($month['breakdown'][$planName], 0) : number_format($month['breakdown'][$planName], 2)) : '-' }}
                                                </td>
                                            @endforeach
                                            <td style="padding: 1rem; font-weight: 700; text-align: right;" class="dark:text-white">
                                                {{ $month['total'] > 0 ? number_format($month['total'], 2) : '0.00' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TOTAL ROW BOTTOM --}}
                    <div style="margin-top: 2.5rem; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--gray-200); background-color: rgba(0,0,0,0.02); display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 2rem;" class="dark:bg-gray-900/80 dark:border-gray-800">
                        @foreach($metrics['yearly']['planNames'] as $planName)
                            <div style="text-align: center; padding: 0 1rem;">
                                <span style="display: block; font-size: 0.875rem; text-transform: uppercase; color: var(--gray-500); font-weight: 500; margin-bottom: 0.25rem;">{{ $planName }}</span>
                                <span style="font-weight: 700; font-size: 1.25rem;" class="dark:text-gray-200">
                                    {{ isset($metrics['yearly']['planTotals'][$planName]) && $metrics['yearly']['planTotals'][$planName] > 0 ? (floor($metrics['yearly']['planTotals'][$planName]) == $metrics['yearly']['planTotals'][$planName] ? number_format($metrics['yearly']['planTotals'][$planName], 0) : number_format($metrics['yearly']['planTotals'][$planName], 2)) : '0' }}
                                </span>
                            </div>
                        @endforeach
                        
                        <div style="height: 3rem; width: 1px; background-color: var(--gray-300);" class="dark:bg-gray-700 hidden md:block"></div>
                        
                        <div style="text-align: right; padding: 0 1rem;">
                            <span style="display: block; font-size: 0.875rem; text-transform: uppercase; color: var(--gray-500); font-weight: 700; margin-bottom: 0.25rem;">TOTAL AN</span>
                            <span style="font-size: 1.875rem; font-weight: 900; color: var(--primary-600);" class="dark:text-primary-400">
                                {{ number_format($metrics['yearly']['revenue'], 2) }} <span style="font-size: 1.25rem;">RON</span>
                            </span>
                        </div>
                    </div>
                </x-filament::section>
            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                let chartInstance = null;
                const yearlyData = @json($metrics['yearly']);
                const months = yearlyData.months.map(m => m.month.substring(0, 3));
                
                const colors = [
                    '#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#6366f1'
                ];

                const datasets = yearlyData.planNames.map((planName, index) => {
                    return {
                        label: planName,
                        data: yearlyData.months.map(m => m.breakdown[planName] || 0),
                        backgroundColor: colors[index % colors.length],
                    };
                });

                // Initialize chart on tab switch
                window.addEventListener('alpine:initialized', () => {
                    const ctx = document.getElementById('yearlyChart');
                    if(ctx) {
                        chartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: months,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom' }
                                },
                                scales: {
                                    x: { stacked: true },
                                    y: { stacked: true }
                                }
                            }
                        });
                    }
                });
            });
        </script>
    </div>
</x-filament-panels::page>
