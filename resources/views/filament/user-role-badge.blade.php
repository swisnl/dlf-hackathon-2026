{{-- Rendered in the topbar via the USER_MENU_BEFORE render hook in AppPanelProvider. --}}
<x-filament::badge :color="$role->getColor()" :icon="$role->getIcon()">
    {{ $role->getLabel() }}
</x-filament::badge>
