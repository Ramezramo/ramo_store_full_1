@php
    $counts  = $sidebarCounts[$key] ?? ['total' => 0, 'pending' => 0];
    $total   = $counts['total'] ?? 0;
    $pending = $counts['pending'] ?? 0;
    $fmt = fn ($n) => $n > 999 ? round($n / 1000, 1) . 'k' : $n;
@endphp
<span class="nav-counts">
    @if($pending > 0)
        <span class="nav-count pending" title="{{ $pending }} awaiting action">{{ $fmt($pending) }}</span>
    @endif
    <span class="nav-count" title="{{ number_format($total) }} total">{{ $fmt($total) }}</span>
</span>
