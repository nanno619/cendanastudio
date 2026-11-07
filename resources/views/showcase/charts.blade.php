<x-layouts.admin title="{{ __('Charts') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Charts') }}" description="{{ __('Visualisation patterns for trends, comparisons, composition and empty states.') }}" />

<section class="grid gap-4 xl:grid-cols-2">
    <x-ui.card title="{{ __('Line chart') }}" description="{{ __('Compare two series over time.') }}">
        <x-ui.chart type="line" aria="{{ __('Line chart of site and app visits over seven days') }}" :labels="array_map('__', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])" :series="[
            ['name' => __('Site'), 'data' => [42, 48, 46, 58, 62, 68, 73], 'color' => 1],
            ['name' => __('App'), 'data' => [26, 31, 29, 36, 39, 44, 48], 'color' => 2],
        ]" summary="{{ __('Both rise through the week; the site has more visits every day.') }}" />
    </x-ui.card>

    <x-ui.card title="{{ __('Bar chart') }}" description="{{ __('Compare values across periods at a glance.') }}">
        <x-ui.chart type="bar" aria="{{ __('Bar chart of tasks completed per month') }}" :labels="array_map('__', ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'])" :series="[['name' => __('Tasks completed'), 'data' => [34, 42, 39, 51, 57, 64], 'color' => 1]]" :legend="true" summary="{{ __('The most tasks were completed in September: 64.') }}" />
    </x-ui.card>

    <x-ui.card title="{{ __('Area chart') }}" description="{{ __('Emphasise the growth of a total in one series.') }}">
        <x-ui.chart type="area" aria="{{ __('Area chart of entry growth over six months') }}" :labels="array_map('__', ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'])" :series="[['name' => __('Entries'), 'data' => [18, 25, 29, 38, 43, 56], 'color' => 2]]" :smooth="true" summary="{{ __('Entries grew from 18 in April to 56 in September.') }}" />
    </x-ui.card>

    <x-ui.card title="{{ __('Stacked chart') }}" description="{{ __('See the total and each category’s contribution together.') }}">
        <x-ui.chart type="stacked" aria="{{ __('Stacked bar chart of work by channel') }}" :labels="array_map('__', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'])" :series="[
            ['name' => __('Core team'), 'data' => [22, 26, 24, 31, 29], 'color' => 1],
            ['name' => __('Collaborators'), 'data' => [12, 15, 14, 17, 20], 'color' => 2],
            ['name' => __('Automated'), 'data' => [7, 8, 10, 9, 12], 'color' => 3],
        ]" summary="{{ __('The core team remains the largest contributor every day.') }}" />
    </x-ui.card>

    <x-ui.card title="{{ __('Donut chart') }}" description="{{ __('Composition of one total, with numbers and percentages.') }}">
        <x-ui.chart type="donut" aria="{{ __('Donut chart of entry sources') }}" :items="[
            ['label' => __('Manual'), 'value' => 520, 'color' => 1],
            ['label' => __('Imported'), 'value' => 280, 'color' => 2],
            ['label' => __('Automated'), 'value' => 200, 'color' => 3],
        ]" total-label="{{ __('Entries') }}" summary="{{ __('Of 1,000 entries, 52% were created manually, 28% imported and 20% automatically.') }}" />
    </x-ui.card>

    <x-ui.card title="{{ __('No data') }}" description="{{ __('Don\'t show an empty chart or axes with no values.') }}">
        <x-ui.table-state state="empty" title="{{ __('No chart data yet') }}" description="{{ __('The chart will appear once data for this period is available.') }}">
            <button type="button" class="btn btn-outline" data-toast="info" data-toast-title="{{ __('Choose another period to see sample data') }}">{{ __('Change period') }}</button>
        </x-ui.table-state>
    </x-ui.card>
</section>
</x-layouts.admin>
