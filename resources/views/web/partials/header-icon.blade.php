<svg class="header-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($icon)
  @case('home')<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-7h6v7"/>@break
  @case('shop')<path d="M4 8h16l-1 13H5zM8 8V6a4 4 0 0 1 8 0v2"/>@break
  @case('offers')<path d="M3 8h18v4H3zM5 12v9h14v-9M12 8v13"/><path d="M12 8H8a3 3 0 1 1 3-3zm0 0h4a3 3 0 1 0-3-3z"/>@break
  @case('cart')<path d="M3 4h2l3 12h11l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>@break
  @case('heart')<path d="M20 5a5 5 0 0 0-7 0l-1 1-1-1a5 5 0 0 0-7 7l8 8 8-8a5 5 0 0 0 0-7Z"/>@break
  @case('package')<path d="m3 7 9-4 9 4v11l-9 4-9-4zM3 7l9 4 9-4M12 11v11M7 5l10 4"/>@break
  @case('globe')<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>@break
  @case('user')<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
  @case('orders')<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 15h6"/>@break
  @case('return')<path d="M4 10h10a6 6 0 0 1 6 6v3M4 10l5-5m-5 5 5 5"/>@break
  @case('star')<path d="m12 3 2.8 5.7 6.3.9-4.5 4.4 1.1 6.2-5.7-3-5.7 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>@break
  @case('referral')<circle cx="12" cy="6" r="2.5"/><circle cx="6" cy="18" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="m10.9 8.3-3.7 7.4m5.9-7.4 3.7 7.4"/>@break
  @case('bell')<path d="M18 8a6 6 0 0 0-12 0c0 6-2 7-2 8h16c0-1-2-2-2-8m-8 11a2 2 0 0 0 4 0"/>@break
  @case('edit')<path d="m14 5 5 5m-14 9 1-5L16 4a2 2 0 0 1 3 0l1 1a2 2 0 0 1 0 3L10 18l-5 1Z"/>@break
  @case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
  @case('logout')<path d="M9 3H4v18h5M10 12h11m-4-4 4 4-4 4"/>@break
  @case('code')<path d="m8 6-6 6 6 6m8-12 6 6-6 6M14 3l-4 18"/>@break
@endswitch
</svg>
