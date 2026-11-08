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
        // One pass over whole placeholder names, so ":to" never eats the start of ":total".
        return text.replace(/:([A-Za-z_]+)/g, function (match, name) {
            return replace && Object.prototype.hasOwnProperty.call(replace, name) ? String(replace[name]) : match;
        });
    };
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])

