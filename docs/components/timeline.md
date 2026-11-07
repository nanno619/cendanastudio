# 🕒 Timeline

`<x-ui.timeline>` shows a sequence of activity in a semantic `<ol>`. Source: `resources/views/components/ui/timeline.blade.php`.

## 🧩 Data shape

`items` is an array; each element needs a `title`. `description`, `time`, `datetime` (ISO, for the `<time>` attribute) and `tone` are optional. `tone="success"` uses a green dot, `danger` red, and anything else the primary colour.

## 📝 History example

```blade
<x-ui.timeline :items="[
    ['title' => __('Order completed'), 'time' => __('Today, :time', ['time' => '10:30']), 'datetime' => '2026-10-03T10:30:00', 'tone' => 'success'],
    ['title' => __('Parcel shipped'), 'time' => __('Yesterday'), 'description' => __('The courier collected the parcel.')],
    ['title' => __('Payment failed'), 'time' => '1 Oct 2026', 'tone' => 'danger'],
]" />
```

## 🔌 From an Eloquent relationship

```php
$events = $order->events()->latest()->get()->map(fn ($event) => [
    'title' => $event->title,
    'description' => $event->description,
    'time' => $event->created_at->diffForHumans(),
    'datetime' => $event->created_at->toIso8601String(),
    'tone' => $event->is_error ? 'danger' : 'success',
])->all();
```

The order follows the array you pass; use `latest()` for newest-first chronology. `diffForHumans()` follows the app locale, so it reads “2 jam yang lepas” in the Malay interface. For an empty list, show `<x-ui.empty>` outside the timeline.
