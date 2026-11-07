@props(['title'])
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} · {{ config('cendana.name') }}</title>
<script>
    (function () {
        try {
            var root = document.documentElement;
            var get = function (key) { return localStorage.getItem(key); };
            var theme = get('theme');
            if (theme === 'light' || theme === 'dark') root.dataset.theme = theme;
            var accent = get('accent');
            if (accent && accent !== 'indigo') root.dataset.accent = accent;
            var radius = get('radius');
            if (radius) root.style.setProperty('--radius', radius + 'rem');
            if (get('sbmode') === 'mini') root.dataset.sbmode = 'mini';
        } catch (error) {}
    })();
</script>
<script>
    window.__i18n = @json(__('js'));
    window.__t = function (key, replace) {
        var text = window.__i18n[key] || key;
        Object.keys(replace || {}).forEach(function (name) { text = text.split(':' + name).join(replace[name]); });
        return text;
    };
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])

