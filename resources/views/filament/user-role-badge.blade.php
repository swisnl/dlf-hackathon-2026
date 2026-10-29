{{-- Shown in the topbar (AdminPanelProvider render hook) and on the developer logins. --}}
<x-filament::badge :color="$role->getColor()" :icon="$role->getIcon()">
    {{ $role->getLabel() }}
</x-filament::badge>
